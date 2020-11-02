<?php

namespace Spatie\LaravelSettings;

use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Spatie\LaravelSettings\Support\Composer;
use Spatie\LaravelSettings\Support\DiscoverSettings;

class SettingsContainer
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function registerBindings(): void
    {
        $this->getSettingClasses()->each(
            fn (string $settingClass) => $this->app->singleton(
                $settingClass,
                fn () => SettingsMapper::create($settingClass)->load()
            )
        );
    }

    public function getSettingClasses(): Collection
    {
        /** @var \Spatie\LaravelSettings\Settings[] $settings */
        $settings = array_merge(
            $this->discoverSettings(),
            config('settings.settings')
        );

        return collect($settings)->unique();
    }

    protected function discoverSettings(): array
    {
        $cachedDiscoveredSettings = config('settings.cache_path') . '/settings.php';

        if (file_exists($cachedDiscoveredSettings)) {
            return require $cachedDiscoveredSettings;
        }

        return (new DiscoverSettings())
            ->within(config('settings.auto_discover_settings'))
            ->useBasePath(base_path())
            ->ignoringFiles(Composer::getAutoloadedFiles(base_path('composer.json')))
            ->discover();
    }
}
