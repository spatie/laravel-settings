<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

use DateTime;
use Spatie\LaravelSettings\Attributes\ShouldBeEncrypted;
use Spatie\LaravelSettings\Settings;

class DummyEncryptedSettings extends Settings
{
    public string $string;

    public ?string $nullable;

    public DateTime $cast;

    #[ShouldBeEncrypted()]
    public string $uses_attribute;

    public static function group(): string
    {
        return 'dummy_encrypted';
    }

    public static function encrypted(): array
    {
        return ['string', 'nullable', 'cast'];
    }
}
