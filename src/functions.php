<?php

namespace PHPico;

ob_start();

if (!defined('PHPICO_BASE_PATH')) {
    define('PHPICO_BASE_PATH', dirname(__DIR__, 2));
}

use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use PHPico\Core\App;
use PHPico\Core\Config;
use PHPico\Core\Environment;
use PHPico\Core\ErrorHandler;
use PHPico\Core\Event;
use PHPico\Core\Log;
use PHPico\Core\Route;
use PHPico\Core\Container;
use PHPico\Exceptions\AbortException;
use PHPico\Support\Guard;
use PHPico\Support\Plugin;
use PHPico\Support\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

if (getenv('APP_ENV') !== Environment::TESTING
    && getenv('APP_ENV') !== Environment::PRODUCTION
) {
    //set_exception_handler([ErrorHandler::class, 'handleException']);
    set_error_handler([ErrorHandler::class, 'handleError']);
    ini_set('display_errors', false);
}

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
    throw new AbortException(s($message), $statusCode);
}

function send_response(ResponseInterface $response): never
{
    while (ob_get_level() > 0) {
        ob_end_clean(); // sauber leeren, aber erst jetzt – nach dem Einsammeln
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

function session(): Session
{
    return app()->session();
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