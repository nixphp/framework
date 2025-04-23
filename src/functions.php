<?php

namespace PHPico;

require_once __DIR__ . '/../vendor/autoload.php';

use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use PHPico\Core\App;
use PHPico\Core\Config;
use PHPico\Core\Event;
use PHPico\Core\Log;
use PHPico\Core\Route;
use PHPico\Core\Container;
use PHPico\Support\Session;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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

function config(?string $key = null, mixed $default = null): array|object|string|null
{
    /** @var Config $config */
    $config = app()->container()->get('config');

    if (empty($key)) {
        return $config;
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

function request(): RequestInterface
{
    return app()->request();
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
    send_response(response(view('errors.' . $statusCode, [
        'statusCode' => $statusCode,
        'message' => s($message)
    ]), $statusCode));
}

function send_response(ResponseInterface $response): never
{
    if (ob_get_length() > 0) ob_end_clean();

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
    exit(0);
}

function session(): Session
{
    return \PHPico\app()->session();
}

function event(): Event
{
    return app()->container()->get('event');
}

function database(): \PDO
{
    return app()->container()->get('database');
}

function log(): Log
{
    return app()->container()->get('log');
}