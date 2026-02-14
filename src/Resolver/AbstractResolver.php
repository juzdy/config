<?php

namespace Juzdy\Config\Resolver;

use Juzdy\Config\Config;

/**
 * Base resolver that provides access to the Config container.
 */
abstract class AbstractResolver implements ResolverInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Config $config Config owner.
     */
    public function __construct(Config $config)
    {
        $this->setConfig($config);
    }

    /**
     * Get the Config instance.
      *
      * @return Config
     */
    protected function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Default pass-through implementation.
      *
      * @param string $path Dot-separated path for context.
      * @param mixed $value Value to resolve (by reference).
      * @param ResolverInterface $next Next resolver in the chain.
      * @return mixed
     */
    public function resolve(string $path, mixed &$value, ResolverInterface $next): mixed
    {
        return $next->resolve($path, $value, $next);
    }

    /**
     * @param Config $config
     * @return void
     */
    private function setConfig(Config $config): void
    {
        $this->config = $config;
    }
}
