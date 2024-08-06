<?php
declare(strict_types=1);

namespace Serapha\Core;

use Serapha\Exception\InitializationException;

final class Config
{
    private array $config;

    public function __construct(string $configFilePath)
    {
        if (!file_exists($configFilePath)) {
            throw new InitializationException("Configuration file does not exist: [{$configFilePath}]");
        }
        $this->config = require $configFilePath;
    }

    /**
     * Get a configuration value.
     *
     * @param string $key The configuration key.
     * @param mixed $default The default value if the key does not exist.
     * @return mixed The configuration value or the default value.
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Get all configuration values.
     * @return array 
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Get an environment value.
     *
     * @param string $key The environment key.
     * @param mixed $default The default value if the key does not exist.
     * @return mixed The environment value or the default value.
     */
    public function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }

    /**
     * Set a configuration value.
     *
     * @param string $key The configuration key.
     * @param mixed $value The value to set.
     */
    public function set(string $key, $value): void
    {
        $this->config[$key] = $value;
    }
}
