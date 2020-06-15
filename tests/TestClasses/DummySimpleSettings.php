<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

use Spatie\LaravelSettings\Settings;

class DummySimpleSettings extends Settings
{
    public string $name;

    public string $description;

    public static function group(): string
    {
        return 'dummy_simple';
    }
}
