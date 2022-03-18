<?php

namespace Spatie\LaravelSettings\Tests\Factories;

use Spatie\LaravelSettings\Factories\SettingsRepositoryFactory;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;

it('can create the default repository', function () {
    expect(SettingsRepositoryFactory::create())
        ->toBeInstanceOf(DatabaseSettingsRepository::class);
});

it('can create a specific repository', function () {
    expect(SettingsRepositoryFactory::create())
        ->toBeInstanceOf(DatabaseSettingsRepository::class);
});
