<?php

namespace NixPHP\Support;

class Guard
{

    public function safePath(string $path): string
    {
        if (
            $path === '' ||
            str_contains($path, '..') ||
            str_starts_with($path, '/') ||
            str_contains($path, '://') ||
            !preg_match('/^[A-Za-z0-9_\/.-]+$/', $path)
        ) {
            throw new \InvalidArgumentException('Insecure path detected! Please find another solution.');
        }

        return $path;
    }

    public function safeOutput(string|array $value): string|array
    {
        if (is_array($value)) {
            return array_map(fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8'), $value);
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

}