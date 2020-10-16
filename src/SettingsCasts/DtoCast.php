<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use Exception;
use Spatie\DataTransferObject\DataTransferObject;

class DtoCast implements SettingsCast
{
    private string $type;

    public function __construct(?string $type)
    {
        $this->type = $this->ensureDtoTypeExists($type);
    }

    public function get($payload): DataTransferObject
    {
        return new $this->type($payload);
    }

    /**
     * @param \Spatie\DataTransferObject\DataTransferObject $payload
     *
     * @return array
     */
    public function set($payload): array
    {
        return $payload->toArray();
    }

    private function ensureDtoTypeExists(?string $type): string
    {
        if ($type === null) {
            throw new Exception('Cannot create a DTO cast because no DTO class was given');
        }

        if (! class_exists($type)) {
            throw new Exception("Cannot create a DTO cast for `{$type}` because the DTO does not exist");
        }

        return $type;
    }
}
