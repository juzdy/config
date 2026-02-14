<?php

namespace Juzdy\Config\Resolver;

use Juzdy\Config\Resolver\Exception\EnvResolveException;

/**
 * Resolves @env(VAR) placeholders using environment variables.
 */
class EnvResolver extends AbstractResolver
{
    /**
     * @throws EnvResolveException
        *
        * @param string $path Dot-separated path for context.
        * @param mixed $value Value to resolve (by reference).
        * @param ResolverInterface $next Next resolver in the chain.
        * @return mixed
     */
    public function resolve(string $path, mixed &$value, ResolverInterface $next): mixed
    {
        if (!is_string($value)) {
            return $next->resolve($path, $value, $next);
        }

        if (!preg_match_all('/@env\(([^)]+)\)/', $value, $matches)) {
            return $next->resolve($path, $value, $next);
        }

        foreach ($matches[1] as $index => $envKey) {
            $envValue = getenv($envKey);
            if ($envValue === false) {
                throw new EnvResolveException(
                    sprintf('Missing environment variable "%s" while resolving "%s".', $envKey, $path)
                );
            }

            $value = str_replace($matches[0][$index], (string) $envValue, $value);
        }

        return $next->resolve($path, $value, $next);
    }
}
