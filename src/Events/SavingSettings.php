<?php

namespace Spatie\LaravelSettings\Events;

use Illuminate\Support\Collection;
use Spatie\LaravelSettings\Settings;

class SavingSettings
{
    public Settings $settings;

    public Collection $properties;

    public ?Collection $originalValues;

    public function __construct(
        Collection $properties,
        ?Collection $originalValues,
        Settings $settings
    ) {
        $this->properties = $properties;

        $this->originalValues = $originalValues;

        $this->settings = $settings;
    }
}
