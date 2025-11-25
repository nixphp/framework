<?php
declare(strict_types=1);

namespace NixPHP;

ob_start();

if (!defined('NIXPHP_BASE_PATH')) {
    define('NIXPHP_BASE_PATH', dirname(__DIR__));
}

use NixPHP\Enum\Environment;
use NixPHP\Enum\EnvironmentInterface;
use NixPHP\Enum\Event;
use NixPHP\Support\AppHolder;
use NixPHP\Support\Guard;
use NixPHP\Support\RequestParameter;
use NixPHP\Support\Stopwatch;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use NixPHP\Core\App;
use NixPHP\Core\Config;
use NixPHP\Core\ErrorHandler;
use NixPHP\Core\EventManager;
use NixPHP\Core\Route;
use NixPHP\Exceptions\AbortException;
use NixPHP\Support\Plugin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use stdClass;

if (getenv('APP_ENV') !== Environment::TEST->value
    && getenv('APP_ENV') !== Environment::PROD->value
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
    $config = app()->container()->get(Config::class);

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
    $route = app()->container()->get(Route::class);

    if (null === $name) {
        return $route;
    }

    return $route->url($name, $params);
}

/**
 * Get all plugins or one specific
 *
 * @method getFromAll()
 */
function plugin(?string $name = null): Plugin|stdClass
{
    if ($name) {
        return app()->getPlugin($name);
    }

    return new class extends stdClass {

        public function getFromAll(string $type)
        {
            $all = app()->getPlugins();
            $getter = 'get' . ucfirst($type);
            $result = [];
            foreach ($all as $plugin) {
                $resp = $plugin->$getter();

                if (is_array($resp)) {
                    $result = array_merge($result, $resp);
                    continue;
                }

                $result[] = $resp;
            }
            return $result;
        }

    };
}

/**
 * Get current request instance
 */
function request(): ServerRequestInterface|RequestInterface
{
    return app()->container()->get(RequestInterface::class);
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
    return app()->container()->get(RequestParameter::class);
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
        '2',
        $status === 301 ? 'Moved permanently' : 'Found'
    );
}

/**
 * Create a refresh response redirecting to the current path
 */
function refresh(): ResponseInterface
{
    $request = app()->container()->get(RequestInterface::class);
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

    $eventResponses = event()->dispatch(Event::RESPONSE_HEADER, $response);
    $eventResponses = array_filter($eventResponses, fn($response) => $response instanceof ResponseInterface);
    if (!empty($eventResponses)) {
        $response = end($eventResponses);
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

    event()->dispatch(Event::RESPONSE_BODY, $response);

    echo $response->getBody();

    event()->dispatch(Event::RESPONSE_END, $response);

    exit(0);
}

/**
 * Get the current environment
 *
 * @return EnvironmentInterface
 */
function env(): EnvironmentInterface
{
    return app()->container()->get(EnvironmentInterface::class);
}

/**
 * Get the event dispatcher instance
 */
function event(): EventManager
{
    return app()->container()->get(EventManager::class);
}

/**
 * Get logger instance
 */
function log(): LoggerInterface
{
    return app()->container()->get(LoggerInterface::class);
}

/**
 * Get the guard instance
 */
function guard(): Guard
{
    return app()->guard();
}