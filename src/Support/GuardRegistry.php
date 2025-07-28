<?php

namespace NixPHP\Support;

class GuardRegistry
{
    /**
     * @var array<string, callable>
     */
    protected array $guards = [];

    public function register(string $name, callable $callback): void
    {
        $this->guards[$name] = $callback;
    }

    public function has(string $name): bool
    {
        return isset($this->guards[$name]);
    }

    private function is(string $type, string $name): bool
    {
        return match ($type) {
            'callable' => is_callable($this->guards[$name]),
            'object' => is_object($this->guards[$name]),
            default => false,
        };
    }

    public function run(string $name, ...$payload): mixed
    {
        if ($this->has($name) && $this->is('callable', $name)) {
            return $this->guards[$name](...$payload);
        }

        return null;
    }
}