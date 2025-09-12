<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use Exception;
use Spatie\LaravelData\Data;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;

class ArrayDataCast implements SettingsCast
{
    protected string $type;

    protected bool $validate;

    public function __construct(?string $type, string $validate = 'true')
    {
        $this->type     = $this->ensureDataTypeExists($type);
        $this->validate = !in_array($validate, ['false', '0'], true);
    }

    /**
     * @param array<array> $payload
     *
     * @return Data[]
     */
    public function get($payload): array
    {
        return array_map(
            fn ($data) => $this->createData($data),
            $payload
        );
    }

    /**
     * @param array<array|Data> $payload
     *
     * @return array<array>
     */
    public function set($payload): array
    {
        return array_map(
            fn ($data) => $this->createData($data)->toArray(),
            $payload
        );
    }

    /**
     * @param array|Data $data
     */
    protected function createData($data): Data
    {
        return $this->validate ? $this->type::validateAndCreate($data) : $this->type::from($data);
    }

    protected function ensureDataTypeExists(?string $type): string
    {
        if ($type === null) {
            throw new Exception('Cannot create a data cast because no data class was given');
        }

        if (!class_exists($type)) {
            throw new Exception("Cannot create a data cast for `$type` because the data does not exist");
        }

        if (!class_implements($type, Data::class)) {
            throw new Exception("Cannot create a data cast for `$type` because the class does not implement data");
        }

        return $type;
    }
}
