<?php

namespace Spatie\LaravelSettings\Tests\Console;

use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;
use Spatie\Snapshots\MatchesSnapshots;

use function Orchestra\Testbench\artisan;
use function Spatie\Snapshots\assertMatchesSnapshot;

uses(MatchesSnapshots::class);

beforeEach(function () {
    $this->app['config']->set('settings.settings', [
        DummySettings::class,
        DummySimpleSettings::class,
    ]);

    $this->container = app(SettingsContainer::class);
});

it('can cache the registered sessions', function () {
    artisan($this, 'settings:discover')->assertExitCode(0);

    assertMatchesSnapshot(file_get_contents(config('settings.discovered_settings_cache_path').'/settings.php'));
});
