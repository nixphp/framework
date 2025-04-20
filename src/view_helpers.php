<?php

namespace PHPico;

use PHPico\Core\Asset;
use PHPico\Core\View;
use PHPico\Http\Response;

function render(string $template, array $vars = []): Response
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