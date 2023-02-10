<?php

namespace Spatie\LaravelSettings\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Support\SettingsStructureScout;

class CacheDiscoveredSettingsCommand extends Command
{
    protected $signature = 'settings:discover';

    protected $description = 'Cache all auto discovered settings';

    public function handle(SettingsContainer $container, Filesystem $files): void
    {
        $this->info('Caching registered settings...');

        SettingsStructureScout::create()->clear();
        SettingsStructureScout::create()->cache();

        $this->info('All done!');
    }
}
