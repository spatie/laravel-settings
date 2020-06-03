<?php

namespace App\Support\Settings;

use App\Support\Settings\Commands\MakeSettingsMigrationCommand;
use App\Support\Settings\SettingsConnection\DatabaseSettingsConnection;
use App\Support\Settings\SettingsConnection\SettingsConnection;
use Exception;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->initiateConnection();
        $this->registerSettingBinding();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeSettingsMigrationCommand::class,
            ]);
        }

        $this->loadMigrationsFrom(database_path('settings'));
    }

    private function initiateConnection(): void
    {
        if (config('settings.connection') !== 'database') {
            throw new Exception('Unknown settings connection');
        }

        $this->app->instance(SettingsConnection::class, new DatabaseSettingsConnection());
    }

    private function registerSettingBinding(): void
    {
        /** @var \App\Support\Settings\Settings[] $settings */
        $settings = config('settings.settings');

        foreach ($settings as $setting) {
            $this->app->bind(
                $setting,
                fn () => $this->app->make(SettingsMapper::class)->load($setting)
            );
        }
    }
}
