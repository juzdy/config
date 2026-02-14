<?php

namespace Juzdy\Config\Resolver;

/**
 * Defines the resolver contract for Config values.
 */
interface ResolverInterface
{
    /**
     * Resolve a value for the given path and pass to the next resolver.
     *
     * @param string $path Dot-separated path for context.
     * @param mixed $value Value to resolve (by reference).
     * @param ResolverInterface $next Next resolver in the chain.
     * @return mixed
     */
    public function resolve(string $path, mixed &$value, ResolverInterface $next): mixed;
}
