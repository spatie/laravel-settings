<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

use Spatie\LaravelSettings\Settings;

class DummySettingsWithDefaultValue extends Settings
{
    public string $site = 'spatie.be';

    public static function group(): string
    {
        return 'dummy_settings_with_default_value';
    }
}
