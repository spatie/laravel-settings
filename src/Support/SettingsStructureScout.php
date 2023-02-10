<?php

namespace Spatie\LaravelSettings\Support;

use Spatie\LaravelSettings\Settings;
use Spatie\StructureDiscoverer\Cache\DiscoverCacheDriver;
use Spatie\StructureDiscoverer\Cache\FileDiscoverCacheDriver;
use Spatie\StructureDiscoverer\Cache\NullDiscoverCacheDriver;
use Spatie\StructureDiscoverer\Discover;
use Spatie\StructureDiscoverer\StructureScout;

class SettingsStructureScout extends StructureScout
{
    public function identifier(): string
    {
        return "laravel-settings";
    }

    protected function definition(): Discover
    {
        return Discover::in(...config('settings.auto_discover_settings', []))
            ->classes()
            ->extending(Settings::class);
    }

    public function cacheDriver(): DiscoverCacheDriver
    {
        return new FileDiscoverCacheDriver(
            config('settings.discovered_settings_cache_path'),
            serialize: false,
            filename: 'settings.php'
        );
    }
}
