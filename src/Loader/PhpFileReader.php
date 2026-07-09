<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Config\Loader;

use Juzdy\Config\Config;
use Juzdy\Config\Exception\InvalidConfigFileException;
use Throwable;

final class PhpFileReader
{
    /**
     * Reads a PHP configuration file and returns its contents as an array.
     *
     * @param string $file   The path to the PHP configuration file
     * @param Config $config The Config instance to bind the file's scope to
     * 
     * @return array The configuration data read from the file
     * 
     * @throws InvalidConfigFileException If the file cannot be read or does not return an array or null
     */
    public function read(string $file, Config $config): array
    {
        try {
            $result = (function (string $__file): mixed {
                return require $__file;
            })->bindTo($config, $config::class)($file);
        } catch (Throwable $e) {
            throw new InvalidConfigFileException(
                sprintf('Failed to read config file "%s": %s', $file, $e->getMessage()),
                previous: $e
            );
        }

        if ($result === null) {
            return [];
        }

        if (!is_array($result)) {
            throw new InvalidConfigFileException(
                sprintf('Config file "%s" must return array or null.', $file)
            );
        }

        return $result;
    }
}