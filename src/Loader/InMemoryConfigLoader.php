<?php

namespace Juzdy\Config\Loader;

/**
 * Test-oriented loader that returns a fixed configuration array.
 */
class InMemoryConfigLoader implements ConfigLoaderInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @param string $configDir Base directory that contains config files.
     * @return array<string, mixed>
     */
    public function load(string $configDir): array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
