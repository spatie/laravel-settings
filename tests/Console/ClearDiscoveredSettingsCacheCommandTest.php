<?php

namespace Spatie\LaravelSettings\Tests\Console;

use function Orchestra\Testbench\artisan;
use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;

use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;

beforeEach(function () {
    $this->app['config']->set('settings.settings', [
        DummySettings::class,
        DummySimpleSettings::class,
    ]);

    $this->settingsContainer = app(SettingsContainer::class);
});

it('can clear the registered settings', function () {
    expect(artisan($this, 'settings:discover'))->toBe(0);

    assertFileExists(config('settings.discovered_settings_cache_path').'/settings.php');

    expect(artisan($this, 'settings:clear-discovered'))->toBe(0);

    assertFileDoesNotExist(config('settings.discovered_settings_cache_path').'/settings.php');
});
