<?php

namespace Juzdy\Config\Loader;

/**
 * Loads configuration arrays from a directory.
 */
interface ConfigLoaderInterface
{
    /**
     * @param mixed $source Base directory that contains config files.
     * @return array<string, mixed>
     */
    public function load(mixed $source): array;
}
