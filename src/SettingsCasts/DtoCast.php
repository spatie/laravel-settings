<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use Spatie\DataTransferObject\DataTransferObject;

class DtoCast implements SettingsCast
{
    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
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
}
