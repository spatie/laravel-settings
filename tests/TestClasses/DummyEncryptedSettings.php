<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

use DateTime;
use Spatie\LaravelSettings\Settings;

class DummyEncryptedSettings extends Settings
{
    public string $string;

    public ?string $nullable;

    public DateTime $cast;

    public static function group(): string
    {
        return 'dummy_encrypted';
    }

    public static function encrypted(): array
    {
        return ['string', 'nullable', 'cast'];
    }
}
