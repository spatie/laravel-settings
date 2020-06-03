<?php

namespace App\Support\Settings;

use App\Support\Settings\Exceptions\InvalidSettingName;
use App\Support\Settings\Exceptions\InvalidSplittingConfig;
use App\Support\Settings\Exceptions\SettingAlreadyExists;
use App\Support\Settings\Exceptions\SettingDoesNotExist;
use App\Support\Settings\SettingsConnection\SettingsConnection;
use Closure;

class SettingsMigrator
{
    private SettingsConnection $connection;

    public function __construct(SettingsConnection $connection)
    {
        $this->connection = $connection;
    }

    public function connection(string $name): self
    {
        $this->connection = SettingsConnectionFactory::create($name);

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

    public function addMany(string $group, array $values): void
    {
        foreach ($values as $name => $value) {
            $this->add("{$group}.{$name}", $value);
        }
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

    public function merge(array $from, string $to, Closure $closure): void
    {
        if ($this->checkIfPropertyExists($to)) {
            throw SettingAlreadyExists::whenMerging($to);
        }

        $values = array_map(
            function (string $property) {
                if (! $this->checkIfPropertyExists($property)) {
                    throw SettingDoesNotExist::whenMerging($property);
                }

                return $this->getPropertyPayload($property);
            },
            $from
        );

        $this->createProperty(
            $to,
            $closure(...$values)
        );

        foreach ($from as $fromProperty) {
            $this->deleteProperty($fromProperty);
        }
    }

    public function split(string $from, array $to, Closure ...$closures): void
    {
        if (! $this->checkIfPropertyExists($from)) {
            throw SettingDoesNotExist::whenSplitting($from);
        }

        if (count($to) !== count($closures)) {
            throw InvalidSplittingConfig::create(count($to), count($closures));
        }

        $value = $this->getPropertyPayload($from);

        foreach (array_values($to) as $i => $property) {
            if ($this->checkIfPropertyExists($property)) {
                throw SettingAlreadyExists::whenSplitting($property);
            }

            $this->createProperty(
                $property,
                $closures[$i]($value)
            );
        }

        $this->deleteProperty($from);
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

        return $this->connection->checkIfPropertyExists($group, $name);
    }

    private function getPropertyPayload(string $property)
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        return $this->connection->getPropertyPayload($group, $name);
    }

    private function createProperty(string $property, $payload): SettingsProperty
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        return $this->connection->createProperty($group, $name, $payload);
    }

    private function updatePropertyPayload(string $property, $payload): void
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        $this->connection->updatePropertyPayload($group, $name, $payload);
    }

    private function deleteProperty(string $property): void
    {
        ['group' => $group, 'name' => $name] = $this->getPropertyParts($property);

        $this->connection->deleteProperty($group, $name);
    }
}
