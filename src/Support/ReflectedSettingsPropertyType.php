<?php

namespace Spatie\LaravelSettings\Support;

class ReflectedSettingsPropertyType
{
    public string $type;

    public bool $isArray;

    public function __construct(string $type, bool $isArray)
    {
        $this->type = $type;
        $this->isArray = $isArray;
    }
}
