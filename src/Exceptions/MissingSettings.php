<?php

namespace Spatie\LaravelSettings\Exceptions;

use Exception;

class MissingSettings extends Exception
{
    public static function create(string $settingsClass, array $missingProperties, string $operation): self
    {
        $missing = implode(', ', $missingProperties);

        return new self("Tried {$operation} settings '{$settingsClass}', and the following properties were missing: {$missing}");
    }
}
