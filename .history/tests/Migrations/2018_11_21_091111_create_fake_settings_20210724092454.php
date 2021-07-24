<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateFakeSettings extends SettingsMigration
{
    public function up()
    {
        $this->migrator->inGroup('general', function (SettingsBlueprint $migrator) {
            $migrator->add('name', 'laravel-settings');
            $migrator->add('organization', 'spatie');
        });

        $this->migrator->inGroup('settings_eloquent', function (SettingsBlueprint $migrator) {
            $migrator->add('fanme', 'laravel-settings');
            $migrator->add('lanme', 'spatie');
        });
    }
}
