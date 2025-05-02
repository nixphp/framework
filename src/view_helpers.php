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
    return guard()->safeOutput($value);
}

