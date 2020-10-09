<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | You can register all the settings dto's here.
    |
    */

    'settings' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Migrations path
    |--------------------------------------------------------------------------
    |
    | When you create a new settings migration via the `make:settings-migration`
    | command the package will store these migrations in this directory.
    |
    */

    'migrations_path' => database_path('settings'),

    /*
    |--------------------------------------------------------------------------
    | Default repository
    |--------------------------------------------------------------------------
    |
    | When no repository was set for a settings dto this repository will be
    | used for loading and saving settings.
    |
    */

    'default_repository' => 'database',

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | Settings will be stored and loaded from these repositories. There are
    | two types of repositories: database and redis. But its always
    | possible to create your own repositories.
    |
    */

    'repositories' => [
        'database' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository::class,
            'model' => \Spatie\LaravelSettings\Models\SettingsProperty::class,
            'connection' => null,
        ],
        'redis' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\RedisSettingsRepository::class,
            'connection' => null,
            'prefix' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default casts
    |--------------------------------------------------------------------------
    |
    | When the package discovers a setting with a type other than the PHP built
    | in types, it should be casted. These casts will automatically cast types
    | when they occur in a settings dto.
    |
    */

    'default_casts' => [
        DateTimeInterface::class => Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast::class,
        DateTimeZone::class => Spatie\LaravelSettings\SettingsCasts\DateTimeZoneCast::class,
        Spatie\DataTransferObject\DataTransferObject::class => Spatie\LaravelSettings\SettingsCasts\DtoCast::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto discover setting
    |--------------------------------------------------------------------------
    |
    | The package will look for settings in these paths and automatically
    | register them.
    |
    */

    'auto_discover_settings' => [
        app()->path(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache path
    |--------------------------------------------------------------------------
    |
    | When in production it is advised to cache the automatically discovered
    | and registered setting dto's these will be cached in this path.
    |
    */

    'cache_path' => storage_path('app/laravel-settings'),
];
