<?php

namespace Spatie\LaravelSettings\Events;

use Spatie\LaravelSettings\Settings;

class SettingsLoaded
{
    public Settings $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }
}
