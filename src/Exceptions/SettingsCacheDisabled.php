<?php

namespace Spatie\LaravelSettings\Exceptions;

use Exception;

class SettingsCacheDisabled extends Exception
{
    public static function create(): self
    {
        return new self('Settings cache is not enabled');
    }
}
