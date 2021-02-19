<?php

namespace Spatie\LaravelSettings;

use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelSettings\Exceptions\CouldNotUnserializeSettings;
use Spatie\LaravelSettings\Support\Composer;
use Spatie\LaravelSettings\Support\DiscoverSettings;

class SettingsContainer
{
    protected Application $app;

    protected static ?Collection $settingsClasses = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function registerBindings(): void
    {
        $cache = $this->app->make(SettingsCache::class);

        $this->getSettingClasses()->each(function (string $settingClass) use ($cache) {
            $settings = $settingClass;

            if ($cache->has($settingClass)) {
                try {
                    $settings = $cache->get($settingClass);
                } catch (CouldNotUnserializeSettings $exception) {
                    Log::error("Could not unserialize settings class: `{$settingClass}` from cache");
                }
            }

            $this->app->singleton($settings);
        });
    }

    public function getSettingClasses(): Collection
    {
        if (self::$settingsClasses !== null) {
            return self::$settingsClasses;
        }

        $cachedDiscoveredSettings = config('settings.cache_path') . '/settings.php';

        if (file_exists($cachedDiscoveredSettings)) {
            $classes = require $cachedDiscoveredSettings;

            return self::$settingsClasses = collect($classes);
        }

        /** @var \Spatie\LaravelSettings\Settings[] $settings */
        $settings = array_merge(
            $this->discoverSettings(),
            config('settings.settings')
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
            ->within(config('settings.auto_discover_settings'))
            ->useBasePath(base_path())
            ->ignoringFiles(Composer::getAutoloadedFiles(base_path('composer.json')))
            ->discover();
    }
}
