<?php

namespace Spatie\LaravelSettings;

use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;

abstract class Settings extends TempDto
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
        $newProperties = array_merge(
            $this->resolveMapper($this->repository)->getSettings(
                static::class
            ),
            $properties
        );

        $this->replaceProperties($newProperties);

        return $this;
    }

    public function save(?string $repository = null): self
    {
        $this->resolveMapper($repository)->save($this);

        return $this;
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

    private function resolveMapper(?string $repository): SettingsMapper
    {
        $repository = $this->repository ?? $repository;

        return $repository === null
            ? resolve(SettingsMapper::class)
            : resolve(SettingsMapper::class)->repository($repository);
    }
}
