<?php

namespace Spatie\LaravelSettings\Exceptions;

use Exception;
use ReflectionProperty;

class CouldNotResolveDocblockType extends Exception
{
    public static function create(
        string $type,
        ReflectionProperty $reflectionProperty
    ): self {
        return new self("Could not resolve type in docblock: `{$type}` of property `{$reflectionProperty->getDeclaringClass()->getName()}::{$reflectionProperty->getName()}`");
    }
}
