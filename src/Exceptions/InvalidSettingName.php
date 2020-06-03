<?php

namespace Spatie\LaravelSettings\Exceptions;

use Exception;

class InvalidSettingName extends Exception
{
    public static function create(string $property): self
    {
        return new self("Setting {$property} is invalid, it should be formatted as such: group.name");
    }
}
