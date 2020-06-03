<?php

namespace Spatie\LaravelSettings;

use Spatie\LaravelSettings\Exceptions\MissingSettingsException;
use Spatie\LaravelSettings\SettingsRepository\SettingsRepository;
use Exception;
use ReflectionClass;
use ReflectionProperty;

class SettingsMapper
{
    private SettingsRepository $repository;

    public function __construct(SettingsRepository $settingsConnection)
    {
        $this->repository = $settingsConnection;
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

        foreach ($settings->all() as $name => $payload) {
            $this->repository->updatePropertyPayload(
                $settings::group(),
                $name,
                $payload
            );
        }

        return $settings;
    }

    public function load(string $settingsClass): Settings
    {
        if (! is_subclass_of($settingsClass, Settings::class)) {
            throw new Exception("Tried loading {$settingsClass} which is not a Settings DTO");
        }

        $properties = $this->repository->getPropertiesInGroup($settingsClass::group());

        $missingProperties = array_diff(
            $this->getRequiredSettings($settingsClass),
            array_keys($properties)
        );

        if (! empty($missingProperties)) {
            throw MissingSettingsException::whenLoading($settingsClass::group(), $missingProperties);
        }

        return new $settingsClass($properties);
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
}
