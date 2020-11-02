<?php

namespace Spatie\LaravelSettings\SettingsCasts;

class ArraySettingsCast implements SettingsCast
{
    protected SettingsCast $cast;

    public function __construct(SettingsCast $cast)
    {
        $this->cast = $cast;
    }

    public function getCast(): ?SettingsCast
    {
        return $this->cast;
    }

    public function get($payload): array
    {
        return array_map(
            fn ($data) => $this->cast->get($data),
            $payload
        );
    }

    public function set($payload)
    {
        return array_map(
            fn ($data) => $this->cast->set($data),
            $payload
        );
    }
}
