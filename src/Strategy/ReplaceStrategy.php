<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Config\Strategy;

class ReplaceStrategy implements ConfigStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function apply(array $current, array $incoming): array
    {
        return $this->replaceRecursive($current, $incoming);
    }

    /**
     * Recursively replaces values in the current configuration with values from the incoming configuration.
     *
     * @param array $current  The current configuration data
     * @param array $incoming The incoming configuration data to be merged
     * 
     * @return array The resulting configuration after applying the replace strategy
     */
    private function replaceRecursive(array $current, array $incoming): array
    {
        foreach ($incoming as $key => $value) {
            if (
                array_key_exists($key, $current)
                && is_array($current[$key])
                && is_array($value)
                && $this->isAssoc($current[$key])
                && $this->isAssoc($value)
            ) {
                $current[$key] = $this->replaceRecursive($current[$key], $value);
                continue;
            }

            $current[$key] = $value;
        }

        return $current;
    }

    /**
     * Determine if an array is associative.
     *
     * @param array $array The array to check
     * 
     * @return bool True if the array is associative, false otherwise
     */
    private function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}