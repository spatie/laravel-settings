<?php

namespace Spatie\LaravelSettings\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Spatie\LaravelSettings\Support\SettingsStructureScout;

class ClearDiscoveredSettingsCacheCommand extends Command
{
    protected $signature = 'settings:clear-discovered';

    protected $description = 'Clear cached auto discovered registered settings classes';

    public function handle(Filesystem $files): void
    {
        SettingsStructureScout::create()->clear();

        $this->info('Cached discovered settings cleared!');
    }
}
