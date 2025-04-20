<?php

namespace PHPico\Core;

use PHPico\Http\Request;

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
        echo $this->container->get('dispatcher')->forward($request->getUri());
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function reuqest(): Request
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

        $this->container->set('config', function() use ($appDir) {
             return new Config(require $appDir . '/config.php');
        });

        $this->container->set('router', function() {
            return new Router();
        });

        $this->container->set('dispatcher', function($container) {
            return new Dispatcher($container->get('router'));
        });

        $this->loadRoutes();
    }

}