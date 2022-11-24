<?php

namespace Spatie\LaravelSettings;

use Illuminate\Events\Dispatcher;
use Spatie\LaravelSettings\Events\SettingsLoaded;
use Spatie\LaravelSettings\Events\SettingsSaved;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;

class SettingsEventSubscriber
{
    private SettingsCacheFactory $settingsCacheFactory;

    public function __construct(SettingsCacheFactory $settingsCacheFactory)
    {
        $this->settingsCacheFactory = $settingsCacheFactory;
    }

    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen(
            SettingsSaved::class,
            function (SettingsSaved $event) {
                $cache = $this->settingsCacheFactory->build(
                    $event->settings::repository()
                );

                if ($cache->isEnabled()) {
                    $cache->put($event->settings);
                }
            }
        );

        $dispatcher->listen(
            SettingsLoaded::class,
            function (SettingsLoaded $event) {
                $cache = $this->settingsCacheFactory->build(
                    $event->settings::repository()
                );

                if ($cache->has(get_class($event->settings))) {
                    return;
                }

                $cache->put($event->settings);
            }
        );
    }
}
