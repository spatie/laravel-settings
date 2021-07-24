<?php

namespace Spatie\LaravelSettings\Events;

use Spatie\LaravelSettings\Interfaces\Settings;

class SettingsLoaded
{
    public Settings $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }
}
