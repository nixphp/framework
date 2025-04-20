<?php

namespace PHPico\Core;

class Config
{

    private array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param string                       $key
     * @param string|array|object|int|null $default
     *
     * @return string|array|object|null
     */
    public function get(string $key, string|array|object|int $default = null): string|array|object|null
    {
        $result = $this->config[$key] ?? null;
        if (str_contains($key, ':') !== false) {
            $result = $this->resolveNamespace($key);
        }
        return $result ?? $default;
    }

    /**
     * @param string $namespace
     * @return string|array|object|null
     */
    private function resolveNamespace(string $namespace): string|array|object|null
    {
        $parts   = explode(':', $namespace);
        $pointer = $this->config;
        foreach ($parts as $part) {
            $pointer = $pointer[$part] ?? null;
        }
        return $pointer;
    }


}