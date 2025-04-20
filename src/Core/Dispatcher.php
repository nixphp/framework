<?php

namespace PHPico\Core;

class Dispatcher
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function forward(string $uri)
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $route = $this->router->find($uri, $method);
        if ($route) {
            $action = $route['action'];

            if (is_array($action)) {
                $class = $action[0];
                $classAction = $action[1];
                return (new $class())->$classAction($route['params'] ?? null);
            }

            return $action($route['params'] ?? null);
        }

        abort(404, 'Not Found');
        return null;
    }

}