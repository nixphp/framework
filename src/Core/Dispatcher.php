<?php

namespace NixPHP\Core;

use NixPHP\Exceptions\DispatcherException;
use NixPHP\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function NixPHP\event;
use function NixPHP\simple_render;

class Dispatcher
{
    private Route $route;

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    public function forward(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod() ?? 'GET';
        $uri = $request->getUri()->getPath();

        try {
            $route = $this->route->find($uri, $method);
        } catch (RouteNotFoundException $e) {
            if ($uri === '/' && $method === 'GET') {
                return simple_render(__DIR__ . '/../Resources/views/nixphp_welcome.phtml');
            }
            throw $e;
        }

        $response = null;
        $action = $route['action'];

        if (is_array($action)) {
            [$class, $classAction] = $action;
            $class = new $class();
            event()->dispatch('controller.calling', $request, $class, $action);
            $response = $class->$classAction($route['params'] ?? null);
            event()->dispatch('controller.called', $request, $class, $action, $response);
        } else if (is_callable($action)) {
            event()->dispatch('controller.calling', $request, null, $action);
            $response = $action($route['params'] ?? null);
            event()->dispatch('controller.called', $request, null, $action, $response);
        }

        if ($response instanceof ResponseInterface) return $response;
        throw new DispatcherException('No valid response returned.');
    }

}