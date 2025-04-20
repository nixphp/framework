<?php

namespace PHPico;

require_once __DIR__ . '/../vendor/autoload.php';

use PHPico\Core\App;
use PHPico\Core\Config;
use PHPico\Core\Container;
use PHPico\Core\Database;
use PHPico\Core\Dispatcher;
use PHPico\Core\Router;
use PHPico\Core\View;

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

function route(string $name = null, array $params = []): Router|string
{
    /* @var Router $router */
    $router = app()->container()->get('router');

    if (null === $name) {
        return $router;
    }

    return $router->url($name, $params);
}

function dispatch(string $uri): Dispatcher
{
    return app()->container()->get('dispatcher')->forward($uri);
}

function request()
{
    return app()->container()->get('request');
}

function response($content): string
{
    $buffer = ob_get_clean();

    if (empty($buffer)) $buffer = '';

    if (is_array($content) || is_object($content)) {
        $buffer .= json_encode($content, JSON_PRETTY_PRINT);
        $contentType = 'application/json';
    } else {
        $buffer .= $content;
        $contentType = 'text/html';
    }

    header('HTTP/1.1 200 OK');
    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . strlen($buffer));

    return $buffer;
}

function view(string $template, array $vars = []): string
{
    $view = new View();
    return response($view->setTemplate($template)->setVariables($vars)->render());
}

function database(): \PDO
{
    $config = config('database');
    if (!$config) throw new \LogicException('Database connection is not defined.');
    $database = new Database($config);
    return $database->getConnection();
}