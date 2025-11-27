<?php
declare(strict_types=1);

namespace NixPHP\Core;

use NixPHP\Exceptions\DispatcherException;
use NixPHP\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function NixPHP\event;
use function NixPHP\simple_render;

/**
 * Core dispatcher that handles HTTP request routing and execution.
 *
 * Manages the routing of requests to appropriate handlers and
 * supports both controller classes and callable actions.
 */
class Dispatcher
{
    /**
     * Route instance used for matching requests to handlers.
     *
     * @var Route
     */
    private Route $route;

    /**
     * Initializes a new dispatcher instance.
     *
     * @param Route $route The route instance for matching and handling requests
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Forwards the HTTP request to its matching route handler.
     *
     * Processes the request by:
     * - Matching the URI and method against registered routes
     * - Handling special case for a root path ('/')
     * - Executing controller actions or callables
     * - Dispatching controller lifecycle events
     * - Validating and returning the response
     *
     * @param ServerRequestInterface $request The incoming HTTP request
     *
     * @return ResponseInterface Response from the route handler
     * @throws RouteNotFoundException If no matching route is found
     * @throws DispatcherException If handler returns invalid response
     */
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
            event()->dispatch(Event::CONTROLLER_CALLING, $request, $class, $action);
            $response = $class->$classAction(...$route['params'] ?? null);
            event()->dispatch(Event::CONTROLLER_CALLED, $request, $class, $action, $response);
        } else if (is_callable($action)) {
            event()->dispatch(Event::CONTROLLER_CALLING, $request, null, $action);
            $response = $action(...$route['params'] ?? null);
            event()->dispatch(Event::CONTROLLER_CALLED, $request, null, $action, $response);
        }

        if ($response instanceof ResponseInterface) return $response;
        if ($response === -1) exit(0);
        throw new DispatcherException('No valid response returned.');
    }

}