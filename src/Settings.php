<?php

namespace Spatie\LaravelSettings;

use Spatie\LaravelSettings\Factories\SettingsRepositoryFactory;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;
use Spatie\LaravelSettings\Support\TempDto;

abstract class Settings extends TempDto
{
    protected array $casts = [];

    abstract public static function group(): string;

    public static function repository(): ?string
    {
        return null;
    }

    public function casts(): array
    {
        return [];
    }

    public static function getRepositoryName()
    {
        return self::repository() ?? config('settings.default_repository');
    }

    public function fill(array $properties): self
    {
        $newProperties = array_merge(
            $this->resolveMapper()->getSettings(static::class),
            $properties
        );

        $this->replaceProperties($newProperties);

        return $this;
    }

    public function save(): self
    {
        $this->resolveMapper()->save($this);

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

    public function getCasts(): array
    {
        return array_merge($this->casts, $this->casts());
    }

    public static function fake(array $values): self
    {
        $realProperties = app(SettingsRepository::class)->getPropertiesInGroup(static::group());

        return app()->instance(static::class, new static(
            array_merge($realProperties, $values)
        ));
    }

    private function resolveRepository(): SettingsRepository
    {
        return SettingsRepositoryFactory::create(self::getRepositoryName());
    }

    private function resolveMapper(): SettingsMapper
    {
        return new SettingsMapper(
            $this->resolveRepository(),
            resolve(SettingsConfig::class)
        );
    }
}
