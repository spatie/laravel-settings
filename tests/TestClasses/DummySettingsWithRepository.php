<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

use Spatie\LaravelSettings\Settings;

class DummySettingsWithRepository extends Settings
{
    public string $name;

    public string $description;

    public static function repository(): ?string
    {
        return 'other_repository';
    }

    public static function group(): string
    {
        return 'dummy_simple';
    }
}
