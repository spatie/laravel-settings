<?php


namespace Spatie\LaravelSettings\SettingsRepository;

use Spatie\LaravelSettings\SettingsProperty;

interface SettingsRepository
{
    public function getPropertiesInGroup(string $group): array;

    public function checkIfPropertyExists(string $group, string $name): bool;

    public function getPropertyPayload(string $group, string $name);

    public function createProperty(string $group, string $name, $payload): SettingsProperty;

    public function updatePropertyPayload(string $group, string $name, $value): void;

    public function deleteProperty(string $group, string $name);

    public function import(array $data);

    public function export(): array;
}
