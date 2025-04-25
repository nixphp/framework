<?php

namespace PHPico\Core;

use Composer\InstalledVersions;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use PHPico\Exceptions\AbortException;
use PHPico\Exceptions\HttpException;
use PHPico\Exceptions\RouteNotFoundException;
use PHPico\Support\Plugin;
use PHPico\Support\Session;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use function PHPico\config;
use function PHPico\event;
use function PHPico\response;
use function PHPico\send_response;
use function PHPico\plugin;
use function PHPico\view;

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
        event()->dispatch('request.start', $request);
        $this->container()->set('request', $request);

        try {
            $response = $this->container->get('dispatcher')->forward($request);
        } catch (AbortException | HttpException | RouteNotFoundException $e) {
            $response = response(view(
                'errors.' . $e->getStatusCode(),
                [
                    'statusCode' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ));
        } catch (\Throwable $e) {
            $response = response(view(
                'errors.500',
                [
                    'statusCode' => 500,
                    'message' => $e->getMessage()
                ]
            ));
        }

        event()->dispatch('response.sending', $response);
        send_response($response); // This will abort the request as exit(0) is called

    }

    public function container(): Container
    {
        return $this->container;
    }

    public function request(): RequestInterface
    {
        return $this->container->get('request');
    }

    public function session(): Session
    {
        return $this->container->get('session');
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
        if (!defined('\PHPICO_BASE_PATH')) {
            return null;
        }
        return \PHPICO_BASE_PATH;
    }

    private function boot(): void
    {
        $envFile = file_exists($this->getBasePath() . '/.env.local')
            ? $this->getBasePath() . '/.env.local'
            : $this->getBasePath() . '/.env';

        $this->loadEnv($envFile);

        if (getenv('APP_ENV') !== Environment::TESTING
            && getenv('APP_ENV') !== Environment::PRODUCTION
        ) {
            set_exception_handler([ErrorHandler::class, 'handleException']);
            set_error_handler([ErrorHandler::class, 'handleError']);
            ini_set('display_errors', false);
        }

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

        $this->container->set('database', function() {

            $config = config('database');
            if (!$config) return null;

            $database = new Database($config);
            return $database->getConnection();

        });
    }

    private function loadRoutes(): void
    {
        $routes = $this->getBasePath() . '/app/routes.php';
        if (file_exists($routes)) require_once $routes;
    }

    private function loadPlugins(): void
    {
        $paths = array_unique(array_map(function ($package) {
            return InstalledVersions::getInstallPath($package);
        }, InstalledVersions::getInstalledPackagesByType('phpico-plugin')));

        $pluginService = $this->container->get('plugin');

        foreach ($paths as $path) {
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