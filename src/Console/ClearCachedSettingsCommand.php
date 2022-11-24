<?php

namespace Spatie\LaravelSettings\Console;

use Illuminate\Console\Command;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;

class ClearCachedSettingsCommand extends Command
{
    protected $signature = 'settings:clear-cache';

    protected $description = 'Clear cached settings';

    public function handle(SettingsCacheFactory $settingsCacheFactory): void
    {
        foreach ($settingsCacheFactory->all() as $settingsCache) {
            $settingsCache->clear();
        }

        $this->info('Cached settings cleared!');
    }
}
