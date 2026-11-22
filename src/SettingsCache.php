<?php

namespace Spatie\LaravelSettings;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelSettings\Exceptions\CouldNotUnserializeSettings;
use Spatie\LaravelSettings\Exceptions\SettingsCacheDisabled;

class SettingsCache
{
    private const CACHE_FORMAT_VERSION = 1;

    private static ?Closure $prefixResolver = null;

    public function __construct(
        private bool $enabled,
        private ?string $store,
        private ?string $prefix,
        private DateTimeInterface|DateInterval|int|null $ttl = null,
        private bool $memo = false,
    ) {
    }

    /**
     * Register a callback that returns a dynamic prefix appended after the
     * static config prefix. Call with null to remove the resolver.
     */
    public static function resolvePrefixUsing(?callable $resolver): void
    {
        self::$prefixResolver = $resolver !== null
            ? Closure::fromCallable($resolver)
            : null;
    }

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

        if ($settings instanceof Settings) {
            return null;
        }

        if (
            ! is_array($settings)
            || ($settings['__settings_cache_format'] ?? null) !== self::CACHE_FORMAT_VERSION
            || ! is_array($settings['values'] ?? null)
        ) {
            throw new CouldNotUnserializeSettings();
        }

        $values = app(SettingsMapper::class)->loadCachedProperties($settingsClass, $settings['values']);

        if ($values === null) {
            return null;
        }

        return $settingsClass::fromCache($values->all());
    }

    public function put(Settings $settings): void
    {
        if ($this->enabled === false) {
            return;
        }

        $settings->settingsConfig()->markLoadedFromCache(true);

        $settingsConfig = $settings->settingsConfig();
        $values = array_merge(
            $settingsConfig->getRepository()->getPropertiesInGroup($settingsConfig->getGroup()),
            app(SettingsMapper::class)->serializeProperties(
                get_class($settings),
                $settings->toCollection()
            )
        );

        $serialized = serialize([
            '__settings_cache_format' => self::CACHE_FORMAT_VERSION,
            'values' => $values,
        ]);

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

        if (self::$prefixResolver !== null) {
            $resolvedPrefix = (string)(self::$prefixResolver)();
            if ($resolvedPrefix !== '') {
                $prefix .= "{$resolvedPrefix}.";
            }
        }

        return "{$prefix}settings.v".self::CACHE_FORMAT_VERSION.".{$settingsClass::cacheKey()}";
    }
}
