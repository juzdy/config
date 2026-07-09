<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Config\Strategy;

final class ExtendStrategy implements ConfigStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function apply(array $current, array $incoming): array
    {
        return $this->extendRecursive($current, $incoming);
    }

    /**
     * Recursively extends the current configuration with the incoming configuration.
     *
     * For associative arrays, values are merged recursively. For indexed arrays, missing values from the incoming array are appended to the current array. Scalar values in the current configuration take precedence over incoming values in case of conflicts.
     *
     * @param array $current  The current configuration data
     * @param array $incoming The incoming configuration data to be merged
     * 
     * @return array The resulting configuration after applying the extend strategy
     */
    private function extendRecursive(array $current, array $incoming): array
    {
        foreach ($incoming as $key => $value) {
            if (!array_key_exists($key, $current)) {
                $current[$key] = $value;
                continue;
            }

            $existing = $current[$key];

            if (is_array($existing) && is_array($value)) {
                $existingAssoc = $this->isAssoc($existing);
                $valueAssoc = $this->isAssoc($value);

                if ($existingAssoc && $valueAssoc) {
                    $current[$key] = $this->extendRecursive($existing, $value);
                    continue;
                }

                if (!$existingAssoc && !$valueAssoc) {
                    $current[$key] = $this->appendMissing($existing, $value);
                    continue;
                }
            }

            // scalar conflict: existing value wins
        }

        return $current;
    }

    /**
     * Append missing values from the incoming array to the current array.
     *
     * @param array $current  The current array
     * @param array $incoming The incoming array
     * 
     * @return array The resulting array after appending missing values
     */
    private function appendMissing(array $current, array $incoming): array
    {
        foreach ($incoming as $value) {
            if (!in_array($value, $current, true)) {
                $current[] = $value;
            }
        }

        return array_values($current);
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