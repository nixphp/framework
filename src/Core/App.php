<?php

declare(strict_types=1);

namespace NixPHP\Core;

use Composer\InstalledVersions;
use NixPHP\Support\AppHolder;
use NixPHP\Support\Guard;
use NixPHP\Support\Plugin;
use NixPHP\Support\RequestParameter;
use NixPHP\Support\Stopwatch;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use function NixPHP\event;
use function NixPHP\log;
use function NixPHP\plugin;
use function NixPHP\response;
use function NixPHP\send_response;
use function NixPHP\simple_view;

class App
{
    private Container $container;
    private array $plugins = [];

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
        $this->container()->get(EventManager::class)->dispatch(Event::REQUEST_START, $request);
        $this->container()->set(RequestInterface::class, $request);
        $this->container()->set(RequestParameter::class, function(ContainerInterface $container) {
            return new RequestParameter($container->get(RequestInterface::class));
        });

        $viewPath = $this->getCoreBasePath() . '/src/Resources/views';

        try {
            $response = $this->container->get(Dispatcher::class)->forward($request);
        } catch (\Throwable $e) {

            $responses = event()->dispatch(Event::EXCEPTION, $e);

            $exceptionResponse = end($responses);

            if ($exceptionResponse instanceof ResponseInterface) {
                send_response($exceptionResponse);
            }

            log()->error($e->getMessage());

            if ($this->container->get(Environment::class) === Environment::PROD) return;

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

        log()->info('Request completed in ' . Stopwatch::stop('app') . 'ms');

        event()->dispatch(Event::RESPONSE_SEND, $response);

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
        return $this->container->get(Guard::class);
    }

    /**
     * Returns an array of all loaded plugins
     *
     * @return Plugin[]
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    public function hasPlugin(string $name): bool
    {
        return isset($this->plugins[$name]);
    }

    public function getPlugin(string $name): Plugin
    {
        if (!$this->hasPlugin($name)) {
            throw new \InvalidArgumentException('Plugin not found: ' . $name);
        }

        return $this->plugins[$name];
    }

    /**
     * @param string $resource
     *
     * @return array
     */
    public function collectPluginResources(string $resource): array
    {
        if (!in_array($resource, ['configPaths', 'viewPaths', 'routeFiles', 'functionFiles', 'viewHelperFiles'])) {
            throw new \InvalidArgumentException('Invalid plugin property type: ' . $resource);
        }

        $result = [];
        $getter = 'get' . ucfirst($resource);

        foreach ($this->getPlugins() as $plugin) {

            $resp = $plugin->$getter();

            if (is_array($resp)) {
                $result = array_merge($result, $resp);
                continue;
            }

            $result[] = $resp;

        }

        return $result;
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

            $key   = trim($key);
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
        $appDir  = $this->getBasePath() . '/app';
        $coreDir = $this->getCoreBasePath() . '/src';

        $this->container->set(Environment::class, fn() => $_ENV['APP_ENV'] ?? getenv('APP_ENV'));

        $this->container->set(Guard::class, fn() => new Guard());

        $this->container->set(Route::class, fn() => new Route());

        $this->container->set(Dispatcher::class, fn($container) => new Dispatcher($container->get(Route::class)));

        $this->container->set(EventManager::class, fn() => new EventManager());

        $this->container->set(Config::class, function() use ($appDir, $coreDir) {

            $appConfig = file_exists($appDir . '/config.php')
                ? require $appDir . '/config.php'
                : [];

            $coreConfig = file_exists($coreDir.'/src/config.php')
                ? require $coreDir . '/src/config.php'
                : [];

            $pluginConfig = [];

            $configPaths = $this->collectPluginResources('configPaths');

            foreach ($configPaths as $file) {
                if (!file_exists($file)) continue;
                $pluginConfig = array_replace_recursive($pluginConfig, require $file);
            }

            $merged = array_replace_recursive($coreConfig, $pluginConfig, $appConfig);

            return new Config($merged);

        });

        $this->container->set(LoggerInterface::class, function() {

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

    }

    /**
     * Load application routes from the route file
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

            $plugin = new Plugin($package);

            $plugin->addConfigPath(
                $path . '/src/config.php'
            );

            $plugin->addRouteFile(
                $path . '/src/routes.php'
            );

            $plugin->addViewPath(
                $path . '/src/views'
            );

            $plugin->addFunctionFile(
                $path . '/src/functions.php'
            );

            $plugin->addViewHelperFile(
                $path . '/src/view_helpers.php'
            );

            $plugin->setBootstrapFile(
                $path . '/bootstrap.php'
            );

            $plugin->boot();

            $this->plugins[$package] = $plugin;

        }
        
    }

    /**
     * Register security guard rules
     */
    private function loadGuards(): void
    {
        $config = $this->container->get(Config::class);

        $this->guard()->register('safePath', function ($path) {

            if (
                $path === '' ||
                str_contains($path, '..') ||
                str_starts_with($path, '/') ||
                str_contains($path, '://') ||
                !preg_match('/^[A-Za-z0-9_\/.-]+$/', $path)
            ) {
                throw new \InvalidArgumentException('Insecure path detected! Navigation outside of application root is not allowed.');
            }

            return $path;

        });

        $this->guard()->register('safeOutput', function ($value) {

            if (is_array($value)) {
                return array_map(fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8'), $value);
            }

            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        });

        $this->guard()->register('ipBlacklist', function (string $ip, array $list = []) use ($config) {

            if (empty($list)) {
                $list = $config->get('guard:ipBlacklist');
            }

            if (in_array($ip, $list, true)) {
                throw new \InvalidArgumentException('IP address is blacklisted!');
            }

            return true;

        });

        $this->guard()->register('userAgentBlacklist', function (string $userAgent, array $list = []) use ($config) {

            if (empty($list)) {
                $list = $config->get('guard:userAgentBlacklist');
            }

            if (in_array($userAgent, $list, true)) {
                throw new \InvalidArgumentException('UserAgent is blacklisted!');
            }

            return true;

        });

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