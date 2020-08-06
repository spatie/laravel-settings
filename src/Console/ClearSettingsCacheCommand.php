<?php

namespace Spatie\LaravelSettings\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ClearSettingsCacheCommand extends Command
{
    protected $signature = 'settings:clear';

    protected $description = 'Clear cached registered settings';

    public function handle(Filesystem $files): void
    {
        $files->delete(config('settings.cache_path') . '/settings.php');

        $this->info('Cached discovered settings cleared!');
    }
}
