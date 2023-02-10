<?php

namespace Spatie\LaravelSettings\Tests;

use function PHPUnit\Framework\assertEqualsCanonicalizing;
use Spatie\LaravelSettings\Support\SettingsStructureScout;
use Spatie\LaravelSettings\Tests\TestClasses\DummyEncryptedSettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettingsWithCast;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettingsWithImportedType;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettingsWithRepository;

use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;

it('can get all classes that are settings', function () {
    config()->set('settings.auto_discover_settings', [__DIR__.'/TestClasses']);

    assertEqualsCanonicalizing([
        DummySimpleSettings::class,
        DummySettings::class,
        DummyEncryptedSettings::class,
        DummySettingsWithImportedType::class,
        DummySettingsWithCast::class,
        DummySettingsWithRepository::class,
    ], SettingsStructureScout::create()->clear()->get());
});
