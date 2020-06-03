<?php

namespace Spatie\LaravelSettings\Exceptions;

use Exception;

class SettingDoesNotExist extends Exception
{
    public static function whenDeleting(string $property): self
    {
        return new self("Could not delete setting {$property} because it does not exist");
    }

    public static function whenEditing(string $property): self
    {
        return new self("Could not edit setting {$property} because it does not exist");
    }

    public static function whenRenaming(string $from, string $to): self
    {
        return new self("Could not rename setting {$from} to {$to} because it does not exist");
    }
}
