<?php

namespace Spatie\LaravelSettings\Support;

use Spatie\LaravelSettings\SettingsCache;

class SettingsCacheFactory
{
    private array $config;

    private SettingsCache $defaultCache;

    private array $repositoryCaches = [];

    public function __construct(array $settingsConfig)
    {
        $this->config = $settingsConfig;

        $this->initializeCaches();
    }

    public function build(?string $repository = null): SettingsCache
    {
        if ($repository === null) {
            return $this->defaultCache;
        }

        if (array_key_exists($repository, $this->repositoryCaches)) {
            return $this->repositoryCaches[$repository];
        }

        return $this->defaultCache;
    }

    /** @return array<SettingsCache> */
    public function all(): array
    {
        return array_merge(
            ['default' => $this->defaultCache],
            $this->repositoryCaches
        );
    }

    protected function initializeCaches(): void
    {
        $this->defaultCache = $this->initializeCache($this->config['cache']);

        foreach ($this->config['repositories'] as $name => $repositoryConfig) {
            if (array_key_exists('cache', $repositoryConfig)) {
                $this->repositoryCaches[$name] = $this->initializeCache($repositoryConfig['cache']);
            }
        }
    }

    protected function initializeCache(array $config): SettingsCache
    {
        return new SettingsCache(
            $config['enabled'] ?? false,
            $config['store'] ?? null,
            $config['prefix'] ?? null,
            $config['ttl'] ?? null,
        );
    }
}
