<?php

namespace Spatie\LaravelSettings\Events;

use Illuminate\Support\Collection;

class LoadingSettings
{
    public string $settingsClass;

    public Collection $properties;

    public function __construct(string $settingsClass, Collection $properties)
    {
        $this->settingsClass = $settingsClass;
        $this->properties = $properties;
    }
}
