<?php

namespace Spatie\LaravelSettings\Tests\Console;

use function Orchestra\Testbench\artisan;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;

use function Spatie\Snapshots\assertMatchesSnapshot;
use Spatie\Snapshots\MatchesSnapshots;

uses(MatchesSnapshots::class);

beforeEach(function () {
    $this->app['config']->set('settings.settings', [
        DummySettings::class,
        DummySimpleSettings::class,
    ]);

    $this->app['config']->set('settings.auto_discover_settings', [
        __DIR__.'/../TestClasses'
    ]);

    $this->container = app(SettingsContainer::class);
});

it('can cache the registered sessions', function () {
    artisan($this, 'settings:discover');

    assertMatchesSnapshot(file_get_contents(config('settings.discovered_settings_cache_path').'/settings.php'));
});
