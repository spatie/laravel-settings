<?php

namespace Spatie\LaravelSettings\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Spatie\LaravelSettings\SettingsContainer;

class CacheDiscoveredSettingsCommand extends Command
{
    protected $signature = 'settings:discover';

    protected $description = 'Cache all auto discovered settings';

    public function handle(SettingsContainer $container, Filesystem $files): void
    {
        $this->info('Caching registered settings...');

        $container
            ->clearCache()
            ->getSettingClasses()
            ->pipe(function (Collection $settingClasses) use ($files) {
                $cachePath = config('settings.discovered_settings_cache_path');

                $files->makeDirectory($cachePath, 0755, true, true);

                $files->put(
                    $cachePath . '/settings.php',
                    '<?php return ' . var_export($settingClasses->toArray(), true) . ';'
                );
            });

        $this->info('All done!');
    }
}
