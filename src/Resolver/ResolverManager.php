<?php

namespace Juzdy\Config\Resolver;

/**
 * Manages resolver registration and applies them across Config values.
 */
class ResolverManager
{
    /**
     * @var array<int, ResolverInterface[]>
     */
    private array $resolvers = [];
    /**
     * @var ResolverInterface|null
     */
    private ?ResolverInterface $pipeline = null;

    /**
     * Register a resolver with priority (higher runs first).
     *
     * @param ResolverInterface $resolver Resolver instance.
     * @param int $priority Higher runs first.
     * @return static
     */
    public function register(ResolverInterface $resolver, int $priority = 0): static
    {
        $resolvers = $this->getResolvers();
        $resolvers[$priority][] = $resolver;
        krsort($resolvers);
        $this->setResolvers($resolvers);
        $this->clearPipeline();

        return $this;
    }

    /**
     * Resolve all values within the Config array.
     *
     * @param array<string, mixed> $data Config data to resolve.
     * @return array<string, mixed>
     */
    public function resolve(array &$data): array
    {
        if (!$this->hasResolvers()) {
            return $data;
        }

        $pipeline = $this->getPipeline();
        $this->resolveRecursive($data, $pipeline, '');

        return $data;
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
        if (!$this->hasResolvers()) {
            return $value;
        }

        $pipeline = $this->getPipeline();
        $pipeline->resolve($path, $value, $pipeline);

        if (is_array($value)) {
            $this->resolveRecursive($value, $pipeline, $path);
        }

        return $value;
    }

    /**
     * @return ResolverInterface[]
     */
    private function getResolverList(): array
    {
        $list = [];
        foreach ($this->getResolvers() as $bucket) {
            $list = array_merge($list, $bucket);
        }

        return array_values($list);
    }

    /**
     * Build or return cached resolver pipeline.
     *
     * @return ResolverInterface
     */
    private function getPipeline(): ResolverInterface
    {
        if ($this->hasPipeline()) {
            return $this->pipeline;
        }

        $this->setPipeline(new ResolverPipeline($this->getResolverList()));

        return $this->pipeline;
    }

    /**
     * Walk the Config tree and resolve values in place.
     *
     * @param array<string, mixed> $data Config data to resolve.
     * @param ResolverInterface $pipeline Resolver pipeline.
     * @param string $basePath Current dot path.
     * @return void
     */
    private function resolveRecursive(array &$data, ResolverInterface $pipeline, string $basePath): void
    {
        foreach ($data as $key => &$value) {
            $path = $this->joinPath($basePath, (string) $key);

            if (is_array($value)) {
                $pipeline->resolve($path, $value, $pipeline);
                $this->resolveRecursive($value, $pipeline, $path);
                continue;
            }

            if (is_string($value)) {
                $pipeline->resolve($path, $value, $pipeline);
            }
        }
    }

    /**
     * Join a path segment to an existing dot path.
     *
     * @param string $basePath Base dot path.
     * @param string $key Path segment to append.
     * @return string
     */
    private function joinPath(string $basePath, string $key): string
    {
        if ($basePath === '') {
            return $key;
        }

        if ($key === '') {
            return $basePath;
        }

        return $basePath . '.' . $key;
    }

    /**
     * @return array<int, ResolverInterface[]>
     */
    private function getResolvers(): array
    {
        return $this->resolvers;
    }

    /**
     * @return bool
     */
    private function hasResolvers(): bool
    {
        return $this->getResolvers() !== [];
    }

    /**
     * @param array<int, ResolverInterface[]> $resolvers
     * @return void
     */
    private function setResolvers(array $resolvers): void
    {
        $this->resolvers = $resolvers;
    }

    /**
     * @return bool
     */
    private function hasPipeline(): bool
    {
        return $this->pipeline instanceof ResolverInterface;
    }

    /**
     * @param ResolverInterface|null $pipeline
     * @return void
     */
    private function setPipeline(?ResolverInterface $pipeline): void
    {
        $this->pipeline = $pipeline;
    }

    /**
     * @return void
     */
    private function clearPipeline(): void
    {
        $this->setPipeline(null);
    }
}
