<?php

namespace Juzdy\Config\Resolver;

use Juzdy\Config\Resolver\Exception\ReferenceResolveException;

/**
 * Resolves @(path.to.value) placeholders using Config values.
 */
class ReferenceResolver extends AbstractResolver
{
    /**
     * @throws ReferenceResolveException
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

        if (!preg_match_all('/@\(([^)]+)\)/', $value, $matches)) {
            return $next->resolve($path, $value, $next);
        }

        foreach ($matches[1] as $index => $refKey) {
            if (!$this->getConfig()->hasRaw($refKey)) {
                throw new ReferenceResolveException(
                    sprintf('Missing Config path "%s" while resolving "%s".', $refKey, $path)
                );
            }

            $refValue = $this->getConfig()->getRaw($refKey);
            $resolved = $this->getConfig()->resolveValue($refKey, $refValue);

            if (is_array($resolved)) {
                throw new ReferenceResolveException(
                    sprintf('Config path "%s" resolved to array while resolving "%s".', $refKey, $path)
                );
            }

            $value = str_replace($matches[0][$index], (string) $resolved, $value);
        }

        return $next->resolve($path, $value, $next);
    }
}
