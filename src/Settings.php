<?php

namespace Spatie\LaravelSettings;

use Spatie\DataTransferObject\DataTransferObject;
use Spatie\LaravelSettings\SettingsRepository\SettingsRepository;

abstract class Settings extends DataTransferObject
{
    private ?string $repository = null;

    abstract public static function group(): string;

    public function repository(?string $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function fill(array $properties): self
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }

        return $this;
    }

    public function save(?string $repository = null): void
    {
        $mapper = $repository === null
            ? resolve(SettingsMapper::class)
            : resolve(SettingsMapper::class)->repository($repository);

        $mapper->save($this);
    }

    public function lock(string ...$properties)
    {
        $this->resolveRepository()->lockProperties(static::group(), $properties);
    }

    public function unlock(string ...$properties)
    {
        $this->resolveRepository()->unlockProperties(static::group(), $properties);
    }

    public static function fake(array $values): self
    {
        $defaultProperties = app(SettingsRepository::class)->getPropertiesInGroup(static::group());

        return app()->instance(static::class, new static(
            array_merge($defaultProperties, $values)
        ));
    }

    private function resolveRepository(): SettingsRepository
    {
        return SettingsRepositoryFactory::create($this->repository);
    }
}
