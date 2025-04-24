<?php
namespace PHPico\Support;

class Plugin
{
    protected array $meta = [
        'viewPaths' => [],
        'configPaths' => [],
        'bootstraps' => [],
    ];

    public function addMeta(string $key, string $value): void
    {
        if (!isset($this->meta[$key])) {
            $this->meta[$key] = [];
        }

        if (!in_array($value, $this->meta[$key], true)) {
            $this->meta[$key][] = $value;
        }
    }

    public function getMeta(string $key): array
    {
        return $this->meta[$key] ?? [];
    }

    public function all(): array
    {
        return $this->meta;
    }
}
