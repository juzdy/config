<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Config;

interface ConfigInterface
{
   /**
    * Get all configuration data as an associative array.
   *
   * @return array The configuration data
   */
    public function all(): array;

    /**
     * Set a configuration value.
     *
     * @param string $key     The configuration key (dot notation supported)
     * @param mixed  $value   The value to set
     * 
     * @return static
     */
    public function set(string $key, mixed $value): static;

    /**
     * Get a configuration value.
     *
     * @param string $key     The configuration key (dot notation supported)
     * @param mixed  $default The default value to return if the key does not exist
     * 
     * @return mixed The configuration value or default if key does not exist
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Check if a configuration key exists.
     *
     * @param string $key The configuration key (dot notation supported)
     * 
     * @return bool True if the key exists, false otherwise
     */
    public function has(string $key): bool;

    /**
     * Remove a configuration key.
     *
     * @param string $key The configuration key (dot notation supported)
     * 
     * @return static
     */
    public function remove(string $key): static;

    /**
     * Clear all configuration data.
     *
     * @return static
     */
    public function clear(): static;

    /**
     * Merge an array of configuration data.
     *
     * @param array $data The configuration data to merge
     * 
     * @return static
     */
    public function merge(array $data): static;

    /**
     * Replace the entire configuration with a new array.
     *
     * @param array $data The new configuration data
     * 
     * @return static
     */
    public function replace(array $data): static;

    /**
     * Extend the configuration with another array, allowing for overrides.
     *
     * @param array $data The configuration data to extend with
     * 
     * @return static
     */
    public function extend(array $data): static;

    /**
     * Load configuration from a file.
     *
     * @param string $path The file path to load configuration from
     * @param string $strategy The loading strategy ('merge' or 'replace')
     * 
     * @return static
     */
    public function load(string $path, string $strategy = 'merge'): static;
}