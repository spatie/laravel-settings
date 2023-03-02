<?php

namespace Spatie\LaravelSettings\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ClearDiscoveredSettingsCacheCommand extends Command
{
    protected $signature = 'settings:clear-discovered';

    protected $description = 'Clear cached auto discovered registered settings classes';

    public function handle(Filesystem $files): void
    {
        $files->delete(config('settings.discovered_settings_cache_path') . '/settings.php');

        $this->info('Cached discovered settings cleared!');
    }
}
