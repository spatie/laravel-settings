<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsCasts\ArrayDataCast;
use Spatie\LaravelSettings\Tests\TestClasses\DummyData;

it('can save and retrieve an array of data objects', function () {
    $settingsClass = new class extends Settings {
        /** @var DummyData[] */
        public array $users;

        public static function group(): string
        {
            return 'test_array_data';
        }

        public static function casts(): array
        {
            return [
                'users' => ArrayDataCast::class . ':' . DummyData::class,
            ];
        }
    };

    resolve(SettingsMigrator::class)->inGroup('test_array_data', function (SettingsBlueprint $blueprint): void {
        $blueprint->add('users', [
            ['name' => 'Freek'],
            ['name' => 'Ruben'],
        ]);
    });

    $settings = resolve($settingsClass::class);

    expect($settings->users)
        ->toBeArray()
        ->toHaveCount(2);

    expect($settings->users[0])
        ->toBeInstanceOf(DummyData::class)
        ->name->toBe('Freek');

    expect($settings->users[1])
        ->toBeInstanceOf(DummyData::class)
        ->name->toBe('Ruben');

    $settings->users = [
        DummyData::from(['name' => 'Brent']),
        DummyData::from(['name' => 'Rias']),
    ];

    $settings->save();

    $this->assertDatabaseHasSetting('test_array_data.users', [
        ['name' => 'Brent'],
        ['name' => 'Rias'],
    ]);
});

it('can save an array of data objects with validation', function () {
    $settingsClass = new class extends Settings {
        /** @var DummyData[] */
        public array $users;

        public static function group(): string
        {
            return 'test_array_data_validated';
        }

        public static function casts(): array
        {
            return [
                'users' => ArrayDataCast::class . ':' . DummyData::class . ',true',
            ];
        }
    };

    resolve(SettingsMigrator::class)->inGroup('test_array_data_validated', function (SettingsBlueprint $blueprint): void {
        $blueprint->add('users', [
            ['name' => 'Freek'],
        ]);
    });

    $settings = resolve($settingsClass::class);

    $settings->users = [
        DummyData::from(['name' => 'Brent']),
        DummyData::from(['name' => 'Rias']),
    ];

    $settings->save();

    $this->assertDatabaseHasSetting('test_array_data_validated.users', [
        ['name' => 'Brent'],
        ['name' => 'Rias'],
    ]);
});
