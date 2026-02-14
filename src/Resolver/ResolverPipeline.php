<?php

namespace Juzdy\Config\Resolver;

/**
 * Runs resolvers in sequence for each value.
 */
final class ResolverPipeline implements ResolverInterface
{
    /**
     * @var ResolverInterface[]
     */
    private array $resolvers;

    /**
     * @param ResolverInterface[] $resolvers
     */
    public function __construct(array $resolvers)
    {
        $this->setResolvers($resolvers);
    }

    /**
     * Resolve a value through the resolver chain.
      *
      * @param string $path Dot-separated path for context.
      * @param mixed $value Value to resolve (by reference).
      * @param ResolverInterface $next Next resolver in the chain.
      * @return mixed
     */
    public function resolve(string $path, mixed &$value, ResolverInterface $next): mixed
    {
        if ($this->getResolvers() === []) {
            return $value;
        }

        $dispatcher = new ResolverDispatcher($this->getResolvers(), 0);

        return $dispatcher->resolve($path, $value, $dispatcher);
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
}
