<?php

namespace Spatie\LaravelSettings\Factories;

use Exception;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;

class SettingsRepositoryFactory
{
    public static function create(?string $name = null): SettingsRepository
    {
        $name ??= config('settings.default_repository');

        if (! array_key_exists($name, config('settings.repositories'))) {
            throw new Exception("Tried to create unknown settings repository: {$name}");
        }

        $config = config("settings.repositories.{$name}");

        return app($config['type'], [
            'config' => $config,
        ]);
    }
}
