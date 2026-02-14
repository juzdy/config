<?php
namespace Juzdy\Config;

interface ConfigInterface
{
    /**
     * Get a configuration value by key with an optional default.
     *
     * @param string $key Dot-separated key for nested values.
     * @param mixed $default Default value if key is not found.
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Check if a configuration key exists.
     *
     * @param string $key Dot-separated key for nested values.
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Get all configuration values.
     *
     * @return array
     */
    public function all(): array;

    /**
     * Load configuration data from a directory.
     *
     * @param string $configDir Directory containing configuration files.
     * @return static
     */
    public function load(string $configDir): static;

    /**
     * Merge additional configuration data into the existing configuration.
     *
     * @param array $data Configuration data to merge.
     * @return static
     */
    public function merge(array $data): static;

    /**
     * Merge additional configuration data into a specific path in the existing configuration.
     *
     * @param string $path Dot-separated path to merge into.
     * @param array $data Configuration data to merge.
     * @return static
     */
    public function mergeTo(string $path, array $data): static;

}