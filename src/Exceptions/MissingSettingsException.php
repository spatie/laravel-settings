<?php

namespace App\Support\Settings\Exceptions;

use Exception;

class MissingSettingsException extends Exception
{
    public static function whenLoading(string $group, array $missingProperties): self
    {
        $missing = implode(', ', $missingProperties);

        return new self("Tried loading {$group} settings but following properties were missing: {$missing}");
    }

    public static function whenSaving(string $group, array $missingProperties): self
    {
        $missing = implode(', ', $missingProperties);

        return new self("Following settings: {$missing} are missing from {$group} so the settings could not be saved");
    }
}
