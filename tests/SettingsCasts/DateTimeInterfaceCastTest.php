<?php

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon as IlluminateCarbon;
use Illuminate\Support\Facades\Date;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast;

afterEach(function () {
    Date::useDefault();
});

it('can save and retrieve properties typed with a date interface', function (?string $dateClass) {
    if ($dateClass !== null) {
        Date::use($dateClass);
    }

    $settingsClass = new class extends Settings {
        public DateTimeInterface $launched_at;

        public ?CarbonInterface $deadline;

        public ?DateTimeInterface $optional_date;

        public static function group(): string
        {
            return 'test_date_interfaces';
        }
    };

    resolve(SettingsMigrator::class)->inGroup('test_date_interfaces', function (SettingsBlueprint $blueprint): void {
        $blueprint->add('launched_at', '2026-11-11T10:00:00+00:00');
        $blueprint->add('deadline', '2026-12-01T09:30:00+01:00');
        $blueprint->add('optional_date', null);
    });

    $settings = resolve($settingsClass::class);

    expect($settings->launched_at)->toBeInstanceOf($dateClass ?? IlluminateCarbon::class)
        ->and($settings->launched_at->format(DATE_ATOM))->toBe('2026-11-11T10:00:00+00:00')
        ->and($settings->deadline)->toBeInstanceOf($dateClass ?? IlluminateCarbon::class)
        ->and($settings->deadline->format(DATE_ATOM))->toBe('2026-12-01T09:30:00+01:00')
        ->and($settings->optional_date)->toBeNull();

    $settings->launched_at = new DateTimeImmutable('2027-02-03T14:15:16-05:00');
    $settings->deadline = null;
    $settings->save();

    $this->assertDatabaseHasSetting('test_date_interfaces.launched_at', '2027-02-03T14:15:16-05:00');
    $this->assertDatabaseHasSetting('test_date_interfaces.deadline', null);

    $settings->refresh();

    expect($settings->launched_at)->toBeInstanceOf($dateClass ?? IlluminateCarbon::class)
        ->and($settings->launched_at->format(DATE_ATOM))->toBe('2027-02-03T14:15:16-05:00')
        ->and($settings->deadline)->toBeNull();
})->with([
    'default factory' => [null],
    'mutable factory' => [Carbon::class],
    'immutable factory' => [CarbonImmutable::class],
]);

it('preserves concrete date types regardless of the date factory', function (string $dateClass) {
    Date::use(CarbonImmutable::class);

    $cast = new DateTimeInterfaceCast($dateClass);
    $payload = '2026-12-01T09:30:00+01:00';
    $date = $cast->get($payload);

    expect($date::class)->toBe($dateClass)
        ->and($date->format(DATE_ATOM))->toBe($payload)
        ->and($cast->set($date))->toBe($payload)
        ->and($cast->get(null))->toBeNull()
        ->and($cast->set(null))->toBeNull();
})->with([
    DateTime::class,
    DateTimeImmutable::class,
    Carbon::class,
    CarbonImmutable::class,
    IlluminateCarbon::class,
]);

it('rejects unsupported date types', function () {
    (new DateTimeInterfaceCast(stdClass::class))->get('2026-12-01T09:30:00+01:00');
})->throws(Exception::class, 'Could not cast DateTime type `stdClass`');
