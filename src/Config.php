<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Config;

use Juzdy\Config\Exception\ConfigException;
use Juzdy\Config\Loader\ConfigLoader;
use Juzdy\Config\Loader\ConfigLoaderInterface;
use Juzdy\Config\Strategy\ConfigStrategyInterface;
use Juzdy\Config\Strategy\ExtendStrategy;
use Juzdy\Config\Strategy\MergeStrategy;
use Juzdy\Config\Strategy\ReplaceStrategy;
use Juzdy\Container\Attribute\Shared;
use Juzdy\Container\Contract\Lifecycle\SharedInterface;

#[Shared]
class Config implements ConfigInterface, SharedInterface
{
    protected array $items = [];

    /**
     * @var array<string, ConfigStrategyInterface>
     */
    protected array $strategies = [];

    public function __construct(
        array $items = [],
        protected ?ConfigLoaderInterface $loader = null,
    ) {
        $this->items = $items;
        $this->loader ??= new ConfigLoader();

        $this->strategies = [
            'merge'   => new MergeStrategy(),
            'replace' => new ReplaceStrategy(),
            'extend'  => new ExtendStrategy(),
        ];
    }

    public function __invoke(string $key): mixed
    {
        return $this->get($key);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function set(string $key, mixed $value): static
    {
        if ($key === null || $key === '') {
            if (!is_array($value)) {
                throw new ConfigException('Root config value must be array.');
            }

            $this->items = $value;
            return $this;
        }

        $segments = $this->segments((string) $key);
        $target =& $this->items;

        foreach ($segments as $segment) {
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }

            $target =& $target[$segment];
        }

        $target = $value;

        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = $this->segments((string) $key);
        $target = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($target) || !array_key_exists($segment, $target)) {
                return $default;
            }

            $target = $target[$segment];
        }

        return $target;
    }

    public function has(string $key): bool
    {
        $segments = $this->segments((string) $key);
        $target = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($target) || !array_key_exists($segment, $target)) {
                return false;
            }

            $target = $target[$segment];
        }

        return true;
    }

    public function remove(string $key): static
    {
        if ($key === '') {
            $this->items = [];
            return $this;
        }

        $segments = $this->segments((string) $key);
        $last = array_pop($segments);

        $target =& $this->items;

        foreach ($segments as $segment) {
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                return $this;
            }

            $target =& $target[$segment];
        }

        if ($last !== null && is_array($target) && array_key_exists($last, $target)) {
            unset($target[$last]);
        }

        return $this;
    }

    public function clear(): static
    {
        $this->items = [];
        return $this;
    }

    public function merge(array $data): static
    {
        return $this->apply($data, 'merge');
    }

    public function replace(array $data): static
    {
        return $this->apply($data, 'replace');
    }

    public function extend(array $data): static
    {
        return $this->apply($data, 'extend');
    }

    public function load(string $path, string $strategy = 'merge'): static
    {
        $this->loader->load($this, $path, $strategy);
        return $this;
    }

    public function apply(array $data, string $strategy = 'merge'): static
    {
        $resolver = $this->strategies[$strategy] ?? null;

        if (!$resolver) {
            throw new ConfigException(
                sprintf('Unknown config strategy "%s".', $strategy)
            );
        }

        $this->items = $resolver->apply($this->items, $data);

        return $this;
    }

    public function strategy(string $name, ConfigStrategyInterface $strategy): static
    {
        $this->strategies[$name] = $strategy;
        return $this;
    }

    public function import(string $file, string $strategy = 'merge'): static
    {
        return $this->load($file, $strategy);
    }

    protected function segments(string $key): array
    {
        return array_values(
            array_filter(
                explode('.', $key),
                static fn (string $segment): bool => $segment !== ''
            )
        );
    }
}