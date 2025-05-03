<?php

namespace NixPHP\Core;

use Composer\InstalledVersions;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use NixPHP\Exceptions\AbortException;
use NixPHP\Exceptions\DispatcherException;
use NixPHP\Exceptions\HttpException;
use NixPHP\Exceptions\RouteNotFoundException;
use NixPHP\Support\Guard;
use NixPHP\Support\Plugin;
use NixPHP\Support\Session;
use Psr\Http\Message\ServerRequestInterface;
use function NixPHP\event;
use function NixPHP\response;
use function NixPHP\send_response;
use function NixPHP\plugin;
use function NixPHP\view;

class App
{

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->boot();
    }

    public function run(): void
    {
        $request = $this->createServerRequest();
        $this->container()->get('event')->dispatch('request.start', $request);
        $this->container()->set('request', $request);

        try {
            $response = $this->container->get('dispatcher')->forward($request);
        } catch (AbortException | HttpException | RouteNotFoundException | DispatcherException $e) {
            $response = response(view(
                'errors.' . $e->getStatusCode(), [
                    'statusCode' => $e->getStatusCode(),
                    'message' => $e->getMessage()
                ]
            ), $e->getStatusCode());
        } catch (\Throwable $e) {
            $response = response(view(
                'errors.default', [
                    'statusCode' => 500,
                    'message' => $e->getMessage()
                ]
            ), 500);

        }

        event()->dispatch('response.sending', $response);
        send_response($response); // This will abort the request as exit(0) is called

    }

    public function container(): Container
    {
        return $this->container;
    }

    public function session(): Session
    {
        return $this->container->get('session');
    }

    public function guard(): Guard
    {
        return $this->container->get('guard');
    }

    public function getBasePath():? string
    {
        if (!defined('\BASE_PATH')) {
            return null;
        }
        return \BASE_PATH;
    }

    private function getCoreBasePath():? string
    {
        if (!defined('\NIXPHP_BASE_PATH')) {
            return null;
        }
        return \NIXPHP_BASE_PATH;
    }

    private function boot(): void
    {
        $envFile = file_exists($this->getBasePath() . '/.env.local')
            ? $this->getBasePath() . '/.env.local'
            : $this->getBasePath() . '/.env';

        $this->loadEnv($envFile);
        $this->loadServices();
        $this->loadPlugins();
        $this->loadRoutes();

    }

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

        $this->container->set('session', function() {
            return new Session();
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
            foreach (plugin()->getMeta('configPaths') as $file) {
                $pluginConfig = array_replace_recursive($pluginConfig, require $file);
            }

            $merged = array_replace_recursive($coreConfig, $pluginConfig, $appConfig);

            return new Config($merged);
        });

        $this->container->set('asset', function() {
            return new Asset();
        });

        $this->container->set('route', function() {
            return new Route();
        });

        $this->container->set('dispatcher', function($container) {
            return new Dispatcher($container->get('route'));
        });
    }

    private function loadRoutes(): void
    {
        $routes = $this->getBasePath() . '/app/routes.php';
        if (file_exists($routes)) require_once $routes;
    }

    private function loadPlugins(): void
    {
        $pluginService = $this->container->get('plugin');

        // Try to load configuration order from user
        $orderedPackages = [];
        $pluginConfigPath = __DIR__ . '/../config/plugins.php';

        if (file_exists($pluginConfigPath)) {
            $orderedPackages = require $pluginConfigPath;
        }

        $allPackages = InstalledVersions::getInstalledPackagesByType('nixphp-plugin');
        var_dump($allPackages);
        $ordered = array_filter($orderedPackages, fn($name) => in_array($name, $allPackages));
        $remaining = array_diff($allPackages, $ordered);

        $finalOrder = array_merge($ordered, $remaining);

        // Lade Plugins in finaler Reihenfolge
        foreach ($finalOrder as $package) {
            $path = InstalledVersions::getInstallPath($package);
            if (!$path) continue;

            if (file_exists($path . '/bootstrap.php')) {
                $pluginService->addMeta('bootstraps', $path . '/bootstrap.php');
                require_once $path . '/bootstrap.php';
            }
            if (file_exists($path . '/app/config.php')) {
                $pluginService->addMeta('configPaths', $path . '/app/config.php');
            }
            if (is_dir($path . '/app/views')) {
                $pluginService->addMeta('viewPaths', $path . '/app/views');
            }
        }
    }

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