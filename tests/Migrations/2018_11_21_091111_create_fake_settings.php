<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;

class CreateFakeSettings extends SettingsMigration
{
    public function up()
    {
        $this->migrator->inGroup('general', function (SettingsBlueprint $migrator) {
            $migrator->add('name', 'laravel-settings');
            $migrator->add('organization', 'spatie');
        });
    }
}
