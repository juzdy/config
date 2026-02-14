<?php

namespace Juzdy\Config\Resolver;

/**
 * Dispatches to the next resolver in the chain.
 */
final class ResolverDispatcher implements ResolverInterface
{
    /**
     * @var ResolverInterface[]
     */
    private array $resolvers;
    /**
     * @var int
     */
    private int $index;

    /**
     * @param ResolverInterface[] $resolvers
     * @param int $index Resolver index to execute.
     */
    public function __construct(array $resolvers, int $index)
    {
        $this->setResolvers($resolvers);
        $this->setIndex($index);
    }

    /**
     * Resolve a value by invoking the current resolver.
      *
      * @param string $path Dot-separated path for context.
      * @param mixed $value Value to resolve (by reference).
      * @param ResolverInterface $next Next resolver in the chain.
      * @return mixed
     */
    public function resolve(string $path, mixed &$value, ResolverInterface $next): mixed
    {
        $resolvers = $this->getResolvers();
        $index = $this->getIndex();
        if (!isset($resolvers[$index])) {
            return $value;
        }

        $current = $resolvers[$index];
        $nextDispatcher = new self($resolvers, $index + 1);

        return $current->resolve($path, $value, $nextDispatcher);
    }

    /**
     * @return ResolverInterface[]
     */
    private function getResolvers(): array
    {
        return $this->resolvers;
    }

    /**
     * @param ResolverInterface[] $resolvers
     * @return void
     */
    private function setResolvers(array $resolvers): void
    {
        $this->resolvers = $resolvers;
    }

    /**
     * @return int
     */
    private function getIndex(): int
    {
        return $this->index;
    }

    /**
     * @param int $index
     * @return void
     */
    private function setIndex(int $index): void
    {
        $this->index = $index;
    }
}
