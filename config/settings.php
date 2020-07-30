<?php

use Carbon\Carbon;
use Carbon\CarbonImmutable;

return [
    'settings' => [

    ],

    'migrations_path' => database_path('settings'),

    'default_repository' => 'database',

    'repositories' => [
        'database' => [
            'type' => Spatie\LaravelSettings\SettingsRepository\DatabaseSettingsRepository::class,
            'model' => Spatie\LaravelSettings\SettingsProperty::class,
            'connection' => null,
        ],
    ],

    'casts' => [
        DateTime::class => Spatie\LaravelSettings\SettingCasts\DateTimeCast::class,
        DateTimeImmutable::class => Spatie\LaravelSettings\SettingCasts\DateTimeImmutableCast::class,
        Carbon::class => Spatie\LaravelSettings\SettingCasts\CarbonCast::class,
        CarbonImmutable::class => Spatie\LaravelSettings\SettingCasts\CarbonImmutableCast::class,
    ],
];
