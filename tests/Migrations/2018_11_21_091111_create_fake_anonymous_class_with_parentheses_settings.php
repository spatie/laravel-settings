<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->inGroup('anonymous-class-general', function (SettingsBlueprint $migrator) {
            $migrator->add('name', 'laravel-settings');
            $migrator->add('organization', 'spatie');
        });
    }
};
