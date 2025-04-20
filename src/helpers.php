<?php

use function PHPico\request;

// Forms

function memory(string $key, mixed $default = null) {
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
function abort(int $statusCode = 404, string $message = '') {

    http_response_code($statusCode);
    $message = htmlspecialchars($message);

    echo <<<HTML
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Fehler</title>
        <style>
            body { 
                font-family: sans-serif; 
                background: #121212; 
                color: #e0e0e0; 
                padding: 2rem; 
                margin: 0;
            }
            .error-box { 
                background: #1e1e1e; 
                border-radius: 8px; 
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); 
                padding: 1.5rem; 
                max-width: 800px; 
                margin: 2rem auto; 
            }
            h1 { color: #ff6f61; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>Fehler {$statusCode}</h1>
            <p>{$message}</p>
        </div>
    </body>
    </html>
    HTML;
    exit; // Wichtig: Sofort beenden

}