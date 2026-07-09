<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Config\Strategy;

final class MergeStrategy implements ConfigStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(array $current, array $incoming): array
    {
        return $this->mergeRecursive($current, $incoming);
    }

    /**
     * Recursively merges two arrays according to the following rules:
     * - If a key exists in the incoming array but not in the current array, it is added to the result.
     * - If a key exists in both arrays and both values are associative arrays, they are merged recursively.
     * - If a key exists in both arrays and both values are indexed arrays, they are concatenated and re-indexed.
     * - In all other cases, the value from the incoming array overwrites the value from the current array.
     *
     * @param array $current  The current configuration data
     * @param array $incoming The incoming configuration data to be merged
     * 
     * @return array The resulting configuration after merging
     */
    private function mergeRecursive(array $current, array $incoming): array
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
                    $current[$key] = $this->mergeRecursive($existing, $value);
                    continue;
                }

                if (!$existingAssoc && !$valueAssoc) {
                    $current[$key] = array_values(array_merge($existing, $value));
                    continue;
                }
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