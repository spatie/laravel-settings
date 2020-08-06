<?php

use Carbon\Carbon;
use Carbon\CarbonImmutable;

return [
    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | You can register all the settings dto's here
    |
    */
    'settings' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Migrations path
    |--------------------------------------------------------------------------
    |
    | When creating new setting migrations, the files will be stored in this
    | directory
    |
    */
    'migrations_path' => database_path('settings'),

    /*
    |--------------------------------------------------------------------------
    | Default repository
    |--------------------------------------------------------------------------
    |
    | When no repository explicitly was given to a settings dto this
    | repository will be used for loading and saving settings.
    |
    */
    'default_repository' => 'database',

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | In these repositories you can store you own settings, types of
    | repositories include database and redis, or you can create
    | your own repository types.
    |
    */
    'repositories' => [
        'database' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository::class,
            'model' => Spatie\LaravelSettings\SettingsProperty::class,
            'connection' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | Types other than the primitive PHP types can be converted from and to
    | repositories by these casts.
    |
    */
    'casts' => [
        DateTime::class => Spatie\LaravelSettings\SettingsCasts\DateTimeCast::class,
        DateTimeImmutable::class => Spatie\LaravelSettings\SettingsCasts\DateTimeImmutableCast::class,
        Carbon::class => Spatie\LaravelSettings\SettingsCasts\CarbonCast::class,
        CarbonImmutable::class => Spatie\LaravelSettings\SettingsCasts\CarbonImmutableCast::class,
    ],

    'auto_discover_settings' => [
        app()->path(),
    ],

    'cache_path' => storage_path('app/laravel-settings'),
];
