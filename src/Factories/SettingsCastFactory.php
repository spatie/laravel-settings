<?php

namespace Spatie\LaravelSettings\Factories;

use Exception;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Object_;
use PhpParser\Node\Scalar\String_;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\LaravelSettings\SettingsCasts\ArraySettingsCast;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;
use Spatie\LaravelSettings\Support\PropertyReflector;
use Spatie\LaravelSettings\Support\ReflectedSettingsPropertyType;

class SettingsCastFactory
{
    public static function resolve(
        ReflectionProperty $reflectionProperty,
        array $localCasts
    ): ?SettingsCast {
        $name = $reflectionProperty->getName();

        if (array_key_exists($name, $localCasts)) {
            return self::createLocalCast($localCasts[$name]);
        }

        $reflectedType = PropertyReflector::resolveType($reflectionProperty);

        if ($reflectedType === null) {
            return null;
        }

        return self::createDefaultCast($reflectedType);
    }

    /**
     * @param string|SettingsCast $cast
     *
     * @return \Spatie\LaravelSettings\SettingsCasts\SettingsCast|null
     */
    private static function createLocalCast(
        $cast
    ): SettingsCast {
        if ($cast instanceof SettingsCast) {
            return $cast;
        }

        $castClass = Str::before($cast, ':');
        $arguments = explode(',', Str::after($cast, ':'));

        return new $castClass(...$arguments);
    }

    private static function createDefaultCast(
        Type $type
    ): ?SettingsCast {
        if ($type instanceof AbstractList) {
            return new ArraySettingsCast(self::createDefaultCast($type->getValueType()));
        }

        if (! $type instanceof Object_) {
            return null;
        }

        $type = ltrim((string ) $type->getFqsen(), '\\');

        foreach (config('settings.default_casts') as $base => $cast) {
            if (self::shouldCast($type, $base)) {
                return new $cast($type);
            }
        }

        return null;
    }

    private static function shouldCast(string $type, string $base): bool
    {
        return $type === $base
            || in_array($type, class_implements($base))
            || is_subclass_of($type, $base);
    }

}
