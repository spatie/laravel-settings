<?php

namespace App\Support\Settings;

use App\Support\Settings\Exceptions\MissingSettingsException;
use App\Support\Settings\SettingsConnection\SettingsConnection;
use Exception;
use ReflectionClass;
use ReflectionProperty;

class SettingsMapper
{
    private SettingsConnection $connection;

    public function __construct(SettingsConnection $settingsConnection)
    {
        $this->connection = $settingsConnection;
    }

    public function connection(string $name): self
    {
        $this->connection = SettingsConnectionFactory::create($name);

        return $this;
    }

    public function save(Settings $settings): Settings
    {
        $properties = $this->connection->getPropertiesInGroup($settings::group());

        $missingSettings = array_diff(
            $this->getRequiredSettings($settings),
            array_keys($properties)
        );

        if (! empty($missingSettings)) {
            throw MissingSettingsException::whenSaving($settings::group(), $missingSettings);
        }

        foreach ($settings->all() as $name => $payload) {
            $this->connection->updatePropertyPayload(
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

        $properties = $this->connection->getPropertiesInGroup($settingsClass::group());

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
     * @param string|\App\Support\Settings\Settings $settingsClass
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
