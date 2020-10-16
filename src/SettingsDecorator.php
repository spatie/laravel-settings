<?php

namespace Spatie\LaravelSettings;

use Exception;
use ReflectionClass;
use ReflectionProperty;
use Spatie\LaravelSettings\Events\LoadedSettings;
use Spatie\LaravelSettings\Events\LoadingSettings;
use Spatie\LaravelSettings\Events\SavedSettings;
use Spatie\LaravelSettings\Events\SavingSettings;
use Spatie\LaravelSettings\Exceptions\MissingSettingsException;
use Spatie\LaravelSettings\Factories\SettingsCastFactory;
use Spatie\LaravelSettings\Factories\SettingsRepositoryFactory;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;
use Spatie\LaravelSettings\Support\SettingsPropertyData;

class SettingsDecorator
{
    private array $defaultCasts;

    /** @var string|class-string */
    private string $settingsClass;

    /** @var array|\ReflectionProperty[] */
    private array $reflectionProperties;

    private SettingsRepository $repository;

    /** @var array|\Spatie\LaravelSettings\Support\SettingsPropertyData[] */
    private array $properties;

    public static function create(string $settingsClass)
    {
        return new self($settingsClass);
    }

    public function __construct(string $settingsClass)
    {
        if (! is_subclass_of($settingsClass, Settings::class)) {
            throw new Exception("Tried decorating {$settingsClass} which is not extending `Spatie\LaravelSettings\Settings::class`");
        }

        $this->defaultCasts = config('settings.global_casts');
        $this->settingsClass = $settingsClass;
        $this->reflectionProperties = (new ReflectionClass($settingsClass))->getProperties(ReflectionProperty::IS_PUBLIC);
        $this->repository = SettingsRepositoryFactory::create($settingsClass::repository());
        $this->properties = $this->resolveProperties();
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

        event(new LoadedSettings($settings));

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
                $property->getPayload()
            );
        }

        event(new SavedSettings($settings));

        return $settings;
    }

    private function resolveProperties(): array
    {
        $properties = $this->repository->getPropertiesInGroup($this->settingsClass::group());
        $lockedProperties = $this->repository->getLockedProperties($this->settingsClass::group());

        return array_map(fn (ReflectionProperty $reflectionProperty) => new SettingsPropertyData(
            $reflectionProperty->name,
            $properties[$reflectionProperty->name] ?? null,
            SettingsCastFactory::resolve($reflectionProperty, $this->settingsClass::casts()),
            $this->isPropertyLocked($reflectionProperty, $lockedProperties),
            $reflectionProperty->getType()->allowsNull(),
            array_key_exists($reflectionProperty->name, $properties)
        ), $this->reflectionProperties);
    }

    private function isPropertyLocked(
        ReflectionProperty $reflectionProperty,
        array $lockedProperties
    ): bool {
        return in_array($reflectionProperty->name, $lockedProperties);
    }

    private function ensureNoMissingSettings(
        string $operation
    ): void {
        $requiredProperties = array_map(
            fn (ReflectionProperty $property) => $property->getName(),
            $this->reflectionProperties
        );

        $availableProperties = array_map(
            fn (SettingsPropertyData $property) => $property->getName(),
            array_filter(
                $this->properties,
                fn (SettingsPropertyData $property) => $property->isPresent(),
            )
        );

        $missingSettings = array_diff($requiredProperties, $availableProperties);

        if (! empty($missingSettings)) {
            throw MissingSettingsException::create($this->settingsClass, $missingSettings, $operation);
        }
    }
}
