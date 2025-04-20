<?php

namespace PHPico\Core;

use PHPico\Http\Response;
use function PHPico\abort;
use function PHPico\render;

class Dispatcher
{
    private Route $router;

    public function __construct(Route $router)
    {
        $this->router = $router;
    }

    public function forward(string $uri): Response|null
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $route = $this->router->find($uri, $method);

        if (!$route) {
            if ($uri === '/') {
                return render('welcome');
            }
            abort(404, 'Not Found');
        }

        $response = null;
        $action = $route['action'];

        if (is_array($action)) {
            [$class, $classAction] = $action;
            $response = (new $class())->$classAction($route['params'] ?? null);
        } else if (is_callable($action)) {
            $response = $action($route['params'] ?? null);
        }

        if ($response instanceof Response) return $response;
        abort(500, 'No valid response returned.');
        return null;
    }

}