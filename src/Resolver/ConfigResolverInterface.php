<?php

namespace Juzdy\Config\Resolver;

use Juzdy\Config\ConfigInterface;

/**
 * Resolves configuration values and manages resolution state.
 */
interface ConfigResolverInterface
{

    /**
     * Set the configuration instance for resolvers to access.
     *
     * @param ConfigInterface $config Config instance.
     * @return static
     */
    public function withConfig(ConfigInterface $config): static;

    /**
     * Register built-in resolvers.
     *
     * @return void
     */
    public function registerDefaultResolvers(): void;

    /**
     * Register a resolver with priority (higher runs first).
     *
     * @param ResolverInterface $resolver Resolver instance.
     * @param int $priority Higher runs first.
     * @return void
     */
    public function registerResolver(ResolverInterface $resolver, int $priority = 0): void;

    /**
     * Resolve all values within the configuration array.
     *
     * @param array<string, mixed> $data Configuration data to resolve.
     * @return void
     */
    public function resolveAll(array &$data): void;

    /**
     * Resolve a single value in the context of the resolver pipeline.
     *
     * @param string $path Dot-separated path for context.
     * @param mixed $value Value to resolve.
     * @return mixed
     */
    public function resolveValue(string $path, mixed $value): mixed;

    /**
     * Ensure values are resolved, resolving when needed.
     *
     * @param array<string, mixed> $data Configuration data to resolve.
     * @return void
     */
    public function ensureResolved(array &$data): void;

    /**
     * Mark configuration state as dirty after changes.
     *
     * @return void
     */
    public function markDirty(): void;

    /**
     * @return bool
     */
    public function isResolved(): bool;
}
