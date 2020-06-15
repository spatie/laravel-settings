<?php

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
];
