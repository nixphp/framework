<?php
declare(strict_types=1);

namespace NixPHP\Core;

use NixPHP\Support\AppHolder;
use NixPHP\Support\Guard;
use NixPHP\Support\Plugin;
use Composer\InstalledVersions;
use NixPHP\Support\RequestParameter;
use NixPHP\Support\Stopwatch;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use function NixPHP\env;
use function NixPHP\event;
use function NixPHP\response;
use function NixPHP\send_response;
use function NixPHP\plugin;
use function NixPHP\simple_view;

class App
{

    private Container $container;

    /**
     * Initialize the application with a dependency container
     *
     * @param Container $container The dependency injection container
     */
    public function __construct(Container $container)
    {
        Stopwatch::start('app');
        $this->container = $container;
        AppHolder::set($this);
        $this->boot();
    }

    /**
     * Run the application and handle the HTTP request-response cycle
     */
    public function run(): void
    {
        if (PHP_SAPI === 'cli') return;

        $request = $this->createServerRequest();
        $this->container()->get('event')->dispatch('request.start', $request);
        $this->container()->set('request', $request);
        $this->container()->set('parameter', function($container) {
            return new RequestParameter($container->get('request'));
        });
        $viewPath = $this->getCoreBasePath() . '/src/Resources/views';

        try {
            $response = $this->container->get('dispatcher')->forward($request);
        } catch (\Throwable $e) {

            if (env('APP_ENV') === Environment::PRODUCTION) {
                \NixPHP\log()->error($e);
                return;
            }

            $statusCode = method_exists($e, 'getStatusCode')
                ? $e->getStatusCode()
                : 500;
            $response = response(simple_view(
                $viewPath . '/errors/default.phtml', [
                    'statusCode' => $statusCode,
                    'message' => $e->getMessage(),
                    'stackTrace' => $e->getTraceAsString(),
                ]
            ), $statusCode);

        }

        event()->dispatch('response.sending', $response);
        send_response($response); // This will abort the request as exit(0) is called
    }

    /**
     * Get the dependency injection container
     *
     * @return Container The container instance
     */
    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Get the guard service for security checks
     *
     * @return Guard The guard service instance
     */
    public function guard(): Guard
    {
        return $this->container->get('guard');
    }

    /**
     * Get the application base path
     *
     * @return string|null The base path or null if not defined
     */
    public function getBasePath():? string
    {
        if (!defined('\BASE_PATH')) {
            return null;
        }
        return \BASE_PATH;
    }

    /**
     * Get the framework core base path
     *
     * @return string|null The core base path or null if not defined
     */
    private function getCoreBasePath():? string
    {
        if (!defined('\NIXPHP_BASE_PATH')) {
            return null;
        }
        return \NIXPHP_BASE_PATH;
    }

    /**
     * Bootstrap the application by loading environment, services, plugins, routes and guards
     */
    private function boot(): void
    {
        $envFile = file_exists($this->getBasePath() . '/.env.local')
            ? $this->getBasePath() . '/.env.local'
            : $this->getBasePath() . '/.env';

        $this->loadEnv($envFile);
        $this->loadServices();
        $this->loadPlugins();
        $this->loadRoutes();
        if (PHP_SAPI !== 'cli') $this->loadGuards();
    }

    /**
     * Load environment variables from a file
     *
     * @param string $path Path to the environment file
     */
    private function loadEnv(string $path = '/.env'): void
    {
        if (!file_exists($path)) return;

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    /**
     * Register core services in the container
     */
    private function loadServices(): void
    {
        $appDir = $this->getBasePath() . '/app';
        $coreDir = $this->getCoreBasePath() . '/src';

        $this->container->set('guard', function() {
            return new Guard();
        });

        $this->container->set('environment', function() {
            return new Environment($_ENV['APP_ENV'] ?? getenv('APP_ENV'));
        });

        $this->container->set('plugin', function() {
            return new Plugin();
        });

        $this->container->set('log', function() {

            $logFile   = $this->getBasePath() . '/logs/app.log';
            $directory = dirname($logFile);

            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            if (!file_exists($logFile)) {
                touch($logFile);
                chmod($logFile, 0664);
            }

            return new Log($logFile);

        });

        $this->container->set('event', function() {
            return new Event();
        });

        $this->container->set('config', function() use ($appDir, $coreDir) {

            $appConfig = file_exists($appDir . '/config.php')
                ? require $appDir . '/config.php'
                : [];

            $coreConfig = file_exists($coreDir.'/src/config.php')
                ? require $coreDir . '/src/config.php'
                : [];

            $pluginConfig = [];
            foreach (plugin()->getSection('configPaths') as $file) {
                $pluginConfig = array_replace_recursive($pluginConfig, require $file);
            }

            $merged = array_replace_recursive($coreConfig, $pluginConfig, $appConfig);

            return new Config($merged);
        });

        $this->container->set('route', function() {
            return new Route();
        });

        $this->container->set('dispatcher', function($container) {
            return new Dispatcher($container->get('route'));
        });
    }

    /**
     * Load application routes from the routes file
     */
    private function loadRoutes(): void
    {
        $routes = $this->getBasePath() . '/app/routes.php';
        if (file_exists($routes)) require_once $routes;
    }

    /**
     * Load and initialize installed plugins
     */
    private function loadPlugins(): void
    {
        /* @var Plugin $pluginService */
        $pluginService = $this->container->get('plugin');

        // Try to load configuration order from userspace
        $orderedPackages = [];
        $pluginConfigPath = $this->getBasePath() . '/app/plugins.php';

        if (file_exists($pluginConfigPath)) {
            $orderedPackages = require $pluginConfigPath;
        }

        $allPackages = array_unique(InstalledVersions::getInstalledPackagesByType('nixphp-plugin'));
        $ordered = array_filter($orderedPackages, fn($name) => in_array($name, $allPackages));
        $remaining = array_diff($allPackages, $ordered);

        $finalOrder = array_merge($ordered, $remaining);

        // Load Plugins in final order
        foreach ($finalOrder as $package) {

            $path = InstalledVersions::getInstallPath($package);

            if (!$path) continue;

            if (file_exists($path . '/src/config.php')) {
                $pluginService->addMeta($package, 'configPaths', $path . '/src/config.php');
            }
            if (file_exists($path . '/src/routes.php')) {
                require_once $path . '/src/routes.php';
            }
            if (is_dir($path . '/src/views')) {
                $pluginService->addMeta($package, 'viewPaths', $path . '/src/views');
            }
            if (file_exists($path . '/src/functions.php')) {
                require_once $path . '/src/functions.php';
            }
            if (file_exists($path . '/src/view_helpers.php')) {
                require_once $path . '/src/view_helpers.php';
            }
            if (file_exists($path . '/bootstrap.php')) {
                $pluginService->bootOnce($package, $path . '/bootstrap.php');
            }

        }
        
    }

    /**
     * Register security guard rules
     */
    private function loadGuards(): void
    {
        $this->guard()->register('safePath', function ($path) {

            if (
                $path === '' ||
                str_contains($path, '..') ||
                str_starts_with($path, '/') ||
                str_contains($path, '://') ||
                !preg_match('/^[A-Za-z0-9_\/.-]+$/', $path)
            ) {
                throw new \InvalidArgumentException('Insecure path detected! Please find another solution.');
            }

            return $path;

        });

        $this->guard()->register('safeOutput', function ($value) {
            if (is_array($value)) {
                return array_map(fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8'), $value);
            }

            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        });

        $this->guard()->register('ipBlacklist', function (string $ip, array $list = []) {

            if (empty($list)) {
                $list = $this->container->get('config')->get('guard:ipBlacklist');
            }

            if (in_array($ip, $list, true)) {
                throw new \InvalidArgumentException('IP address is blacklisted!');
            }
            return true;

        });

        $this->guard()->register('userAgentBlacklist', function (string $userAgent, array $list = []) {

            if (empty($list)) {
                $list = $this->container->get('config')->get('guard:userAgentBlacklist');
            }

            if (in_array($userAgent, $list, true)) {
                throw new \InvalidArgumentException('UserAgent is blacklisted!');
            }

            return true;

        });

    }

    /**
     * Check if a plugin is loaded
     *
     * @param string $pluginName Name of the plugin to check
     *
     * @return bool True if the plugin exists, false otherwise
     */
    public function hasPlugin(string $pluginName): bool
    {
        /** @var Plugin $pluginService */
        $pluginService = $this->container->get('plugin');
        $plugin = $pluginService->getMeta($pluginName);
        return !empty($plugin);
    }

    /**
     * Create a PSR-7 server request from globals
     *
     * @return ServerRequestInterface The created server request
     */
    private function createServerRequest(): ServerRequestInterface
    {
        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        return $creator->fromGlobals();
    }

}