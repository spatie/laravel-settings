<?php

use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;
use Spatie\LaravelSettings\Models\SettingsProperty;

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
    }
]);