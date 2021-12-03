<?php

namespace Spatie\LaravelSettings\Support;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use ReflectionNamedType;
use ReflectionProperty;
use Spatie\LaravelSettings\Exceptions\CouldNotResolveDocblockType;

class PropertyReflector
{
    public static function resolveType(
        ReflectionProperty $reflectionProperty
    ): ?Type {
        $reflectionType = $reflectionProperty->getType();
        $docblock = $reflectionProperty->getDocComment();

        if ($reflectionType === null && empty($docblock)) {
            return null;
        }

        if ($docblock) {
            preg_match('/@var ((?:(?:[\w?|\\\\<>,\s])+(?:\[])?)+)/', $docblock, $output_array);

            return count($output_array) === 2
                ? self::reflectDocblock($reflectionProperty, $output_array[1])
                : null;
        }

        if (! $reflectionType instanceof ReflectionNamedType) {
            return null;
        }

        $builtInTypes = [
            'int',
            'string',
            'float',
            'bool',
            'mixed',
            'array',
        ];

        if (in_array($reflectionType->getName(), $builtInTypes)) {
            return null;
        }

        $type = new Object_(new Fqsen('\\' . $reflectionType->getName()));

        return $reflectionType->allowsNull()
            ? new Nullable($type)
            : $type;
    }

    protected static function reflectDocblock(
        ReflectionProperty $reflectionProperty,
        string $type
    ): Type {
        $resolvedType = (new TypeResolver())->resolve($type);

        $isValidPrimitive = $resolvedType instanceof Boolean
            || $resolvedType instanceof Float_
            || $resolvedType instanceof Integer
            || $resolvedType instanceof String_;

        if ($isValidPrimitive) {
            return $resolvedType;
        }

        if ($resolvedType instanceof Object_) {
            return self::reflectObject($reflectionProperty, $resolvedType);
        }

        if ($resolvedType instanceof Compound) {
            return self::reflectCompound($reflectionProperty, $resolvedType);
        }

        if ($resolvedType instanceof Nullable) {
            return new Nullable(self::reflectDocblock($reflectionProperty, (string) $resolvedType->getActualType()));
        }

        if ($resolvedType instanceof AbstractList) {
            $listType = get_class($resolvedType);

            return new $listType(
                self::reflectDocblock($reflectionProperty, (string) $resolvedType->getValueType()),
                $resolvedType->getKeyType()
            );
        }

        throw CouldNotResolveDocblockType::create($type, $reflectionProperty);
    }

    private static function reflectCompound(
        ReflectionProperty $reflectionProperty,
        Compound $compound
    ): Nullable {
        if ($compound->getIterator()->count() !== 2 || ! $compound->contains(new Null_())) {
            throw CouldNotResolveDocblockType::create((string) $compound, $reflectionProperty);
        }

        $other = current(array_filter(
            iterator_to_array($compound->getIterator()),
            fn (Type $type) => ! $type instanceof Null_
        ));

        return new Nullable(self::reflectDocblock($reflectionProperty, (string) $other));
    }

    private static function reflectObject(
        ReflectionProperty $reflectionProperty,
        Object_ $object
    ): Object_ {
        if (class_exists((string) $object->getFqsen())) {
            return $object;
        }

        $context = (new ContextFactory)->createFromReflector($reflectionProperty);

        $className = ltrim((string) $object->getFqsen(), '\\');

        $fqsen = (new FqsenResolver)->resolve($className, $context);

        return new Object_($fqsen);
    }
}
