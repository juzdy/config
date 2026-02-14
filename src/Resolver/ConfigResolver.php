<?php

namespace Juzdy\Config\Resolver;

use Juzdy\Config\ConfigInterface;

/**
 * Default configuration resolver implementation.
 */
class ConfigResolver implements ConfigResolverInterface
{
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var ResolverManager
     */
    private ResolverManager $resolverManager;

    /**
     * @var bool
     */
    private bool $resolved = false;

    /**
     * @param ConfigInterface $config Config instance.
     * @param ResolverManager $resolverManager Resolver manager instance.
     */
    public function __construct(ResolverManager $resolverManager)
    {
        $this->setResolverManager($resolverManager);
    }

    public function withConfig(ConfigInterface $config): static
    {
        $this->setConfig($config);

        return $this;
    }

    /**
     * Register built-in resolvers.
     *
     * @return void
     */
    public function registerDefaultResolvers(): void
    {
        $this->getResolverManager()
            ->register(new ExtendsResolver($this->getConfig()), 200)
            ->register(new EnvResolver($this->getConfig()), 110)
            ->register(new ReferenceResolver($this->getConfig()), 100);
    }

    /**
     * Register a resolver with priority (higher runs first).
     *
     * @param ResolverInterface $resolver Resolver instance.
     * @param int $priority Higher runs first.
     * @return void
     */
    public function registerResolver(ResolverInterface $resolver, int $priority = 0): void
    {
        $this->getResolverManager()->register($resolver, $priority);
        $this->markDirty();
    }

    /**
     * Resolve all values within the configuration array.
     *
     * @param array<string, mixed> $data Configuration data to resolve.
     * @return void
     */
    public function resolveAll(array &$data): void
    {
        if ($this->isResolved()) {
            return;
        }

        $this->getResolverManager()->resolve($data);
        $this->markResolved();
    }

    /**
     * Resolve a single value in the context of the resolver pipeline.
     *
     * @param string $path Dot-separated path for context.
     * @param mixed $value Value to resolve.
     * @return mixed
     */
    public function resolveValue(string $path, mixed $value): mixed
    {
        return $this->getResolverManager()->resolveValue($path, $value);
    }

    /**
     * Ensure values are resolved, resolving when needed.
     *
     * @param array<string, mixed> $data Configuration data to resolve.
     * @return void
     */
    public function ensureResolved(array &$data): void
    {
        if (!$this->isResolved()) {
            $this->resolveAll($data);
        }
    }

    /**
     * Mark configuration state as dirty after changes.
     *
     * @return void
     */
    public function markDirty(): void
    {
        $this->setResolvedFlag(false);
    }

    /**
     * @return bool
     */
    public function isResolved(): bool
    {
        return $this->getResolvedFlag();
    }

    /**
     * @return ConfigInterface
     */
    private function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    /**
     * @param ConfigInterface $config
     * @return void
     */
    private function setConfig(ConfigInterface $config): void
    {
        $this->config = $config;
    }

    /**
     * @return ResolverManager
     */
    private function getResolverManager(): ResolverManager
    {
        return $this->resolverManager;
    }

    /**
     * @param ResolverManager $resolverManager
     * @return void
     */
    private function setResolverManager(ResolverManager $resolverManager): void
    {
        $this->resolverManager = $resolverManager;
    }

    /**
     * @return bool
     */
    private function getResolvedFlag(): bool
    {
        return $this->resolved;
    }

    /**
     * @param bool $resolved
     * @return void
     */
    private function setResolvedFlag(bool $resolved): void
    {
        $this->resolved = $resolved;
    }

    /**
     * @return void
     */
    private function markResolved(): void
    {
        $this->setResolvedFlag(true);
    }
}
