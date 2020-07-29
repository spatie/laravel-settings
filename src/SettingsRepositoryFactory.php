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

    public static function create(?string $name = null): SettingsRepository
    {
        $name ??= config('settings.default_repository');

        if (! array_key_exists($name, static::$mapping)) {
            throw new Exception("Tried to create unknown settings repository: {$name}");
        }

        $config = config('settings.repositories.'. config('settings.default_repository'));

        return new static::$mapping[$name]($config);
    }
}
