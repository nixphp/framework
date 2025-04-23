<?php

namespace PHPico\Core;

use PHPico\Exceptions\DispatcherException;
use PHPico\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function PHPico\abort;
use function PHPico\event;
use function PHPico\render;
use function PHPico\send_response;
use function PHPico\view;

class Dispatcher
{
    private Route $router;

    public function __construct(Route $router)
    {
        $this->router = $router;
    }

    public function forward(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod() ?? 'GET';
        $uri = $request->getUri()->getPath();

        try {
            $route = $this->router->find($uri, $method);
        } catch (RouteNotFoundException $e) {
            if ($uri === '/') send_response(render('phpico_welcome'));
            abort(404, 'Not Found');
        }

        $response = null;
        $action = $route['action'];

        if (is_array($action)) {
            [$class, $classAction] = $action;
            $class = new $class();
            event()->dispatch('controller.calling', $class, $action);
            $response = $class->$classAction($route['params'] ?? null);
            event()->dispatch('controller.called', $class, $action, $response);
        } else if (is_callable($action)) {
            event()->dispatch('controller.calling', null, $action);
            $response = $action($route['params'] ?? null);
            event()->dispatch('controller.called', null, $action, $response);
        }

        if ($response instanceof ResponseInterface) return $response;
        throw new DispatcherException('No valid response returned.');
    }

}