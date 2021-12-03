<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

use Spatie\LaravelSettings\Settings;

class DummySettingsWithImportedType extends Settings
{
    /** @var DummyDto[] */
    public array $dto_array;

    public static function group(): string
    {
        return 'dummy_with_imported';
    }
}
