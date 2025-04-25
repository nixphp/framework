<?php

namespace PHPico\Core;

class Environment
{

    protected string $environment;

    public const string PRODUCTION = 'production';
    public const string STAGING = 'staging';
    public const string LOCAL = 'local';
    public const string TESTING = 'testing';

    public function __construct(string $environment = null)
    {
        $this->environment = $environment;
    }

    public function isProduction(): bool
    {
        return $this->environment === static::PRODUCTION;
    }

    public function isStaging(): bool
    {
        return $this->environment === static::STAGING;
    }

    public function isLocal(): bool
    {
        return $this->environment === static::LOCAL;
    }

    public function isTesting(): bool
    {
        return $this->environment === static::TESTING;
    }

    public function get(): string
    {
        return $this->environment;
    }

}