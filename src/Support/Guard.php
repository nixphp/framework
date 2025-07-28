<?php

namespace NixPHP\Support;

class Guard
{
    protected GuardRegistry $registry;

    public function __construct()
    {
        $this->registry = new GuardRegistry();
    }

    public function register(string $name, callable $callback): void
    {
        $this->registry->register($name, $callback);
    }

    public function run(string $name, ...$payload): mixed
    {
        return $this->registry->run($name, ...$payload);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->run($name, ...$arguments);
    }

}