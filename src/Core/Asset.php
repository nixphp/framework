<?php

namespace NixPHP\Core;

use function NixPHP\s;

class Asset
{
    private const string TAG_CSS = '<link rel="stylesheet" href="%s">';
    private const string TAG_JS = '<script src="%s"></script>';
    
    protected array $assets = [
        'css' => [],
        'js' => [],
    ];

    public function add(string $path): void
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);

        if (in_array($type, ['css', 'js'])) {
            $this->assets[$type][] = $path;
        }
    }

    public function list(string $type): array
    {
        return array_unique($this->assets[$type] ?? []);
    }

    public function render(string $type): string
    {
        $html = '';

        foreach ($this->list($type) as $path) {
            if ($type === 'css') {
                $html .= sprintf(self::TAG_CSS, s($path)) . PHP_EOL;
            } elseif ($type === 'js') {
                $html .= sprintf(self::TAG_JS, s($path)) . PHP_EOL;
            }
        }
        return trim($html);
    }
}