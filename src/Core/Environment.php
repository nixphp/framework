<?php
declare(strict_types=1);

namespace NixPHP\Core;

class Environment
{
    public const string PRODUCTION = 'production';
    public const string STAGING = 'staging';
    public const string LOCAL = 'local';
    public const string TESTING = 'testing';
    private string $environment;

    /**
     * Creates a new Environment instance
     *
     * @param string|null $environment Environment name to set
     */
    public function __construct(string $environment = null)
    {
        $this->environment = $environment;
    }

    /**
     * Checks if current environment is production
     *
     * @return bool True if production environment, false otherwise
     */
    public function isProduction(): bool
    {
        return $this->environment === static::PRODUCTION;
    }

    /**
     * Checks if the current environment is staging
     *
     * @return bool True if staging environment, false otherwise
     */
    public function isStaging(): bool
    {
        return $this->environment === static::STAGING;
    }

    /**
     * Checks if the current environment is local development
     *
     * @return bool True if local environment, false otherwise
     */
    public function isLocal(): bool
    {
        return $this->environment === static::LOCAL;
    }

    /**
     * Checks if the current environment is testing
     *
     * @return bool True if testing environment, false otherwise
     */
    public function isTesting(): bool
    {
        return $this->environment === static::TESTING;
    }

    /**
     * Gets the current environment name
     *
     * @return string Current environment name
     */
    public function get(): string
    {
        return $this->environment;
    }

}