<?php

namespace Spatie\LaravelSettings\Tests\Console;

use Spatie\LaravelSettings\Tests\TestClasses\DummyEncryptedSettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettingsWithCast;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettingsWithImportedType;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettingsWithRepository;
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
        __DIR__.'/../TestClasses',
    ]);

    $this->container = app(SettingsContainer::class);
});

it('can cache the registered sessions', function () {
    artisan($this, 'settings:discover');

    $settingsClasses = require config('settings.discovered_settings_cache_path').'/settings.php';

    expect($settingsClasses)->toEqual([
        DummySettingsWithRepository::class,
        DummyEncryptedSettings::class,
        DummySimpleSettings::class,
        DummySettings::class,
        DummySettingsWithImportedType::class,
        DummySettingsWithCast::class
    ]);
});
