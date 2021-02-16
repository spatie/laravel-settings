<?php

namespace Spatie\LaravelSettings;

use Illuminate\Support\Collection;
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

        return $this->configs[$settingsClass] = new SettingsConfig($settingsClass);
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

        $this->ensureNoMissingSettings($settingsClass, $properties, 'loading');

        return $properties;
    }

    public function save(
        string $settingsClass,
        Collection $properties
    ): Collection {
        $config = $this->getConfig($settingsClass);

        $this->ensureNoMissingSettings($settingsClass, $properties, 'saving');

        $lockedProperties = $this->fetchProperties(
            $settingsClass,
            $config->getReflectedProperties()->keys()
        )->filter(fn($payload, string $name) => $config->isLocked($name));

        $changedProperties = $properties
            ->reject(fn($payload, string $name) => $config->isLocked($name))
            ->each(function ($payload, string $name) use ($config) {
                if ($cast = $config->getCast($name)) {
                    $payload = $cast->set($payload);
                }

                if ($config->isEncrypted($name)) {
                    $payload = Crypto::encrypt($payload);
                }

                $config->getRepository()->updatePropertyPayload(
                    $config->getGroup(),
                    $name,
                    $payload
                );
            });

        return $lockedProperties->merge($changedProperties);
    }

    public function fetchProperties(string $settingsClass, Collection $names): Collection
    {
        $config = $this->getConfig($settingsClass);

        return collect($config->getRepository()->getPropertiesInGroup($config->getGroup()))
            ->filter(fn($payload, string $name) => $names->contains($name))
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

    private function ensureNoMissingSettings(
        string $settingsClass,
        Collection $properties,
        string $operation
    ): void {
        $missingSettings = $this->configs[$settingsClass]
            ->getReflectedProperties()
            ->keys()
            ->diff($properties->keys())
            ->toArray();

        if (! empty($missingSettings)) {
            throw MissingSettings::create($settingsClass, $missingSettings, $operation);
        }
    }
}
