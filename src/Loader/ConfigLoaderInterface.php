<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Config\Loader;

use Juzdy\Config\ConfigInterface;

interface ConfigLoaderInterface
{
    /**
     * Load configuration data from a specified path and merge it into the provided Config instance using the specified strategy.
     *
     * @param ConfigInterface $config   The Config instance to load data into
     * @param string $path     The path to the configuration file or directory
     * @param string $strategy The strategy to use for merging configuration data (default: 'merge')
     * 
     * @return void
     */
    public function load(ConfigInterface $config, string $path, string $strategy = 'merge'): void;
}