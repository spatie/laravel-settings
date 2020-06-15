<?php

namespace Spatie\LaravelSettings;

use Spatie\DataTransferObject\DataTransferObject;
use Spatie\LaravelSettings\SettingsRepository\SettingsRepository;

abstract class Settings extends DataTransferObject
{
    abstract public static function group(): string;

    public function fill(array $properties): self
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }

        return $this;
    }

    public function save(?string $connection = null): void
    {
        $mapper = $connection === null
            ? resolve(SettingsMapper::class)
            : resolve(SettingsMapper::class)->repository($connection);

        $mapper->save($this);
    }

    public static function fake(array $values): self
    {
        $defaultProperties = app(SettingsRepository::class)->getPropertiesInGroup(static::group());

        return app()->instance(static::class, new static(
            array_merge($defaultProperties, $values)
        ));
    }
}
