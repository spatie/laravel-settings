<?php

namespace Spatie\LaravelSettings;

use Cache;
use Illuminate\Support\Collection;
use Spatie\LaravelSettings\Exceptions\CouldNotUnserializeSettings;
use Spatie\LaravelSettings\Exceptions\SettingsCacheDisabled;

class SettingsCache
{
    private bool $enabled;

    private ?string $store;

    private ?string $prefix;

    public function __construct(
        bool $enabled,
        ?string $store,
        ?string $prefix
    ) {
        $this->enabled = $enabled;
        $this->store = $store;
        $this->prefix = $prefix;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function has(string $settingsClass): bool
    {
        if ($this->enabled === false) {
            return false;
        }

        return Cache::store($this->store)->has($this->resolveCacheKey($settingsClass));
    }

    public function get(string $settingsClass): Settings
    {
        if ($this->enabled === false) {
            throw SettingsCacheDisabled::create();
        }

        $serialized = Cache::store($this->store)->get($this->resolveCacheKey($settingsClass));

        $settings = unserialize($serialized);

        if (! $settings instanceof Settings) {
            throw new CouldNotUnserializeSettings();
        }

        return $settings;
    }

    public function put(Settings $settings): void
    {
        if ($this->enabled === false) {
            return;
        }

        $serialized = serialize($settings);

        Cache::store($this->store)->put(
            $this->resolveCacheKey(get_class($settings)),
            $serialized
        );
    }

    public function clear(): void
    {
        app(SettingsContainer::class)
            ->getSettingClasses()
            ->map(fn (string $class) => $this->resolveCacheKey($class))
            ->pipe(fn (Collection $keys) => Cache::store($this->store)->deleteMultiple($keys));
    }

    private function resolveCacheKey(string $settingsClass): string
    {
        $prefix = $this->prefix ? "{$this->prefix}." : '';

        return "{$prefix}settings.{$settingsClass}";
    }
}
