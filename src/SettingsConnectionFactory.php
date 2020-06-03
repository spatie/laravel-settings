<?php

namespace App\Support\Settings;

use App\Support\Settings\SettingsConnection\DatabaseSettingsConnection;
use App\Support\Settings\SettingsConnection\SettingsConnection;
use Exception;

class SettingsConnectionFactory
{
    private static array $mapping = [
        'database' => DatabaseSettingsConnection::class,
    ];

    public static function create(string $name): SettingsConnection
    {
        if (! array_key_exists($name, static::$mapping)) {
            throw new Exception("Tried to create unknown settings connection: {$name}");
        }

        return new static::$mapping[$name];
    }
}
