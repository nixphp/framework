<?php
declare(strict_types=1);

namespace NixPHP;

ob_start();

if (!defined('NIXPHP_BASE_PATH')) {
    define('NIXPHP_BASE_PATH', dirname(__DIR__));
}

use NixPHP\Support\AppHolder;
use NixPHP\Support\Guard;
use NixPHP\Support\RequestParameter;
use NixPHP\Support\Stopwatch;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use NixPHP\Core\App;
use NixPHP\Core\Config;
use NixPHP\Core\Environment;
use NixPHP\Core\ErrorHandler;
use NixPHP\Core\Event;
use NixPHP\Core\Log;
use NixPHP\Core\Route;
use NixPHP\Exceptions\AbortException;
use NixPHP\Support\Plugin;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

if (getenv('APP_ENV') !== Environment::TESTING
    && getenv('APP_ENV') !== Environment::PRODUCTION
) {
    set_error_handler([ErrorHandler::class, 'handleError']);
    ini_set('display_errors', false);
}

/**
 * Get the application instance
 */
function app(): App
{
    return AppHolder::get();
}

/**
 * Get configuration value by key
 *
 * @param string|null $key     Configuration key to retrieve
 * @param mixed       $default Default value if key not found
 *
 * @return mixed Configuration value or entire config if no key provided
 */
function config(?string $key = null, mixed $default = null): mixed
{
    /** @var Config $config */
    $config = app()->container()->get('config');

    if (empty($key)) {
        return $config->all();
    }

    return $config->get($key, $default);
}

/**
 * Get the route instance or generate URL for a named route
 *
 * @param string|null $name   Route name
 * @param array       $params Route parameters
 *
 * @return Route|string Route instance or generated URL
 */
function route(?string $name = null, array $params = []): Route|string
{
    /* @var Route $route */
    $route = app()->container()->get('route');

    if (null === $name) {
        return $route;
    }

    return $route->url($name, $params);
}

/**
 * Get the plugin manager instance
 */
function plugin(): Plugin
{
    return app()->container()->get('plugin');
}

/**
 * Get current request instance
 */
function request(): ServerRequestInterface
{
    return app()->container()->get('request');
}

/**
 * Create a new response
 *
 * @param mixed $content Response content
 * @param int   $status  HTTP status code
 * @param array $headers Response headers
 */
function response(mixed $content = '', int $status = 200, array $headers = []): ResponseInterface
{
    return new Response($status, $headers, $content);
}

/**
 * Get request parameter handler instance
 */
function param(): RequestParameter
{
    return app()->container()->get('parameter');
}

/**
 * Create JSON response
 *
 * @param mixed $data    Data to encode as JSON
 * @param int   $status  HTTP status code
 * @param array $headers Response headers
 */
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

/**
 * Create a redirect response
 *
 * @param string $url    Target URL
 * @param int    $status HTTP status code
 */
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

/**
 * Create a refresh response redirecting to the current path
 */
function refresh(): ResponseInterface
{
    /** @var ServerRequestInterface $request */
    $request = app()->container()->get('request');
    return redirect($request->getUri()->getPath());
}

/**
 * Abort request with status code and message
 *
 * @param int    $statusCode HTTP status code
 * @param string $message    Error message
 *
 * @throws AbortException
 */
function abort(int $statusCode = 404, string $message = ''): never
{
    throw new AbortException(htmlspecialchars($message), $statusCode);
}

/**
 * Send a response to the client
 *
 * @param ResponseInterface $response Response to send
 */
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

    event()->dispatch('request.end', $response, Stopwatch::stop('app'));

    exit(0);
}

/**
 * Get environment value
 *
 * @param string|null $key     Environment key
 * @param mixed       $default Default value if key not found
 *
 * @return mixed Environment value or entire environment if no key provided
 */
function env(?string $key = null, mixed $default = null): mixed
{
    $env = app()->container()->get('environment');

    if (empty($key)) {
        return $env;
    }

    return $env->get($key, $default);
}

/**
 * Get the event dispatcher instance
 */
function event(): Event
{
    return app()->container()->get('event');
}

/**
 * Get logger instance
 */
function log(): Log
{
    return app()->container()->get('log');
}

/**
 * Get the guard instance
 */
function guard(): Guard
{
    return app()->guard();
}