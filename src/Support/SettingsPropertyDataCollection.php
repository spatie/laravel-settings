<?php

namespace Spatie\LaravelSettings\Support;

use ReflectionProperty;
use Spatie\DataTransferObject\DataTransferObjectCollection;
use Spatie\LaravelSettings\Factories\SettingsCastFactory;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;

class SettingsPropertyDataCollection extends DataTransferObjectCollection
{
    /**
     * @param string|\Spatie\LaravelSettings\Settings $settingsClass
     * @param array $reflectionProperties
     * @param \Spatie\LaravelSettings\SettingsRepositories\SettingsRepository $repository
     *
     * @return \Spatie\LaravelSettings\Support\SettingsPropertyDataCollection
     */
    public static function resolve(
        $settingsClass,
        array $reflectionProperties,
        SettingsRepository $repository
    ): self {
        $properties = $repository->getPropertiesInGroup($settingsClass::group());
        $lockedProperties = $repository->getLockedProperties($settingsClass::group());

        $reflectionProperties = array_filter(
            $reflectionProperties,
            fn (ReflectionProperty $reflectionProperty) => array_key_exists($reflectionProperty->name, $properties)
        );

        $collection = array_map(fn (ReflectionProperty $reflectionProperty) => new SettingsPropertyData(
            $name = $reflectionProperty->name,
            self::resolvePayload($name, $properties),
            self::resolveCast($reflectionProperty, $settingsClass::casts()),
            self::resolveIsLocked($name, $lockedProperties),
            self::resolveIsNullable($reflectionProperty),
            self::resolveShouldBeEncrypted($name, $settingsClass::encrypted())
        ), $reflectionProperties);

        return new self($collection);
    }

    protected static function resolvePayload(string $name, array $properties)
    {
        return $properties[$name] ?? null;
    }

    protected static function resolveCast(ReflectionProperty $reflectionProperty, array $localCasts): ?SettingsCast
    {
        return SettingsCastFactory::resolve($reflectionProperty, $localCasts);
    }

    protected static function resolveIsLocked(string $name, array $lockedProperties): bool
    {
        return in_array($name, $lockedProperties);
    }

    protected static function resolveIsNullable(ReflectionProperty $reflectionProperty): bool
    {
        return $reflectionProperty->getType()->allowsNull();
    }

    protected static function resolveShouldBeEncrypted(string $name, array $encryptedProperties): bool
    {
        return in_array($name, $encryptedProperties);
    }

    public function current(): SettingsPropertyData
    {
        return parent::current();
    }
}
