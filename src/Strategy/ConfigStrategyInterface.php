<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Config\Strategy;

interface ConfigStrategyInterface
{
    /**
     * Apply the configuration strategy to merge incoming configuration data with the current configuration.
     *
     * @param array $current  The current configuration data
     * @param array $incoming The incoming configuration data to be merged
     * 
     * @return array The resulting configuration after applying the strategy
     */
    public function apply(array $current, array $incoming): array;
}
