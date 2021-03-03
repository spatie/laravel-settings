<?php

namespace Spatie\LaravelSettings\Console;

use Illuminate\Console\Command;
use Spatie\LaravelSettings\SettingsCache;

class ClearCachedSettingsCommand extends Command
{
    protected $signature = 'settings:clear-cache';

    protected $description = 'Clear cached settings';

    public function handle(SettingsCache $settingsCache): void
    {
        $settingsCache->clear();

        $this->info('Cached settings cleared!');
    }
}
