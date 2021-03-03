<?php

namespace Spatie\LaravelSettings\Tests\Console;

use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Tests\TestCase;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;
use Spatie\Snapshots\MatchesSnapshots;

class CacheDiscoveredSettingsCommandTest extends TestCase
{
    use MatchesSnapshots;

    private SettingsContainer $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('settings.settings', [
            DummySettings::class,
            DummySimpleSettings::class,
        ]);

        $this->container = app(SettingsContainer::class);
    }

    /** @test */
    public function it_can_cache_the_registered_sessions()
    {
        $this->artisan('settings:discover')->assertExitCode(0);

        $this->assertMatchesSnapshot(file_get_contents(config('settings.discovered_settings_cache_path').'/settings.php'));
    }
}
