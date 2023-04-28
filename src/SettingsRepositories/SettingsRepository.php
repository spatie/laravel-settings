<?php

namespace Spatie\LaravelSettings\SettingsRepositories;

interface SettingsRepository
{
    /**
     * Get all the properties in the repository for a single group
     */
    public function getPropertiesInGroup(string $group): array;

    /**
     * Check if a property exists in a group
     */
    public function checkIfPropertyExists(string $group, string $name): bool;

    /**
     * Get the payload of a property
     */
    public function getPropertyPayload(string $group, string $name);

    /**
     * Create a property within a group with a payload
     */
    public function createProperty(string $group, string $name, $payload): void;

    /**
     * Update the payloads of properties within a group.
     */
    public function updatePropertiesPayload(string $group, array $properties): void;

    /**
     * Delete a property from a group
     */
    public function deleteProperty(string $group, string $name): void;

    /**
     * Lock a set of properties for a specific group
     */
    public function lockProperties(string $group, array $properties): void;

    /**
     * Unlock a set of properties for a group
     */
    public function unlockProperties(string $group, array $properties): void;

    /**
     * Get all the locked properties within a group
     */
    public function getLockedProperties(string $group): array;
}
