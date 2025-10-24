<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use Spatie\LaravelData\Data;

class ArrayDataCast implements SettingsCast
{
    protected DataCast $cast;

    public function __construct(?string $type, bool|string $validate = false)
    {
        $this->cast = new DataCast($type, $validate);
    }

    /**
     * @param array<array> $payload
     *
     * @return Data[]
     */
    public function get($payload): array
    {
        return array_map(
            fn ($data) => $this->cast->get($data),
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
            fn ($data) => $this->cast->set($data),
            $payload
        );
    }
}
