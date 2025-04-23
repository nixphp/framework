<?php

namespace PHPico\Core;

use PHPico\Exceptions\RouteNotFoundException;
use function PHPico\event;

class Route
{
    protected array $routes = [];

    public function add(string $method, string $path, array|callable $action, ?string $name = null)
    {
        if (count($this->routes) > 0 && empty($name)) {
            throw new \LogicException('You can\'t add routes without a name when there is more than one route configured.');
        }

        $this->routes[$name] = [
            'method' => $method,
            'path' => $path,
            'action' => $action
        ];
        return $this;
    }

    /**
     * @param string $uri
     * @param string $method
     *
     * @return array|null
     * @throws RouteNotFoundException
     */
    public function find(string $uri, string $method): ?array
    {
        event()->dispatch('route.matching', $uri, $method);
        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }
            $pattern = preg_replace('#\{[^}]+\}#', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                preg_match_all('#\{([^}]+)\}#', $route['path'], $paramNames);
                $params = array_combine($paramNames[1], $matches);
                event()->dispatch('route.matched', $route);
                return ['action' => $route['action'], 'params' => $params];
            }
        }
        event()->dispatch('route.not_found', $uri, $method);
        throw new RouteNotFoundException();
    }

    public function url(string $name, array $params = [])
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name) {
                $url = $route['path'];
                foreach ($params as $key => $value) {
                    $url = str_replace('{' . $key . '}', $value, $url);
                }
                return $url;
            }
        }
        throw new \Exception("Route '{$name}' not found.");
    }

}