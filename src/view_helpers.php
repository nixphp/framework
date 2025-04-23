<?php

namespace PHPico;

use PHPico\Core\Asset;
use PHPico\Core\View;
use Psr\Http\Message\ResponseInterface;

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
    if (is_array($value)) {
        return array_map(fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8'), $value);
    }

    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

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