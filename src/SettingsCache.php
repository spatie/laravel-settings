<?php

namespace Spatie\LaravelSettings;

use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelSettings\Exceptions\CouldNotUnserializeSettings;
use Spatie\LaravelSettings\Exceptions\SettingsCacheDisabled;

class SettingsCache
{
    public function __construct(
        private bool $enabled,
        private ?string $store,
        private ?string $prefix,
        private DateTimeInterface|DateInterval|int|null $ttl = null,
        private bool $memo = false,
    ) {}

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isMemoEnabled(): bool
    {
        return $this->memo;
    }

    public function get(string $settingsClass): ?Settings
    {
        if ($this->enabled === false) {
            throw SettingsCacheDisabled::create();
        }

        $serialized = $this->cacheRepository()->get($this->resolveCacheKey($settingsClass));

        if ($serialized === null) {
            return null;
        }

        $settings = unserialize($serialized);

        if (! $settings instanceof Settings) {
            throw new CouldNotUnserializeSettings();
        }

        $settings->settingsConfig()->markLoadedFromCache(true);

        return $settings;
    }

    public function put(Settings $settings): void
    {
        if ($this->enabled === false) {
            return;
        }

        $settings->settingsConfig()->markLoadedFromCache(true);

        $serialized = serialize($settings);

        $this->cacheRepository()->put(
            $this->resolveCacheKey(get_class($settings)),
            $serialized,
            $this->ttl
        );
    }

    public function clear(): void
    {
        app(SettingsContainer::class)
            ->getSettingClasses()
            ->map(fn (string $class) => $this->resolveCacheKey($class))
            ->pipe(fn (Collection $keys) => $this->cacheRepository()->deleteMultiple($keys));
    }

    protected function cacheRepository(): Repository
    {
        if ($this->memo && method_exists(Cache::getFacadeRoot(), 'memo')) {
            return Cache::memo($this->store);
        }

        return Cache::store($this->store);
    }

    private function resolveCacheKey(string $settingsClass): string
    {
        $prefix = $this->prefix ? "{$this->prefix}." : '';

        return "{$prefix}settings.{$settingsClass::cacheKey()}";
    }
}
