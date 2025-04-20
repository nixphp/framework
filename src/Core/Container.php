<?php

namespace PHPico\Core;

class Container
{

    private array $services = [];

    public function get(string $name): object
    {
        if (!isset($this->services[$name])) {
            throw new \LogicException("Service '$name' not found");
        }

        if ($this->services[$name] instanceof \Closure) { // Transform factory into the actual service
            $this->services[$name] = call_user_func($this->services[$name], $this);
        }

        return $this->services[$name];
    }

    public function set(string $name, callable|object $factory): void
    {
        $this->services[$name] = $factory;
    }

    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    public function reset(string $name)
    {
        if ($this->has($name)) {
            unset($this->services[$name]);
        }
    }

}