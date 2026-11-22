<?php

namespace Spatie\LaravelSettings;

use Illuminate\Support\Collection;
use ReflectionProperty;
use Spatie\LaravelSettings\Events\LoadingSettings;
use Spatie\LaravelSettings\Exceptions\MissingSettings;
use Spatie\LaravelSettings\Support\Crypto;

class SettingsMapper
{
    /** @var array<string, \Spatie\LaravelSettings\SettingsConfig> */
    private array $configs = [];

    public function initialize(string $settingsClass): SettingsConfig
    {
        if ($this->has($settingsClass)) {
            return $this->configs[$settingsClass];
        }

        $config = new SettingsConfig($settingsClass);

        return $this->configs[$settingsClass] = $config;
    }

    public function has(string $settingsClass): bool
    {
        return array_key_exists($settingsClass, $this->configs);
    }

    public function load(string $settingsClass): Collection
    {
        $config = $this->getConfig($settingsClass);

        $properties = $this->fetchProperties(
            $settingsClass,
            $config->getReflectedProperties()->keys()
        );

        event(new LoadingSettings($settingsClass, $properties));

        $properties = $this->fillMissingSettingsWithDefaultValues($config, $properties);

        $this->ensureNoMissingSettings($config, $properties, 'loading');

        return $properties;
    }

    public function save(
        string     $settingsClass,
        Collection $properties
    ): Collection {
        $config = $this->getConfig($settingsClass);

        $this->ensureNoMissingSettings($config, $properties, 'saving');

        $notRejectedProperties = $properties
            ->reject(fn ($payload, string $name) => $config->isLocked($name));

        $changedProperties = $this->serializeProperties($settingsClass, $notRejectedProperties);

        $config->getRepository()->updatePropertiesPayload(
            $config->getGroup(),
            $changedProperties
        );

        return $this
            ->fetchProperties($settingsClass, $config->getLocked())
            ->merge($notRejectedProperties);
    }

    public function serializeProperties(string $settingsClass, Collection $properties): array
    {
        $config = $this->getConfig($settingsClass);

        return $properties
            ->map(function ($payload, string $name) use ($config) {
                if ($cast = $config->getCast($name)) {
                    $payload = $cast->set($payload);
                }

                if ($config->isEncrypted($name)) {
                    $payload = Crypto::encrypt($payload);
                }

                return $payload;
            })
            ->toArray();
    }

    public function fetchProperties(string $settingsClass, Collection $names): Collection
    {
        $config = $this->getConfig($settingsClass);

        return $this->mapProperties(
            $settingsClass,
            $config->getRepository()->getPropertiesInGroup($config->getGroup()),
            $names
        );
    }

    public function loadCachedProperties(string $settingsClass, array $properties): ?Collection
    {
        $config = $this->getConfig($settingsClass);
        $mappedProperties = $this->fillMissingSettingsWithDefaultValues(
            $config,
            $this->mapProperties($settingsClass, $properties)
        );

        if ($config->getReflectedProperties()->keys()->diff($mappedProperties->keys())->isNotEmpty()) {
            return null;
        }

        return $mappedProperties;
    }

    private function mapProperties(
        string $settingsClass,
        array $properties,
        ?Collection $names = null
    ): Collection {
        $config = $this->getConfig($settingsClass);

        return collect($properties)
            ->filter(fn ($payload, string $name) => $config->getReflectedProperties()->has($name)
                && ($names === null || $names->contains($name)))
            ->map(function ($payload, string $name) use ($config) {
                if ($config->isEncrypted($name)) {
                    $payload = Crypto::decrypt($payload);
                }

                if ($cast = $config->getCast($name)) {
                    $payload = $cast->get($payload);
                }

                return $payload;
            });
    }

    private function getConfig(string $settingsClass): SettingsConfig
    {
        if (! $this->has($settingsClass)) {
            $this->initialize($settingsClass);
        }

        return $this->configs[$settingsClass];
    }

    private function fillMissingSettingsWithDefaultValues(SettingsConfig $config, Collection $properties): Collection
    {
        $config->resetDefaultValueLoadedProperties();

        $config
            ->getReflectedProperties()
            ->keys()
            ->diff($properties->keys())
            ->each(function (string $missingSetting) use ($config, &$properties) {
                /** @var ReflectionProperty $reflectionProperty */
                $reflectionProperty = $config->getReflectedProperties()[$missingSetting];

                if ($reflectionProperty->hasDefaultValue()) {
                    $config->markPropertyAsDefaultValueLoaded($missingSetting);

                    $properties->put($missingSetting, $reflectionProperty->getDefaultValue());
                }
            });

        return $properties;
    }

    private function ensureNoMissingSettings(
        SettingsConfig $config,
        Collection     $properties,
        string         $operation
    ): void {
        $missingSettings = $config
            ->getReflectedProperties()
            ->keys()
            ->diff($properties->keys())
            ->when($operation === 'saving', fn (Collection $collection) => $collection->concat($config->getDefaultValueLoadedProperties()))
            ->toArray();

        if (! empty($missingSettings)) {
            throw MissingSettings::create($config->getName(), $missingSettings, $operation);
        }
    }
}
