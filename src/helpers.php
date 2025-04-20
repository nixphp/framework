<?php

namespace PHPico;

// Forms

function memory(string $key, mixed $default = null)
{
    return request()->body()->get($key)
        ?? request()->query()->get($key, $default);
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

// Http
function abort(int $statusCode = 404, string $message = ''): string
{
    http_response_code($statusCode);
    echo view('errors.' . $statusCode, [
        'statusCode' => $statusCode,
        'message' => s($message)
    ]);
    exit(0);

}

