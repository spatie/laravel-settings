<?php

namespace App\Support\Settings\Exceptions;

use Exception;

class SettingDoesNotExist extends Exception
{
    public static function whenMerging(string $property): self
    {
        return new self("Could not merge from setting {$property} because it does not exist");
    }

    public static function whenSplitting(string $property): self
    {
        return new self("Could not split from setting {$property} because it does not exist");
    }

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
