<?php


namespace Spatie\LaravelSettings\SettingsRepositories;

use Spatie\LaravelSettings\SettingsProperty;

interface SettingsRepository
{
    public function __construct(array $config);

    public function getPropertiesInGroup(string $group): array;

    public function checkIfPropertyExists(string $group, string $name): bool;

    public function getPropertyPayload(string $group, string $name);

    public function createProperty(string $group, string $name, $payload): void;

    public function updatePropertyPayload(string $group, string $name, $value): void;

    public function deleteProperty(string $group, string $name);

    public function lockProperties(string $group, array $properties);

    public function unlockProperties(string $group, array $properties);

    public function getLockedProperties(string $group): array;

    public function import(array $data);

    public function export(): array;
}
