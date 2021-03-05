<?php

namespace Spatie\LaravelSettings;

use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use ReflectionProperty;
use Serializable;
use Spatie\LaravelSettings\Events\SavingSettings;
use Spatie\LaravelSettings\Events\SettingsLoaded;
use Spatie\LaravelSettings\Events\SettingsSaved;

abstract class Settings implements Arrayable, Jsonable, Responsable, Serializable
{
    private SettingsMapper $mapper;

    private SettingsConfig $config;

    private bool $loaded = false;

    private bool $configInitialized = false;

    abstract public static function group(): string;

    public static function repository(): ?string
    {
        return null;
    }

    public static function casts(): array
    {
        return [];
    }

    public static function encrypted(): array
    {
        return [];
    }

    /**
     * @param array $values
     *
     * @return static
     */
    public static function fake(array $values): self
    {
        $settingsMapper = app(SettingsMapper::class);

        $propertiesToLoad = $settingsMapper->initialize(static::class)
            ->getReflectedProperties()
            ->keys()
            ->reject(fn (string $name) => array_key_exists($name, $values));

        $mergedValues = $settingsMapper
            ->fetchProperties(static::class, $propertiesToLoad)
            ->merge($values)
            ->toArray();

        return app(Container::class)->instance(static::class, new static(
            $settingsMapper,
            $mergedValues
        ));
    }

    public function __construct(SettingsMapper $mapper, array $values = [])
    {
        $this->loadConfig($mapper);

        foreach ($this->config->getReflectedProperties()->keys() as $name) {
            unset($this->{$name});
        }

        if (! empty($values)) {
            $this->loadValues($values);
        }
    }

    public function __get($name)
    {
        $this->loadValues();

        return $this->{$name};
    }

    public function __set($name, $value)
    {
        $this->loadValues();

        $this->{$name} = $value;
    }

    public function __debugInfo()
    {
        $this->loadValues();
    }

    /**
     * @param \Illuminate\Support\Collection|array $properties
     *
     * @return $this
     */
    public function fill($properties): self
    {
        foreach ($properties as $name => $payload) {
            $this->{$name} = $payload;
        }

        return $this;
    }

    public function save(): self
    {
        $properties = $this->toCollection();

        event(new SavingSettings($properties, $this));

        $this->fill($this->mapper->save(static::class, $properties));

        event(new SettingsSaved($this));

        return $this;
    }

    public function lock(string ...$properties)
    {
        $this->loadConfig();

        $this->config->lock(...$properties);
    }

    public function unlock(string ...$properties)
    {
        $this->loadConfig();

        $this->config->unlock(...$properties);
    }

    public function getLockedProperties(): array
    {
        $this->loadConfig();

        return $this->config->getLocked()->toArray();
    }

    public function toCollection(): Collection
    {
        $this->loadConfig();

        return $this->config
            ->getReflectedProperties()
            ->mapWithKeys(fn (ReflectionProperty $property) => [
                $property->getName() => $this->{$property->getName()},
            ]);
    }

    public function toArray(): array
    {
        return $this->toCollection()->toArray();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function toResponse($request)
    {
        return response()->json($this->toJson());
    }

    public function serialize(): string
    {
        return serialize($this->toArray());
    }

    public function unserialize($serialized): void
    {
        $properties = unserialize($serialized);

        $this->fill($properties);
        $this->loaded = true;
    }

    private function loadValues(?array $values = null): self
    {
        if ($this->loaded) {
            return $this;
        }

        $values ??= $this->mapper->load(static::class);

        $this->loaded = true;
        $this->fill($values);

        event(new SettingsLoaded($this));

        return $this;
    }

    private function loadConfig(?SettingsMapper $mapper = null): self
    {
        if ($this->configInitialized) {
            return $this;
        }

        $this->mapper = $mapper ?? app(SettingsMapper::class);
        $this->config = $this->mapper->initialize(static::class);
        $this->configInitialized = true;

        return $this;
    }
}
