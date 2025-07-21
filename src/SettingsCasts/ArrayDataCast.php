<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use Exception;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;

class ArrayDataCast implements SettingsCast
{
    protected string $type;

    public function __construct(?string $type)
    {
        $this->type = $this->ensureDataTypeExists($type);
    }

    /**
     * @param array<array> $payload
     *
     * @return BaseData[]
     */
    public function get($payload): array
    {
        return array_map(
            fn ($data) => $this->type::validateAndCreate($data),
            $payload
        );
    }

    /**
     * @param array<array|BaseData> $payload
     *
     * @return array<array>
     */
    public function set($payload): array
    {
        return array_map(
            fn ($data) => $this->type::validateAndCreate($data)->toArray(),
            $payload
        );
    }

    protected function ensureDataTypeExists(?string $type): string
    {
        if ($type === null) {
            throw new Exception('Cannot create a data cast because no data class was given');
        }

        if (!class_exists($type)) {
            throw new Exception("Cannot create a data cast for `$type` because the data does not exist");
        }

        if (!class_implements($type, BaseData::class)) {
            throw new Exception("Cannot create a data cast for `$type` because the class does not implement data");
        }

        return $type;
    }
}
