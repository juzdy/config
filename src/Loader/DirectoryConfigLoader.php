<?php

namespace Juzdy\Config\Loader;

use Juzdy\Config\Exception\InvalidArgumentException;

/**
 * Loads configuration arrays from PHP files in a directory tree.
 */
class DirectoryConfigLoader implements ConfigLoaderInterface
{
    /**
     * @param mixed $configDir Base directory that contains config files.
     * @return array<string, mixed>
     */
    public function load(mixed $configDir): array
    {
        if (!is_string($configDir)) {
            throw new InvalidArgumentException(sprintf('Expected string for config directory, got %s', get_debug_type($configDir)));
        }

        $baseDir = rtrim((string)$configDir, DIRECTORY_SEPARATOR);
        if ($baseDir === '' || !is_dir($baseDir)) {
            throw new InvalidArgumentException(sprintf('Invalid config directory: %s', $baseDir));
        }

        $data = [];
        foreach ($this->collectConfigFiles($baseDir) as $file) {
            $config = include $file;
            if (!is_array($config)) {
                continue;
            }

            $scope = $this->getScopePath($baseDir, $file);
            $data = $this->mergeConfig($data, $scope, $config);
        }

        return $data;
    }

    /**
     * @param string $baseDir Base directory for config files.
     * @return string[]
     */
    private function collectConfigFiles(string $baseDir): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && strtolower($fileInfo->getExtension()) === 'php') {
                $files[] = $fileInfo->getPathname();
            }
        }

        usort($files, function (string $left, string $right) use ($baseDir): int {
            $depthLeft = $this->getDirectoryDepth($baseDir, $left);
            $depthRight = $this->getDirectoryDepth($baseDir, $right);

            if ($depthLeft === $depthRight) {
                return strcmp($left, $right);
            }

            return $depthRight <=> $depthLeft;
        });

        return $files;
    }

    /**
     * @param string $baseDir Base directory for config files.
     * @param string $file Full file path.
     * @return int
     */
    private function getDirectoryDepth(string $baseDir, string $file): int
    {
        $relativeDir = trim(str_replace($baseDir, '', dirname($file)), DIRECTORY_SEPARATOR);
        if ($relativeDir === '') {
            return 0;
        }

        return count(array_filter(explode(DIRECTORY_SEPARATOR, $relativeDir), 'strlen'));
    }

    /**
     * @param string $baseDir Base directory for config files.
     * @param string $file Full file path.
     * @return string
     */
    private function getScopePath(string $baseDir, string $file): string
    {
        $relativeDir = trim(str_replace($baseDir, '', dirname($file)), DIRECTORY_SEPARATOR);
        if ($relativeDir === '') {
            return '';
        }

        return implode('.', array_filter(explode(DIRECTORY_SEPARATOR, $relativeDir), 'strlen'));
    }

    /**
     * @param array<string, mixed> $data Existing data.
     * @param string $scope Dot-separated scope.
     * @param array<string, mixed> $config Config array to merge.
     * @return array<string, mixed>
     */
    private function mergeConfig(array $data, string $scope, array $config): array
    {
        if ($scope === '') {
            return array_replace_recursive($data, $config);
        }

        $parts = $this->splitPath($scope);
        $target = &$data;
        foreach ($parts as $part) {
            if (!isset($target[$part]) || !is_array($target[$part])) {
                $target[$part] = [];
            }
            $target = &$target[$part];
        }

        $target = array_replace_recursive($target, $config);

        return $data;
    }

    /**
     * @param string $key Dot-separated key.
     * @return string[]
     */
    private function splitPath(string $key): array
    {
        $key = trim($key);
        if ($key === '') {
            return [];
        }

        return array_values(array_filter(explode('.', $key), 'strlen'));
    }
}
