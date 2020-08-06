<?php

namespace Spatie\LaravelSettings\Migrations;

use Closure;
use Spatie\LaravelSettings\Exceptions\InvalidSettingName;
use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;
use Spatie\LaravelSettings\Exceptions\SettingDoesNotExist;
use Spatie\LaravelSettings\SettingsProperty;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;
use Spatie\LaravelSettings\SettingsRepositoryFactory;

class SettingsMigrator
{
    private SettingsRepository $repository;

    public function __construct(SettingsRepository $connection)
    {
        $this->repository = $connection;
    }

    public function repository(string $name): self
    {
        $this->repository = SettingsRepositoryFactory::create($name);

        return $this;
    }

    public function rename(string $from, string $to): void
    {
        if (! $this->checkIfPropertyExists($from)) {
            throw SettingDoesNotExist::whenRenaming($from, $to);
        }

        if ($this->checkIfPropertyExists($to)) {
            throw SettingAlreadyExists::whenRenaming($from, $to);
        }

        $this->createProperty(
            $to,
            $this->getPropertyPayload($from)
        );

        $this->deleteProperty($from);
    }

    public function add(string $property, $value): SettingsProperty
    {
        if ($this->checkIfPropertyExists($property)) {
            throw SettingAlreadyExists::whenAdding($property);
        }

        return $this->createProperty($property, $value);
    }

    public function delete(string $property): void
    {
        if (! $this->checkIfPropertyExists($property)) {
            throw SettingDoesNotExist::whenDeleting($property);
        }

        $this->deleteProperty($property);
    }

    public function update(string $property, Closure $closure): void
    {
        if (! $this->checkIfPropertyExists($property)) {
            throw SettingDoesNotExist::whenEditing($property);
        }

        $this->updatePropertyPayload(
            $property,
            $closure($this->getPropertyPayload($property))
        );
    }

    public function inGroup(string $group, Closure $closure): void
    {
        $closure(new SettingsBlueprint($group, $this));
    }

    private function getPropertyParts(string $property): array
    {
        $propertyParts = explode('.', $property);

        if (count($propertyParts) !== 2) {
            throw InvalidSettingName::create($property);
        }

        return ['group' => $propertyParts[0], 'name' => $propertyParts[1]];
    }

    private function checkIfPropertyExists(string $property): bool
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        return $this->repository->checkIfPropertyExists($group, $name);
    }

    private function getPropertyPayload(string $property)
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        return $this->repository->getPropertyPayload($group, $name);
    }

    private function createProperty(string $property, $payload): SettingsProperty
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        return $this->repository->createProperty($group, $name, $payload);
    }

    private function updatePropertyPayload(string $property, $payload): void
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        $this->repository->updatePropertyPayload($group, $name, $payload);
    }

    private function deleteProperty(string $property): void
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        $this->repository->deleteProperty($group, $name);
    }
}
