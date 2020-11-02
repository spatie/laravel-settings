<?php

namespace Spatie\LaravelSettings\Support;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
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

        if ($reflectionType->isBuiltin()) {
            return null;
        }

        if (! $reflectionType instanceof ReflectionNamedType) {
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

        if ($resolvedType instanceof Nullable) {
            return new Nullable(self::reflectDocblock(
                $reflectionProperty,
                (string) $resolvedType->getActualType()
            ));
        }

        if ($resolvedType instanceof Object_) {
            return $resolvedType;
        }

        if ($resolvedType instanceof AbstractList) {
            $isValid = $resolvedType->getValueType() instanceof Boolean
                || $resolvedType->getValueType() instanceof Array_
                || $resolvedType->getValueType() instanceof Float_
                || $resolvedType->getValueType() instanceof Integer
                || $resolvedType->getValueType() instanceof String_
                || $resolvedType->getValueType() instanceof Object_;

            if ($isValid) {
                return $resolvedType;
            }
        }

        throw CouldNotResolveDocblockType::create($type, $reflectionProperty);
    }
}
