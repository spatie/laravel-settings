<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use Exception;
use Spatie\LaravelData\Data;

class DataCast implements SettingsCast
{
    protected string $type;

    public function __construct(?string $type)
    {
        $this->type = $this->ensureDataTypeExists($type);
    }

    public function get($payload): Data
    {
        return $this->type::from($payload);
    }

    /**
     * @param Data $payload
     *
     * @return array
     */
    public function set($payload): array
    {
        return $payload->toArray();
    }

    protected function ensureDataTypeExists(?string $type): string
    {
        if ($type === null) {
            throw new Exception('Cannot create a data cast because no data class was given');
        }

        if (! class_exists($type)) {
            throw new Exception("Cannot create a data cast for `{$type}` because the data does not exist");
        }

        return $type;
    }
}
