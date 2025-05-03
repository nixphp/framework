<?php

namespace NixPHP;

use Psr\Http\Message\ResponseInterface;

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

/**
 * @param string $template
 * @param array $vars
 * @return ResponseInterface
 * @internal
 */
function simple_render(string $template, array $vars = []): ResponseInterface
{
    return response(simple_view($template, $vars));
}

/**
 * @param string $template
 * @param array $vars
 * @return string
 * @internal
 */
function simple_view(string $template, array $vars = []): string
{
    ob_start();
    extract($vars);
    include $template;
    return ob_get_clean();
}
