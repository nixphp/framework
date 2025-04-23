<?php

namespace PHPico\Core;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use PHPico\Exceptions\HttpException;
use PHPico\Support\Session;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function PHPico\config;
use function PHPico\event;
use function PHPico\send_response;

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

        $response = $this->container->get('dispatcher')->forward($request);

        if ($response instanceof ResponseInterface) {
            event()->dispatch('response.sending', $response);
            send_response($response);
            event()->dispatch('request.end', $response, $this);
            exit(0);
        }

        throw new HttpException('No response object returned.');
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

    private function loadRoutes(): void
    {
        $routes = $this->getBasePath() . '/app/routes.php';
        if (file_exists($routes)) require_once $routes;
    }

    private function boot(): void
    {
        set_exception_handler([ErrorHandler::class, 'handleException']);
        set_error_handler([ErrorHandler::class, 'handleError']);
        ini_set('display_errors', false);

        $appDir = $this->getBasePath() . '/app';

        $this->container->set('log', function() {

            $logFile   = $this->getBasePath() . '/log/app.log';
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

        $this->container->set('config', function() use ($appDir) {
             return new Config(require $appDir . '/config.php');
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

        $this->loadRoutes();
    }

}