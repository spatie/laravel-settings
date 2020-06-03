<?php

namespace Spatie\LaravelSettings\Exceptions;

use Exception;

class SettingAlreadyExists extends Exception
{
    public static function whenAdding(string $property): self
    {
        throw new self("Could not create setting {$property} because it already exists");
    }

    public static function whenRenaming(string $from, string $to): self
    {
        return new self("Could not rename setting {$from} to {$to} because it already exists");
    }
}
