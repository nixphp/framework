<?php

namespace PHPico\Support;

class Collection
{
    protected array $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }
}
