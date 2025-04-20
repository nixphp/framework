<?php

namespace PHPico;

use PHPico\Core\App;
use PHPico\Core\Config;
use PHPico\Core\Route;
use PHPico\Core\Container;
use PHPico\Http\Request;
use PHPico\Http\Response;

ob_start();

global $container;
$container = new Container();

function app(): App
{
    global $container;

    if (!$container->has('app')) {
        $container->set('app', function($container) { return new App($container); });
    }
    return $container->get('app');
}

function config(string $key = null, mixed $default = null): array|object|string
{
    /** @var Config $config */
    $config = app()->container()->get('config');

    if (empty($key)) {
        return $config;
    }

    return $config->get($key, $default);
}

function route(string $name = null, array $params = []): Route|string
{
    /* @var Route $route */
    $route = app()->container()->get('route');

    if (null === $name) {
        return $route;
    }

    return $route->url($name, $params);
}

function dispatch(string $uri): string
{
    return app()->container()->get('dispatcher')->forward($uri);
}

function request(): Request
{
    return app()->request();
}

function response(mixed $content = '', int $statusCode = 200): Response
{
    $response = new Response($content);
    $response->setStatus($statusCode);
    return $response;
}

function event()
{
    return app()->container()->get('event');
}

function database(): \PDO
{
    return app()->container()->get('database');
}