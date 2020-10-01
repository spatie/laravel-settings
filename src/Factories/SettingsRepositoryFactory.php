<?php

namespace Spatie\LaravelSettings\Factories;

use Exception;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;
use Spatie\LaravelSettings\SettingsRepositories\RedisSettingsRepository;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;

class SettingsRepositoryFactory
{
    private static array $mapping = [
        'database' => DatabaseSettingsRepository::class,
        'redis' => RedisSettingsRepository::class,
    ];

    public static function create(?string $name = null): SettingsRepository
    {
        $name ??= config('settings.default_repository');

        // TODO: you should check the config not the mapping, although the mapping should also be checked
        if (! array_key_exists($name, static::$mapping)) {
            throw new Exception("Tried to create unknown settings repository: {$name}");
        }

        $config = config('settings.repositories.'. config('settings.default_repository'));

        return new static::$mapping[$name]($config);
    }
}
