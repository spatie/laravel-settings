<?php

namespace Spatie\LaravelSettings;

use Exception;
use ReflectionClass;
use ReflectionProperty;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\LaravelSettings\Exceptions\CouldNotCastSetting;
use Spatie\LaravelSettings\Exceptions\MissingSettingsException;
use Spatie\LaravelSettings\SettingsRepository\SettingsRepository;

class SettingsMapper
{
    private SettingsRepository $repository;

    private SettingsConfig $config;

    public function __construct(
        SettingsRepository $settingsConnection,
        SettingsConfig $config
    ) {
        $this->repository = $settingsConnection;
        $this->config = $config;
    }

    public function repository(string $name): self
    {
        $this->repository = SettingsRepositoryFactory::create($name);

        return $this;
    }

    public function save(Settings $settings): Settings
    {
        $properties = $this->repository->getPropertiesInGroup($settings::group());

        $missingSettings = array_diff(
            $this->getRequiredSettings($settings),
            array_keys($properties)
        );

        if (! empty($missingSettings)) {
            throw MissingSettingsException::whenSaving($settings::group(), $missingSettings);
        }

        foreach ($this->resolveSaveableProperties($settings) as $name => $payload) {
            $this->repository->updatePropertyPayload(
                $settings::group(),
                $name,
                $this->castToRepository($payload, $name, get_class($settings))
            );
        }

        return $settings->fill($this->getSettings(get_class($settings)));
    }

    public function load(string $settingsClass): Settings
    {
        if (! is_subclass_of($settingsClass, Settings::class)) {
            throw new Exception("Tried loading {$settingsClass} which is not a Settings DTO");
        }

        return new $settingsClass($this->getSettings($settingsClass));
    }

    /**
     * @param string|\Spatie\LaravelSettings\Settings $settingsClass
     *
     * @return array
     */
    private function getSettings(string $settingsClass): array
    {
        $properties = $this->repository->getPropertiesInGroup($settingsClass::group());

        $missingProperties = array_diff(
            $this->getRequiredSettings($settingsClass),
            array_keys($properties)
        );

        if (! empty($missingProperties)) {
            throw MissingSettingsException::whenLoading($settingsClass::group(), $missingProperties);
        }

        return collect($properties)->map(
            fn ($value, string $property) => $this->castFromRepository($value, $property, $settingsClass),
        )->toArray();
    }

    /**
     * @param string|\Spatie\LaravelSettings\Settings $settingsClass
     *
     * @return array
     * @throws \ReflectionException
     */
    private function getRequiredSettings($settingsClass): array
    {
        $reflection = new ReflectionClass($settingsClass);

        return array_map(
            fn (ReflectionProperty $property) => $property->getName(),
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC)
        );
    }

    private function resolveSaveableProperties(Settings $settings): array
    {
        $lockedProperties = $this->repository->getLockedProperties($settings::group());

        return array_filter(
            $settings->all(),
            fn (string $property) => ! in_array($property, $lockedProperties),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function castFromRepository($payload, string $property, string $settingsClass)
    {
        $reflection = new ReflectionProperty($settingsClass, $property);

        if ($this->skipCastingFromRepository($property, $reflection)) {
            return $payload;
        }

        foreach ($this->config->getCasts() as $type => $cast) {
            /** @var \Spatie\LaravelSettings\SettingCasts\SettingsCast $cast */
            if ($reflection->getType()->getName() === $type) {
                return $cast->get($payload);
            }
        }

        throw CouldNotCastSetting::fromRepository($settingsClass, $property, $reflection);
    }

    private function castToRepository($payload, string $property, string $settingsClass)
    {
        if ($this->skipCastingToRepository($payload)) {
            return $payload;
        }

        $reflection = new ReflectionProperty($settingsClass, $property);

        foreach ($this->config->getCasts() as $type => $cast) {
            /** @var \Spatie\LaravelSettings\SettingCasts\SettingsCast $cast */
            if ($reflection->getType()->getName() === $type) {
                return $cast->set($payload);
            }
        }

        throw CouldNotCastSetting::toRepository($settingsClass, $property, $reflection);
    }

    private function skipCastingToRepository($payload): bool
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
