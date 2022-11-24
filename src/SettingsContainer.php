<?php

namespace Spatie\LaravelSettings;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelSettings\Exceptions\CouldNotUnserializeSettings;
use Spatie\LaravelSettings\Support\Composer;
use Spatie\LaravelSettings\Support\DiscoverSettings;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;

class SettingsContainer
{
    protected Container $container;

    protected static ?Collection $settingsClasses = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function registerBindings(): void
    {
        $cacheFactory = $this->container->make(SettingsCacheFactory::class);

        $this->getSettingClasses()->each(function (string $settingClass) use ($cacheFactory) {
            $this->container->scoped($settingClass, function () use ($cacheFactory, $settingClass) {
                $cache = $cacheFactory->build($settingClass::repository());

                if ($cache->isEnabled() && $cache->has($settingClass)) {
                    try {
                        return $cache->get($settingClass);
                    } catch (CouldNotUnserializeSettings $exception) {
                        Log::error("Could not unserialize settings class: `{$settingClass}` from cache");
                    }
                }

                return new $settingClass();
            });
        });
    }

    public function getSettingClasses(): Collection
    {
        if (self::$settingsClasses !== null) {
            return self::$settingsClasses;
        }

        $cachedDiscoveredSettings = config('settings.discovered_settings_cache_path') . '/settings.php';

        if (file_exists($cachedDiscoveredSettings)) {
            $classes = require $cachedDiscoveredSettings;

            return self::$settingsClasses = collect($classes);
        }

        /** @var \Spatie\LaravelSettings\Settings[] $settings */
        $settings = array_merge(
            $this->discoverSettings(),
            config('settings.settings', [])
        );

        return self::$settingsClasses = collect($settings)->unique();
    }

    public function clearCache(): self
    {
        self::$settingsClasses = null;

        return $this;
    }

    protected function discoverSettings(): array
    {
        return (new DiscoverSettings())
            ->within(config('settings.auto_discover_settings', []))
            ->useBasePath(base_path())
            ->ignoringFiles(Composer::getAutoloadedFiles(base_path('composer.json')))
            ->discover();
    }
}
