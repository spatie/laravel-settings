<?php

namespace App\Support\Settings\Exceptions;

use Exception;

class InvalidSplittingConfig extends Exception
{
    public static function create(int $properties, int $closures): self
    {
        return new self("Could not split because there were {$properties} properties defined to split, using {$closures} closures");
    }
}
