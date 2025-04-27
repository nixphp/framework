<?php

namespace PHPico;

use PHPico\Core\Asset;
use PHPico\Core\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

function render(string $template, array $vars = []): ResponseInterface
{
    return response(view($template, $vars));
}

function view(string $template, array $vars = []): string
{
    return (new View())->setTemplate($template)->setVariables($vars)->render();
}

function asset(): Asset
{
    return app()->container()->get('asset');
}

/**
 * Sanitize either a string or an array
 *
 * @param string|array $value
 * @return string|array
 */
function s(string|array $value): string|array
{
    return guard()->safeOutput($value);
}

function memory(string $key, mixed $default = null):? string
{
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
    return $input === $value ? 'checked' : '';
}

function memory_selected(string $key, mixed $expectedValue): string
{
    $input = memory($key);
    return $input == $expectedValue ? 'selected' : '';
}