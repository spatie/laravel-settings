<?php

namespace Spatie\LaravelSettings\Events;

use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\Support\SettingsPropertyDataCollection;

class SavingSettings
{
    public Settings $settings;

    public string $settingsClass;

    public SettingsPropertyDataCollection $properties;

    public function __construct(
        string $settingsClass,
        SettingsPropertyDataCollection $properties,
        Settings $settings
    ) {
        $this->settingsClass = $settingsClass;

        $this->properties = $properties;

        $this->settings = $settings;
    }
}
