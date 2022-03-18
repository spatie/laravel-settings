<?php

use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;

dataset('configurationsProvider', [
    fn () => new DatabaseSettingsRepository([
        'connection' => 'other',
    ]),
    function () {
        $model = new class() extends SettingsProperty {
            public function getConnectionName()
            {
                return 'other';
            }
        };

        return new DatabaseSettingsRepository([
            'model' => get_class($model),
        ]);
    },
]);
