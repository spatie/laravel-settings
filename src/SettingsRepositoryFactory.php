<?php

namespace Spatie\LaravelSettings;

use Exception;
use Spatie\LaravelSettings\SettingsRepository\DatabaseSettingsRepository;
use Spatie\LaravelSettings\SettingsRepository\SettingsRepository;

class SettingsRepositoryFactory
{
    private static array $mapping = [
        'database' => DatabaseSettingsRepository::class,
    ];

    public static function create(string $name): SettingsRepository
    {
        if (! array_key_exists($name, static::$mapping)) {
            throw new Exception("Tried to create unknown settings repository: {$name}");
        }

        return new static::$mapping[$name];
    }
}
