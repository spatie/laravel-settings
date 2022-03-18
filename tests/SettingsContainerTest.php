<?php

namespace Spatie\LaravelSettings\Tests;

use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Tests\Fakes\FakeAction;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;

beforeEach(function () {
    $this->setRegisteredSettings([
        DummySimpleSettings::class,
    ]);

    $this->migrator = resolve(SettingsMigrator::class);

    $this->migrator->inGroup('dummy_simple', function (SettingsBlueprint $blueprint): void {
        $blueprint->add('name', 'Louis Armstrong');
        $blueprint->add('description', 'Hello Dolly');
    });

    resolve(SettingsContainer::class)->registerBindings();
});

it('will not fetch data from the repository twice', function () {
    DB::connection()->enableQueryLog();

    $settingsA = resolve(DummySimpleSettings::class);
    $settingsB = resolve(DummySimpleSettings::class);

    $settingsA->name;
    $settingsB->name;

    $log = DB::connection()->getQueryLog();

    expect($log)->toHaveCount(1);
});

it('wont fetch data from the repository when injected only', function () {
    DB::connection()->enableQueryLog();

    resolve(DummySimpleSettings::class);

    $log = DB::connection()->getQueryLog();

    expect($log)->toHaveCount(0);
});

it('settings are shared between instances', function () {
    $settingsA = resolve(DummySimpleSettings::class);
    $settingsB = resolve(DummySimpleSettings::class);

    $settingsA->name = 'Nina Simone';

    expect($settingsB)
        ->name
        ->toEqual('Nina Simone');

    $settingsB->lock('name');

    $settingsB->save();

    expect($settingsA)
        ->name
        ->toEqual('Louis Armstrong');
});

it('can refresh settings', function () {
    $settings = resolve(DummySimpleSettings::class);

    $fakeAction = resolve(FakeAction::class);

    $fakeAction->updateSettings();

    expect($settings)->name->toEqual('updated');
});
