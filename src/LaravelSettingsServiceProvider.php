<?php

namespace Spatie\LaravelSettings;

use Exception;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\Commands\MakeSettingsMigrationCommand;
use Spatie\LaravelSettings\SettingsRepository\DatabaseSettingsRepository;
use Spatie\LaravelSettings\SettingsRepository\SettingsRepository;

class LaravelSettingsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/settings.php' => config_path('settings.php'),
            ], 'settings');

            if (! class_exists('CreateSettingsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_settings_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_settings_table.php'),
                ], 'migrations');
            }

            $this->commands([
                MakeSettingsMigrationCommand::class,
            ]);
        }

        $this->loadMigrationsFrom(config('settings.migrations_path'));
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/settings.php', 'settings');

        $this->initiateConnection();
        $this->registerSettingBinding();
    }

    private function initiateConnection(): void
    {
        $this->app->instance(SettingsRepository::class, SettingsRepositoryFactory::create());
    }

    private function registerSettingBinding(): void
    {
        /** @var \Spatie\LaravelSettings\Settings[] $settings */
        $settings = config('settings.settings');

        foreach ($settings as $setting) {
            $this->app->bind(
                $setting,
                fn () => $this->app->make(SettingsMapper::class)->load($setting)
            );
        }
    }
}
