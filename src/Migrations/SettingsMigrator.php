<?php

namespace Spatie\LaravelSettings\Migrations;

use Closure;
use Illuminate\Support\Collection;
use Spatie\LaravelSettings\Exceptions\InvalidSettingName;
use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;
use Spatie\LaravelSettings\Exceptions\SettingDoesNotExist;
use Spatie\LaravelSettings\Factories\SettingsRepositoryFactory;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;
use Spatie\LaravelSettings\SettingsConfig;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;
use Spatie\LaravelSettings\Support\Crypto;

class SettingsMigrator
{
    protected SettingsRepository $repository;

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

    public function add(string $property, $value = null, bool $encrypted = false): void
    {
        if ($this->checkIfPropertyExists($property)) {
            throw SettingAlreadyExists::whenAdding($property);
        }

        if ($encrypted) {
            $value = Crypto::encrypt($value);
        }

        $this->createProperty($property, $value);
    }

    public function delete(string $property): void
    {
        if (! $this->checkIfPropertyExists($property)) {
            throw SettingDoesNotExist::whenDeleting($property);
        }

        $this->deleteProperty($property);
    }

    public function deleteIfExists(string $property): void
    {
        if ($this->checkIfPropertyExists($property)) {
            $this->deleteProperty($property);
        }
    }

    public function update(string $property, Closure $closure, bool $encrypted = false): void
    {
        if (! $this->checkIfPropertyExists($property)) {
            throw SettingDoesNotExist::whenEditing($property);
        }

        $originalPayload = $encrypted
            ? Crypto::decrypt($this->getPropertyPayload($property))
            : $this->getPropertyPayload($property);

        $updatedPayload = $encrypted
            ? Crypto::encrypt($closure($originalPayload))
            : $closure($originalPayload);

        $this->updatePropertyPayload($property, $updatedPayload);
    }

    public function addEncrypted(string $property, $value = null): void
    {
        $this->add($property, $value, true);
    }

    public function updateEncrypted(string $property, Closure $closure): void
    {
        $this->update($property, $closure, true);
    }

    public function encrypt(string $property): void
    {
        $this->update($property, fn ($payload) => Crypto::encrypt($payload));
    }

    public function decrypt(string $property): void
    {
        $this->update($property, fn ($payload) => Crypto::decrypt($payload));
    }

    public function exists(string $property): bool
    {
        return $this->checkIfPropertyExists($property);
    }

    public function inGroup(string $group, Closure $closure): void
    {
        $closure(new SettingsBlueprint($group, $this));
    }

    protected function getPropertyParts(string $property): array
    {
        $propertyParts = explode('.', $property);

        if (count($propertyParts) !== 2) {
            throw InvalidSettingName::create($property);
        }

        return ['group' => $propertyParts[0], 'name' => $propertyParts[1]];
    }

    protected function checkIfPropertyExists(string $property): bool
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        return $this->repository->checkIfPropertyExists($group, $name);
    }

    protected function getPropertyPayload(string $property)
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        $payload = $this->repository->getPropertyPayload($group, $name);

        return $this->getCast($group, $name)?->get($payload) ?: $payload;
    }

    protected function createProperty(string $property, $payload): void
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        if (is_object($payload)) {
            $payload = $this->getCast($group, $name)?->set($payload) ?: $payload;
        }

        $this->repository->createProperty($group, $name, $payload);
    }

    protected function updatePropertyPayload(string $property, $payload): void
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        if (is_object($payload)) {
            $payload = $this->getCast($group, $name)?->set($payload) ?: $payload;
        }

        $this->repository->updatePropertiesPayload($group, [$name => $payload]);
    }

    protected function deleteProperty(string $property): void
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        $this->repository->deleteProperty($group, $name);
    }

    protected function getCast(string $group, string $name): ?SettingsCast
    {
        return $this->settingsGroups()->get($group)?->getCast($name);
    }

    protected function settingsGroups(): Collection
    {
        return app(SettingsContainer::class)
            ->getSettingClasses()
            ->mapWithKeys(fn (string $settingsClass) => [
                $settingsClass::group() => new SettingsConfig($settingsClass),
            ]);
    }
}
