<?php

namespace Spatie\LaravelSettings\Exceptions;

use Exception;
use ReflectionProperty;

class CouldNotCastSetting extends Exception
{
    public static function fromRepository(
        string $settingsClass,
        string $property,
        ReflectionProperty $reflection
    ): self {
        return new self("Could not cast `{$settingsClass}::{$property}` to type {$reflection->getType()->getName()}");
    }

    public static function toRepository(
        string $settingsClass,
        string $property,
        ReflectionProperty $reflection
    ): self
    {
        return new self("Could not cast `{$settingsClass}::{$property}` from type {$reflection->getType()->getName()}");
    }
}
