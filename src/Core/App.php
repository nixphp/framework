<?php

namespace PHPico\Core;

use PHPico\Http\Request;
use PHPico\Http\Response;
use function PHPico\config;

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
        $request = new Request();
        $this->container()->set('request', $request);
        $response = $this->container->get('dispatcher')->forward($request->getUri());

        if ($response instanceof Response) {
            $response->send();
            exit(0);
        }

        throw new \LogicException('No response object returned.');
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function request(): Request
    {
        return $this->container->get('request');
    }

    public function getBasePath():? string
    {
        if (!defined('\BASE_PATH')) {
            return null;
        }

        return \BASE_PATH;
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

        $this->container->set('database', function($container) {

            $config = config('database');
            if (!$config) return null;

            $database = new Database($config);
            return $database->getConnection();

        });

        $this->loadRoutes();
    }

}