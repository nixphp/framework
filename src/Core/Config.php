<?php

namespace PHPico\Core;

class Config
{

    private array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $this->resolveEnv($config);
    }

    /**
     * @param string                       $key
     * @param string|array|object|int|null $default
     *
     * @return string|array|object|null
     */
    public function get(string $key, string|array|object|int|null $default = null): string|array|object|null
    {
        $result = $this->config[$key] ?? null;
        if (str_contains($key, ':') !== false) {
            $result = $this->resolveNamespace($key);
        }
        return $result ?? $default;
    }

    public function all(): array
    {
        return $this->config;
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

    function resolveEnv(array $config): array
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $config[$key] = $this->resolveEnv($value);
            } elseif (is_string($value) && str_starts_with($value, 'ENV:')) {
                $envKey = substr($value, 4);
                $config[$key] = $_ENV[$envKey] ?? null;
            }
        }
        return $config;
    }

}