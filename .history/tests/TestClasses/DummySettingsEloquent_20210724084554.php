<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

use Spatie\LaravelSettings\SettingsEloquent;

class DummySettingsEloquent extends SettingsEloquent
{
    public string $name;

    public string $description;

    public static function group(): string
    {
        return 'dummy_simple';
    }
}
