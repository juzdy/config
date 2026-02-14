<?php

namespace Juzdy\Config\Resolver;

use Juzdy\Config\Resolver\Exception\ExtendsResolveException;

/**
 * Applies @extends rules by merging referenced Config arrays.
 */
class ExtendsResolver extends AbstractResolver
{
    /**
     * @throws ExtendsResolveException
        *
        * @param string $path Dot-separated path for context.
        * @param mixed $value Value to resolve (by reference).
        * @param ResolverInterface $next Next resolver in the chain.
        * @return mixed
     */
    public function resolve(string $path, mixed &$value, ResolverInterface $next): mixed
    {
        if (!is_array($value) || !array_key_exists('@extends', $value)) {
            return $next->resolve($path, $value, $next);
        }

        $extensions = $value['@extends'];
        unset($value['@extends']);

        if (is_string($extensions)) {
            $extensions = [$extensions];
        }

        if (!is_array($extensions)) {
            throw new ExtendsResolveException(
                sprintf('Invalid @extends definition at "%s".', $path)
            );
        }

        $merged = [];
        foreach ($extensions as $extension) {
            if (!is_string($extension)) {
                throw new ExtendsResolveException(
                    sprintf('Invalid @extends entry at "%s".', $path)
                );
            }

            $resolvedPath = $this->resolvePath($path, $extension);
            if ($resolvedPath === null) {
                throw new ExtendsResolveException(
                    sprintf('Invalid @extends path at "%s".', $path)
                );
            }

            if (!$this->getConfig()->hasRaw($resolvedPath)) {
                throw new ExtendsResolveException(
                    sprintf(
                        'Missing Config path "%s" (resolved as "%s") while resolving "%s".',
                        $extension,
                        $resolvedPath,
                        $path
                    )
                );
            }

            $refValue = $this->getConfig()->getRaw($resolvedPath);
            $resolvedValue = $this->getConfig()->resolveValue($resolvedPath, $refValue);
            if (!is_array($resolvedValue)) {
                throw new ExtendsResolveException(
                    sprintf('Config path "%s" is not an array while resolving "%s".', $resolvedPath, $path)
                );
            }

            $merged = $this->mergeArrays($merged, $resolvedValue);
        }

        $value = $this->mergeArrays($merged, $value);

        return $next->resolve($path, $value, $next);
    }

    /**
     * Resolve relative @extends paths against the current node path.
      *
      * @param string $basePath Base dot path.
      * @param string $path Relative or absolute path.
      * @return string|null
     */
    private function resolvePath(string $basePath, string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, './') || str_starts_with($path, '../')) {
            $baseParts = $basePath === '' ? [] : explode('.', $basePath);
            while (str_starts_with($path, '../')) {
                array_pop($baseParts);
                $path = substr($path, 3);
            }

            if (str_starts_with($path, './')) {
                $path = substr($path, 2);
            }

            $path = trim($path, '.');
            $relativeParts = $path === '' ? [] : explode('.', $path);
            $parts = array_merge($baseParts, $relativeParts);

            return implode('.', array_filter($parts, 'strlen'));
        }

        return $path;
    }

    /**
     * Merge arrays so numeric keys append and string keys overwrite.
      *
      * @param array<mixed> $base Base array.
      * @param array<mixed> $overlay Overlay array.
      * @return array<mixed>
     */
    private function mergeArrays(array $base, array $overlay): array
    {
        foreach ($overlay as $key => $value) {
            if (is_int($key)) {
                $base[] = $value;
                continue;
            }

            if (isset($base[$key]) && is_array($base[$key]) && is_array($value)) {
                $base[$key] = $this->mergeArrays($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }
}
