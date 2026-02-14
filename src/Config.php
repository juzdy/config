<?php

namespace Juzdy\Config;

use Juzdy\Config\Loader\ConfigLoaderInterface;
use Juzdy\Config\Loader\DirectoryConfigLoader;
use Juzdy\Config\Resolver\ConfigResolverInterface;
use Juzdy\Config\Resolver\ResolverInterface;
use Juzdy\Config\Resolver\ResolverManager;
use Juzdy\Container\Attribute\Parameter\Using;
use Juzdy\Container\Attribute\Shared;

/**
 * Loads Config arrays from PHP files and provides resolved access via dot notation.
 */
#[Shared()]
class Config implements ConfigInterface
{
	/**
	 * @var array<string, mixed>
	 */
	private array $data = [];
	
	
	public function __construct(
        #[Using(DirectoryConfigLoader::class)]
		private ConfigLoaderInterface $loader,
		private ConfigResolverInterface $resolver
	)
	{
		$this->registerDefaultResolvers();
	}

	/**
	 * Check if a Config key exists after resolution.
	 *
	 * @param string $key Dot-separated key.
	 * @return bool
	 */
	public function has(string $key): bool
	{
		$this->ensureResolved();

		return $this->hasRaw($key);
	}

	/**
	 * Get a resolved Config value by dot path.
	 *
	 * @param string $key Dot-separated key.
	 * @param mixed $default Default value when key is missing.
	 * @return mixed
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		$this->ensureResolved();

		return $this->getRaw($key, $default);
	}

	/**
	 * Set a Config value using dot notation.
	 *
	 * @param string $key Dot-separated key.
	 * @param mixed $value Value to set.
	 * @return void
	 */
	public function set(string $key, mixed $value): void
	{
		$parts = $this->splitPath($key);
		if ($parts === []) {
			return;
		}

		$data = &$this->getData();
		$lastIndex = count($parts) - 1;
		foreach ($parts as $index => $part) {
			if ($index === $lastIndex) {
				$data[$part] = $value;
				break;
			}

			if (!isset($data[$part]) || !is_array($data[$part])) {
				$data[$part] = [];
			}

			$data = &$data[$part];
		}

		$this->getResolver()->markDirty();
	}

	/**
	 * Fluent setter for Config values.
	 *
	 * @param string $key Dot-separated key.
	 * @param mixed $value Value to set.
	 * @return static
	 */
	public function with(string $key, mixed $value): static
	{
		$this->set($key, $value);

		return $this;
	}

	/**
	 * Return all resolved Config values.
	 *
	 * @return array<string, mixed>
	 */
	public function all(): array
	{
		$this->ensureResolved();

		return $this->getData();
	}

	/**
	 * Register a resolver for dynamic values.
	 *
	 * @param ResolverInterface $resolver Resolver instance.
	 * @param int $priority Higher runs first.
	 * @return void
	 */
	public function registerResolver(ResolverInterface $resolver, int $priority = 0): void
	{
		$this->getResolver()->registerResolver($resolver, $priority);
	}

	/**
	 * Fluent resolver registration.
	 *
	 * @param ResolverInterface $resolver Resolver instance.
	 * @param int $priority Higher runs first.
	 * @return static
	 */
	public function withResolver(ResolverInterface $resolver, int $priority = 0): static
	{
		$this->registerResolver($resolver, $priority);

		return $this;
	}

	/**
	 * Fluent loader registration.
	 *
	 * @param ConfigLoaderInterface $loader Config loader instance.
	 * @return static
	 */
	public function withLoader(ConfigLoaderInterface $loader): static
	{
		$this->setLoader($loader);
		$this->getResolver()->markDirty();

		return $this;
	}

	/**
	 * Resolve all Config values in place.
	 *
	 * @return void
	 */
	public function resolveAll(): void
	{
		$data = &$this->getData();
		$this->getResolver()->resolveAll($data);
	}

	/**
	 * Fluent resolver execution.
	 *
	 * @return static
	 */
	public function resolve(): static
	{
		$this->resolveAll();

		return $this;
	}

	/**
	 * Resolve a single value within the Config context.
	 *
	 * @param string $path Dot-separated path to resolve against.
	 * @param mixed $value Value to resolve.
	 * @return mixed
	 */
	public function resolveValue(string $path, mixed $value): mixed
	{
		return $this->getResolver()->resolveValue($path, $value);
	}

	/**
	 * Check if a Config key exists without forcing resolution.
	 *
	 * @param string $key Dot-separated key.
	 * @return bool
	 */
	public function hasRaw(string $key): bool
	{
		$parts = $this->splitPath($key);
		$found = false;
		$this->traverse($parts, $found);

		return $found;
	}

	/**
	 * Get a Config value without forcing resolution.
	 *
	 * @param string $key Dot-separated key.
	 * @param mixed $default Default value when key is missing.
	 * @return mixed
	 */
	public function getRaw(string $key, mixed $default = null): mixed
	{
		$parts = $this->splitPath($key);
		$found = false;
		$value = $this->traverse($parts, $found);

		return $found ? $value : $default;
	}

	/**
	 * Ensure Config values are resolved.
	 *
	 * @return void
	 */
	private function ensureResolved(): void
	{
		$data = &$this->getData();
		$this->getResolver()->ensureResolved($data);
	}

	/**
	 * Register the built-in resolvers.
	 *
	 * @return void
	 */
	private function registerDefaultResolvers(): void
	{
		$this->getResolver()->withConfig($this)->registerDefaultResolvers();
	}

	/**
	 * Load Config data from disk into the internal store.
	 *
	 * @param string $configDir Base directory that contains Config files.
	 * @return static
	 */
	public function load(string $configDir): static
	{
		$config = $this->getLoader()->load($configDir);
		if ($config !== []) {
			$this->setData(array_replace_recursive($this->getData(), $config));
		}

		$this->getResolver()->markDirty();

		return $this;
	}

	/**
	 * Merge additional Config data into the existing configuration.
	 *
	 * @param array<string, mixed> $data Config data to merge.
	 * @return static
	 */
	public function merge(array $data): static
	{
		if ($data !== []) {
			$this->setData(array_replace_recursive($this->getData(), $data));
			$this->getResolver()->markDirty();
		}

		return $this;
	}

	/**
	 * Merge additional Config data into a specific path.
	 *
	 * @param string $path Dot-separated path to merge into.
	 * @param array<string, mixed> $data Config data to merge.
	 * @return static
	 */
	public function mergeTo(string $path, array $data): static
	{
		$parts = $this->splitPath($path);
		if ($parts === [] || $data === []) {
			return $this;
		}

		$this->mergeAtPath($parts, $data);
		$this->getResolver()->markDirty();

		return $this;
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

	/**
	 * Merge configuration data at a given path.
	 *
	 * @param string[] $parts Dot-separated path parts.
	 * @param array<string, mixed> $data Config data to merge.
	 * @return void
	 */
	private function mergeAtPath(array $parts, array $data): void
	{
		$target = &$this->getData();
		$lastIndex = count($parts) - 1;
		foreach ($parts as $index => $part) {
			if ($index === $lastIndex) {
				if (!isset($target[$part]) || !is_array($target[$part])) {
					$target[$part] = [];
				}
				$target[$part] = array_replace_recursive($target[$part], $data);
				return;
			}

			if (!isset($target[$part]) || !is_array($target[$part])) {
				$target[$part] = [];
			}

			$target = &$target[$part];
		}
	}

	/**
	 * Get resolver manager instance, creating it when needed.
	 *
	 * @return ResolverManager
	 */
	/**
	 * @return ConfigResolverInterface
	 */
	private function getResolver(): ConfigResolverInterface
	{
		return $this->resolver;
	}

	/**
	 * @param ConfigResolverInterface $resolver Config resolver instance.
	 * @return void
	 */
	private function setResolver(ConfigResolverInterface $resolver): void
	{
		$this->resolver = $resolver;
	}

	/**
	 * @return ConfigLoaderInterface
	 */
	private function getLoader(): ConfigLoaderInterface
	{
		return $this->loader;
	}

	/**
	 * @param ConfigLoaderInterface $loader Config loader instance.
	 * @return void
	 */
	private function setLoader(ConfigLoaderInterface $loader): void
	{
		$this->loader = $loader;
	}

	
	/**
	 * @return array<string, mixed>
	 */
	private function &getData(): array
	{
		return $this->data;
	}

	/**
	 * @param array<string, mixed> $data Config data.
	 * @return void
	 */
	private function setData(array $data): void
	{
		$this->data = $data;
	}


	/**
	 * Traverse the Config tree using dot-separated parts.
	 *
	 * @param string[] $parts
	 * @param bool $found Output flag for successful lookup.
	 * @return mixed
	 */
	private function traverse(array $parts, bool &$found): mixed
	{
		$found = false;
		if ($parts === []) {
			return null;
		}

		$value = $this->getData();
		foreach ($parts as $part) {
			if (is_array($value) && array_key_exists($part, $value)) {
				$value = $value[$part];
				continue;
			}

			return null;
		}

		$found = true;

		return $value;
	}
}
