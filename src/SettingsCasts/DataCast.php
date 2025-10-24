<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use Exception;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Data;

class DataCast implements SettingsCast
{
    protected string $type;

    protected bool $validate;

    public function __construct(?string $type, string|bool $validate = false)
    {
        $this->type = $this->ensureDataTypeExists($type);

        $this->validate = is_string($validate)
            ? in_array($validate, ['true', '1'], true)
            : $validate;
    }

    public function get($payload): Data
    {
        return $this->validate
            ? $this->type::validateAndCreate($payload)
            : $this->type::from($payload);
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

        if (! class_implements($type, BaseData::class)) {
            throw new Exception("Cannot create a data cast for `$type` because the class does not implement data");
        }

        return $type;
    }
}
