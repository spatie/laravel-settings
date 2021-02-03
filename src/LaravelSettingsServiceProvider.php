<?php

namespace Spatie\LaravelSettings;

use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\Console\CacheDiscoveredSettingsCommand;
use Spatie\LaravelSettings\Console\ClearDiscoveredSettingsCacheCommand;
use Spatie\LaravelSettings\Console\MakeSettingsMigrationCommand;
use Spatie\LaravelSettings\Factories\SettingsRepositoryFactory;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;

class LaravelSettingsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/settings.php' => config_path('settings.php'),
            ], 'settings');

            if (! class_exists('CreateSettingsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_settings_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_settings_table.php'),
                ], 'migrations');
            }

            $this->commands([
                MakeSettingsMigrationCommand::class,
                CacheDiscoveredSettingsCommand::class,
                ClearDiscoveredSettingsCacheCommand::class,
            ]);
        }

        $this->loadMigrationsFrom(config('settings.migrations_path'));
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/settings.php', 'settings');

        $this->app->singleton(SettingsRepository::class, fn () => SettingsRepositoryFactory::create());

        app(SettingsContainer::class)->registerBindings();
    }
}
