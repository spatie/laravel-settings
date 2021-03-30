<?php

namespace Spatie\LaravelSettings\Events;

use Illuminate\Support\Collection;
use Spatie\LaravelSettings\Settings;

class SavingSettings
{
    public Settings $settings;

    public Collection $properties;

    public Collection $old;

    public function __construct(
        Collection $properties,
        Collection $old,
        Settings $settings
    ) {
        $this->properties = $properties;

        $this->old = $old;

        $this->settings = $settings;
    }
}
