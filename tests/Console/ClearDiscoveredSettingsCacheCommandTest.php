<?php

namespace Spatie\LaravelSettings\Tests\Console;

use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Tests\TestCase;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettingsEloquent;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;

class ClearDiscoveredSettingsCacheCommandTest extends TestCase
{
    private SettingsContainer $settingsContainer;

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('settings.settings', [
            DummySettings::class,
            DummySimpleSettings::class,
            DummySettingsEloquent::class

        ]);

        $this->settingsContainer = app(SettingsContainer::class);
    }

    /** @test */
    public function it_can_clear_the_registered_settings()
    {
        $this->artisan('settings:discover')->assertExitCode(0);

        $this->assertFileExists(config('settings.discovered_settings_cache_path').'/settings.php');

        $this->artisan('settings:clear-discovered')->assertExitCode(0);

        $this->assertFileDoesNotExist(config('settings.discovered_settings_cache_path').'/settings.php');
    }
}
