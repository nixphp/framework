<?php

namespace PHPico;

// Forms

use PHPico\Support\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

function memory(string $key, mixed $default = null): string
{
    /** @var ServerRequestInterface $request */
    $request = app()->request();

    $parsedBody = $request->getParsedBody();
    $queryParams = $request->getQueryParams();

    // parsedBody kann null sein (bei GET-Requests)
    if (is_array($parsedBody) && array_key_exists($key, $parsedBody)) {
        return $parsedBody[$key];
    }

    // Fallback auf Query-Parameter
    return $queryParams[$key] ?? $default;
}

function memory_checked(string $key, mixed $value = 'on'): string
{
    $input = memory($key);
    if (is_array($input)) {
        return in_array($value, $input, true) ? 'checked' : '';
    }
    return $input === $value ? 'checked' : '';
}

function memory_selected(string $key, mixed $expectedValue): string
{
    $input = memory($key);
    if (is_array($input)) {
        return in_array($expectedValue, $input, true) ? 'selected' : '';
    }
    return $input == $expectedValue ? 'selected' : '';
}

function session(): Session
{
    return app()->session();
}

// Http
function abort(int $statusCode = 404, string $message = ''): never
{
    $response = response(view('errors.' . $statusCode, [
        'statusCode' => $statusCode,
        'message' => s($message)
    ]), 500);
    send_response($response);
    exit(0);
}

function send_response(ResponseInterface $response): void
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

