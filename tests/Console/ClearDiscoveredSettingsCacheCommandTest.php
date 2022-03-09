<?php

namespace Spatie\LaravelSettings\Tests\Console;

use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;

use function Orchestra\Testbench\artisan;
use function PHPUnit\Framework\{assertFileExists, assertFileDoesNotExist};

beforeEach(function () {
    $this->app['config']->set('settings.settings', [
        DummySettings::class,
        DummySimpleSettings::class,
    ]);

    $this->settingsContainer = app(SettingsContainer::class);
});

it('can clear the registered settings', function () {
    artisan($this, 'settings:discover')->assertExitCode(0);

    assertFileExists(config('settings.discovered_settings_cache_path').'/settings.php');

    artisan($this, 'settings:clear-discovered')->assertExitCode(0);

    assertFileDoesNotExist(config('settings.discovered_settings_cache_path').'/settings.php');
});
