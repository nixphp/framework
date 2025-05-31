<?php

namespace NixPHP;

ob_start();

if (!defined('NIXPHP_BASE_PATH')) {
    define('NIXPHP_BASE_PATH', dirname(__DIR__));
}

use NixPHP\Support\RequestParameter;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use NixPHP\Core\App;
use NixPHP\Core\Config;
use NixPHP\Core\Environment;
use NixPHP\Core\ErrorHandler;
use NixPHP\Core\Event;
use NixPHP\Core\Log;
use NixPHP\Core\Route;
use NixPHP\Core\Container;
use NixPHP\Exceptions\AbortException;
use NixPHP\Support\Guard;
use NixPHP\Support\Plugin;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

if (getenv('APP_ENV') !== Environment::TESTING
    && getenv('APP_ENV') !== Environment::PRODUCTION
) {
    set_error_handler([ErrorHandler::class, 'handleError']);
    ini_set('display_errors', false);
}

global $container;
$container = new Container();

function app(): App
{
    global $container;

    if (!isset($container)) {
        throw new \LogicException('NixPHP not bootstrapped. Did you forget to include bootstrap_runtime.php?');
    }

    if (!$container->has('app')) {
        echo "New";
        $container->set('app', function($container) { return new App($container); });
    }

    return $container->get('app');
}

function config(?string $key = null, mixed $default = null): mixed
{
    /** @var Config $config */
    $config = app()->container()->get('config');

    if (empty($key)) {
        return $config->all();
    }

    return $config->get($key, $default);
}

function route(?string $name = null, array $params = []): Route|string
{
    /* @var Route $route */
    $route = app()->container()->get('route');

    if (null === $name) {
        return $route;
    }

    return $route->url($name, $params);
}

function plugin(): Plugin
{
    return app()->container()->get('plugin');
}

function request(): ServerRequestInterface
{
    return app()->container()->get('request');
}

function response(mixed $content = '', int $status = 200, array $headers = []): ResponseInterface
{
    return new Response($status, $headers, $content);
}

function param(): RequestParameter
{
    return app()->container()->get('parameter');
}

function json(mixed $data, int $status = 200, array $headers = []): ResponseInterface
{
    $body = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if (false === $body) {
        throw new \RuntimeException('Unable to encode data to JSON: ' . json_last_error_msg());
    }

    $stream = Stream::create($body);

    $headers = array_merge([
        'Content-Type' => 'application/json; charset=UTF-8',
    ], $headers);

    return response($stream, $status, $headers);
}

function redirect(string $url, int $status = 302): ResponseInterface
{
    return new Response(
        $status,
        ['Location' => $url],
        null,
        '1.1',
        $status === 301 ? 'Moved permanently' : 'Found'
    );
}

function refresh(): ResponseInterface
{
    /** @var ServerRequestInterface $request */
    $request = app()->container()->get('request');
    return redirect($request->getUri());
}

function abort(int $statusCode = 404, string $message = ''): never
{
    throw new AbortException(htmlspecialchars($message), $statusCode);
}

function send_response(ResponseInterface $response): never
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header(sprintf(
        'HTTP/%s %d %s',
        $response->getProtocolVersion(),
        $response->getStatusCode(),
        $response->getReasonPhrase()
    ));

    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header("$name: $value", false);
        }
    }

    echo $response->getBody();

    event()->dispatch('request.end', $response);

    exit(0);
}

function env(?string $key = null, mixed $default = null): mixed
{
    $env = app()->container()->get('environment');

    if (empty($key)) {
        return $env;
    }

    return $env->get($key, $default);
}

function event(): Event
{
    return app()->container()->get('event');
}

function log(): Log
{
    return app()->container()->get('log');
}

function guard(): Guard
{
    return app()->guard();
}