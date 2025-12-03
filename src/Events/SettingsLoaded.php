<?php

namespace Spatie\LaravelSettings\Events;

use Spatie\LaravelSettings\Settings;

class SettingsLoaded
{
    public function __construct(public Settings $settings, public bool $loadedFromCache)
    {
    }
}
