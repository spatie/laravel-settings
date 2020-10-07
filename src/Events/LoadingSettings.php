<?php

namespace Spatie\LaravelSettings\Events;

use Spatie\LaravelSettings\Settings;

class LoadingSettings
{
    public string $settingsClass;

    /** @var array|\Spatie\LaravelSettings\Support\SettingsPropertyData[]  */
    public array $properties;

    public function __construct(string $settingsClass, array $properties)
    {
        $this->settingsClass = $settingsClass;
        $this->properties = $properties;
    }
}
