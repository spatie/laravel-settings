<?php

namespace Spatie\LaravelSettings;

use Exception;
use ReflectionClass;
use ReflectionProperty;
use Spatie\LaravelSettings\Events\LoadingSettings;
use Spatie\LaravelSettings\Events\SavingSettings;
use Spatie\LaravelSettings\Events\SettingsLoaded;
use Spatie\LaravelSettings\Events\SettingsSaved;
use Spatie\LaravelSettings\Exceptions\MissingSettings;
use Spatie\LaravelSettings\Factories\SettingsRepositoryFactory;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;
use Spatie\LaravelSettings\Support\SettingsPropertyData;
use Spatie\LaravelSettings\Support\SettingsPropertyDataCollection;

class SettingsMapper
{
    /** @var string|class-string */
    protected string $settingsClass;

    /** @var array|\ReflectionProperty[] */
    protected array $reflectionProperties;

    protected SettingsRepository $repository;

    protected SettingsPropertyDataCollection $properties;

    public static function create(string $settingsClass)
    {
        return new self($settingsClass);
    }

    public function __construct(string $settingsClass)
    {
        if (! is_subclass_of($settingsClass, Settings::class)) {
            throw new Exception("Tried decorating {$settingsClass} which is not extending `Spatie\LaravelSettings\Settings::class`");
        }

        $this->settingsClass = $settingsClass;
        $this->reflectionProperties = (new ReflectionClass($settingsClass))->getProperties(ReflectionProperty::IS_PUBLIC);
        $this->repository = SettingsRepositoryFactory::create($settingsClass::repository());

        $this->properties = SettingsPropertyDataCollection::resolve(
            $this->settingsClass,
            $this->reflectionProperties,
            $this->repository
        );
    }

    public function load(): Settings
    {
        /** @var \Spatie\LaravelSettings\Settings $settings */
        $settings = new $this->settingsClass;

        $this->ensureNoMissingSettings('loading');

        event(new LoadingSettings($this->settingsClass, $this->properties));

        foreach ($this->properties as $property) {
            $settings->{$property->getName()} = $property->getValue();
        }

        event(new SettingsLoaded($settings));

        return $settings;
    }

    public function save(Settings $settings): Settings
    {
        $this->ensureNoMissingSettings('saving');

        event(new SavingSettings($this->settingsClass, $this->properties, $settings));

        foreach ($this->properties as $property) {
            if ($property->isLocked()) {
                $settings->{$property->getName()} = $property->getValue();

                continue;
            }

            $property->setValue($settings->{$property->getName()});

            $this->repository->updatePropertyPayload(
                $this->settingsClass::group(),
                $property->getName(),
                $property->getRawPayload()
            );
        }

        event(new SettingsSaved($settings));

        return $settings;
    }

    protected function ensureNoMissingSettings(
        string $operation
    ): void {
        $requiredProperties = array_map(
            fn (ReflectionProperty $property) => $property->getName(),
            $this->reflectionProperties
        );

        $availableProperties = array_map(
            fn (SettingsPropertyData $property) => $property->getName(),
            $this->properties->toArray()
        );

        $missingSettings = array_diff($requiredProperties, $availableProperties);

        if (! empty($missingSettings)) {
            throw MissingSettings::create($this->settingsClass, $missingSettings, $operation);
        }
    }
}
