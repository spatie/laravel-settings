<?php

namespace Spatie\LaravelSettings;

use Exception;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionProperty;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\LaravelSettings\Events\LoadedSettings;
use Spatie\LaravelSettings\Events\SavedSettings;
use Spatie\LaravelSettings\Events\SavingSettings;
use Spatie\LaravelSettings\Exceptions\CouldNotCastSetting;
use Spatie\LaravelSettings\Exceptions\MissingSettingsException;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;

class SettingsMapper
{
    private SettingsRepository $repository;

    private SettingsConfig $config;

    public function __construct(
        SettingsRepository $repository,
        SettingsConfig $config
    ) {
        $this->repository = $repository;
        $this->config = $config;
    }

    public function save(Settings $settings): Settings
    {
        event(new SavingSettings($settings));

        $properties = $this->repository->getPropertiesInGroup($settings::group());

        $settingsClass = get_class($settings);

        $this->ensureNoMissingSettings($settingsClass, $properties, 'saving');

        $this->getWritableProperties($settings)->each(function ($payload, string $name) use ($settings) {
            $this->repository->updatePropertyPayload(
                $settings::group(),
                $name,
                $this->castToRepository($payload, $name, get_class($settings))
            );
        });

        $settings->fill($this->getSettings($settingsClass));

        event(new SavedSettings($settings));

        return $settings;
    }

    public function load(string $settingsClass): Settings
    {
        if (! is_subclass_of($settingsClass, Settings::class)) {
            throw new Exception("Tried loading {$settingsClass} which is not a Settings DTO");
        }

        $settings = new $settingsClass($this->getSettings($settingsClass));

        event(new LoadedSettings($settings));

        return $settings;
    }

    /**
     * @param string|\Spatie\LaravelSettings\Settings $settingsClass
     *
     * @return array
     */
    public function getSettings(string $settingsClass): array
    {
        $properties = $this->repository->getPropertiesInGroup($settingsClass::group());

        $this->ensureNoMissingSettings($settingsClass, $properties, 'loading');

        return collect($properties)->map(
            fn ($value, string $property) => $this->castFromRepository($value, $property, $settingsClass),
        )->toArray();
    }

    private function ensureNoMissingSettings(string $settingsClass, array $properties, string $operation): void
    {
        $reflection = new ReflectionClass($settingsClass);

        $requiredProperties = array_map(
            fn (ReflectionProperty $property) => $property->getName(),
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC)
        );

        $missingSettings = array_diff(
            $requiredProperties,
            array_keys($properties)
        );

        if (! empty($missingSettings)) {
            throw MissingSettingsException::create($settingsClass, $missingSettings, $operation);
        }
    }

    private function getWritableProperties(Settings $settings): Collection
    {
        $lockedProperties = $this->repository->getLockedProperties($settings::group());

        return collect($settings->all())->filter(
            fn ($value, string $property) => ! in_array($property, $lockedProperties)
        );
    }

    private function castFromRepository($payload, string $property, string $settingsClass)
    {
        $reflection = new ReflectionProperty($settingsClass, $property);

        if ($this->skipCastingFromRepository($property, $reflection)) {
            return $payload;
        }

        foreach ($this->config->getCasts() as $type => $cast) {
            /** @var \Spatie\LaravelSettings\SettingsCasts\SettingsCast $cast */
            if ($reflection->getType()->getName() === $type) {
                return $cast->get($payload);
            }
        }

        throw CouldNotCastSetting::fromRepository($settingsClass, $property, $reflection);
    }

    private function castToRepository($payload, string $property, string $settingsClass)
    {
        if ($this->skipCasting($payload)) {
            return $payload;
        }

        $reflection = new ReflectionProperty($settingsClass, $property);

        foreach ($this->config->getCasts() as $type => $cast) {
            /** @var \Spatie\LaravelSettings\SettingsCasts\SettingsCast $cast */
            if ($reflection->getType()->getName() === $type) {
                return $cast->set($payload);
            }
        }

        throw CouldNotCastSetting::toRepository($settingsClass, $property, $reflection);
    }

    private function skipCasting($payload): bool
    {
        return is_array($payload)
            || is_scalar($payload)
            || is_null($payload)
            || is_subclass_of($payload, DataTransferObject::class);
    }

    private function skipCastingFromRepository(
        $payload,
        ReflectionProperty $reflection
    ): bool {
        return $reflection->getType()->isBuiltin()
            || $payload === null
            || is_subclass_of($reflection->getType()->getName(), DataTransferObject::class);
    }
}
