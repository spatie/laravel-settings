<?php

namespace Spatie\LaravelSettings;

use Illuminate\Events\Dispatcher;
use Spatie\LaravelSettings\Events\SettingsLoaded;
use Spatie\LaravelSettings\Events\SettingsSaved;

class SettingsEventSubscriber
{
    private SettingsCache $settingsCache;

    public function __construct(SettingsCache $settingsCache)
    {
        $this->settingsCache = $settingsCache;
    }

    public function subscribe(Dispatcher $dispatcher)
    {
        if (! $this->settingsCache->isEnabled()) {
            return;
        }

        $dispatcher->listen(
            SettingsSaved::class,
            function (SettingsSaved $event) {
                $this->settingsCache->put($event->settings);
            }
        );

        $dispatcher->listen(
            SettingsLoaded::class,
            function (SettingsLoaded $event) {
                if ($this->settingsCache->has(get_class($event->settings))) {
                    return;
                }

                $this->settingsCache->put($event->settings);
            }
        );
    }
}
