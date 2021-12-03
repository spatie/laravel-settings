<?php

namespace Spatie\LaravelSettings\Factories;

use Illuminate\Support\Str;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use ReflectionProperty;
use Spatie\LaravelSettings\SettingsCasts\ArraySettingsCast;
use Spatie\LaravelSettings\SettingsCasts\EnumCast;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;
use Spatie\LaravelSettings\Support\PropertyReflector;

class SettingsCastFactory
{
    public static function resolve(
        ReflectionProperty $reflectionProperty,
        array $localCasts
    ): ?SettingsCast {
        $name = $reflectionProperty->getName();

        $reflectedType = PropertyReflector::resolveType($reflectionProperty);

        if (array_key_exists($name, $localCasts)) {
            return self::createLocalCast($localCasts[$name], $reflectedType);
        }

        if ($reflectedType === null) {
            return null;
        }

        return self::createDefaultCast($reflectedType);
    }

    /**
     * @param string|SettingsCast $castDefinition
     * @param \phpDocumentor\Reflection\Type|null $type
     *
     * @return \Spatie\LaravelSettings\SettingsCasts\SettingsCast
     */
    protected static function createLocalCast(
        $castDefinition,
        ?Type $type
    ): SettingsCast {
        if ($castDefinition instanceof SettingsCast) {
            return $castDefinition;
        }

        $castClass = Str::before($castDefinition, ':');

        $arguments = Str::contains($castDefinition, ':')
            ? explode(',', Str::after($castDefinition, ':'))
            : [];

        $reflectedType = self::getLocalCastReflectedType($type);

        if ($reflectedType) {
            array_push($arguments, $reflectedType);
        }

        return new $castClass(...$arguments);
    }

    protected static function createDefaultCast(
        Type $type
    ): ?SettingsCast {
        $noCastRequired = self::isTypeWithNoCastRequired($type)
            || ($type instanceof AbstractList && self::isTypeWithNoCastRequired($type->getValueType()))
            || ($type instanceof Nullable && self::isTypeWithNoCastRequired($type->getActualType()));

        if ($noCastRequired) {
            return null;
        }

        if ($type instanceof AbstractList) {
            return new ArraySettingsCast(self::createDefaultCast($type->getValueType()));
        }

        if ($type instanceof Nullable) {
            return self::createDefaultCast($type->getActualType());
        }

        if (! $type instanceof Object_) {
            return null;
        }

        $className = self::getObjectClassName($type);

        if (enum_exists($className)) {
            return new EnumCast($className);
        }

        foreach (config('settings.global_casts', []) as $base => $cast) {
            if (self::shouldCast($className, $base)) {
                return new $cast($className);
            }
        }

        return null;
    }

    protected static function isTypeWithNoCastRequired(Type $type): bool
    {
        return $type instanceof Integer
            || $type instanceof Boolean
            || $type instanceof Float_
            || $type instanceof String_;
    }

    protected static function shouldCast(string $type, string $base): bool
    {
        return $type === $base
            || in_array($type, class_implements($base))
            || is_subclass_of($type, $base);
    }

    protected static function getLocalCastReflectedType(?Type $type): ?string
    {
        if ($type instanceof Object_) {
            return self::getObjectClassName($type);
        }

        if ($type instanceof Nullable) {
            return self::getLocalCastReflectedType($type->getActualType());
        }

        return null;
    }

    protected static function getObjectClassName(Object_ $type): string
    {
        return ltrim((string ) $type->getFqsen(), '\\');
    }
}
