<?php

use Carbon\CarbonInterface;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\Settings;

it('can retrieve properties typed with a date interface', function () {
    $settingsClass = new class extends Settings {
        public DateTimeInterface $launched_at;

        public CarbonInterface $deadline;

        public static function group(): string
        {
            return 'test_date_interfaces';
        }
    };

    resolve(SettingsMigrator::class)->inGroup('test_date_interfaces', function (SettingsBlueprint $blueprint): void {
        $blueprint->add('launched_at', '2026-11-11T10:00:00+00:00');
        $blueprint->add('deadline', '2026-12-01T09:30:00+01:00');
    });

    $settings = resolve($settingsClass::class);

    expect($settings->launched_at)->toBeInstanceOf(DateTimeInterface::class)
        ->and($settings->launched_at->format(DATE_ATOM))->toBe('2026-11-11T10:00:00+00:00')
        ->and($settings->deadline)->toBeInstanceOf(CarbonInterface::class)
        ->and($settings->deadline->format(DATE_ATOM))->toBe('2026-12-01T09:30:00+01:00');
});
