<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

use Spatie\LaravelSettings\Settings;

class DummySettingsEloquent extends Settings
{
    public string $name;

    public string $description;

    public static function group(): string
    {
        return 'dummy_simple';
    }
}
