<?php

namespace Spatie\LaravelSettings;

use Illuminate\Database\Events\SchemaLoaded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\Console\CacheDiscoveredSettingsCommand;
use Spatie\LaravelSettings\Console\ClearCachedSettingsCommand;
use Spatie\LaravelSettings\Console\ClearDiscoveredSettingsCacheCommand;
use Spatie\LaravelSettings\Console\MakeSettingCommand;
use Spatie\LaravelSettings\Console\MakeSettingsMigrationCommand;
use Spatie\LaravelSettings\Factories\SettingsRepositoryFactory;
use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class LaravelSettingsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/settings.php' => config_path('settings.php'),
            ], 'config');

            if (! class_exists('CreateSettingsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_settings_table.php.stub' => database_path('migrations/2022_12_14_083707_create_settings_table.php'),
                ], 'migrations');
            }

            $this->commands([
                MakeSettingCommand::class,
                MakeSettingsMigrationCommand::class,
                CacheDiscoveredSettingsCommand::class,
                ClearDiscoveredSettingsCacheCommand::class,
                ClearCachedSettingsCommand::class,
            ]);
        }

        Event::subscribe(SettingsEventSubscriber::class);
        Event::listen(SchemaLoaded::class, fn ($event) => $this->removeMigrationsWhenSchemaLoaded($event));

        $this->loadMigrationsFrom($this->resolveMigrationPaths());
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/settings.php', 'settings');

        $this->app->bind(SettingsRepository::class, fn () => SettingsRepositoryFactory::create());

        $this->app->bind(SettingsCacheFactory::class, fn () => new SettingsCacheFactory(
            config('settings'),
        ));

        $this->app->scoped(SettingsMapper::class);

        $settingsContainer = app(SettingsContainer::class);
        $settingsContainer->registerBindings();
    }

    private function removeMigrationsWhenSchemaLoaded(SchemaLoaded $event)
    {
        $files = Finder::create()
            ->files()
            ->ignoreDotFiles(true)
            ->in($this->resolveMigrationPaths())
            ->depth(0);

        $migrations = collect(iterator_to_array($files))
            ->map(function (SplFileInfo $file) {
                $contents = file_get_contents($file->getRealPath());

                if (
                    str_contains($contents, 'return new class extends '.SettingsMigration::class)
                    || str_contains($contents, 'return new class extends SettingsMigration')
                    || str_contains($contents, 'return new class() extends '.SettingsMigration::class)
                    || str_contains($contents, 'return new class() extends SettingsMigration')
                ) {
                    return $file->getBasename('.php');
                }

                preg_match('/class\s*(?P<className>\w*)\s*extends/', $contents, $found);

                if (empty($found['className'])) {
                    return null;
                }

                require_once $file->getRealPath();

                if (! is_subclass_of($found['className'], SettingsMigration::class)) {
                    return null;
                }

                return $file->getBasename('.php');
            })
            ->filter()
            ->values();

        $migrationsConfig = config()->get('database.migrations');

        $migrationsTable = is_array($migrationsConfig) ? ($migrationsConfig['table'] ?? null) : $migrationsConfig;

        $event->connection
            ->table($migrationsTable)
            ->useWritePdo()
            ->whereIn('migration', $migrations)
            ->delete();
    }

    protected function resolveMigrationPaths(): array
    {
        return ! empty(config('settings.migrations_path'))
            ? [config('settings.migrations_path')]
            : config('settings.migrations_paths');
    }
}
