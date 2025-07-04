<?php

namespace NixPHP\Core;

use NixPHP\Exceptions\ContainerException;
use NixPHP\Exceptions\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{

    private array $services = [];

    /**
     * @param string $id
     * @return mixed
     *
     * @throws ServiceNotFoundException
     * @throws ContainerException
     */
    public function get(string $id): mixed
    {
        if (!isset($this->services[$id])) {
            throw new ServiceNotFoundException("Service '$id' not found.");
        }

        if ($this->services[$id] instanceof \Closure) {
            try {
                $this->services[$id] = call_user_func($this->services[$id], $this);
            } catch (\Throwable $e) {
                throw new ContainerException($e->getMessage());
            }
        }

        return $this->services[$id];
    }

    public function set(string $id, callable|object $factory): void
    {
        $this->services[$id] = $factory;
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    public function reset(string $id): void
    {
        unset($this->services[$id]);
    }

}