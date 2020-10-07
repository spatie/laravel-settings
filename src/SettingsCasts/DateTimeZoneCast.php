<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use DateTimeZone;

class DateTimeZoneCast implements SettingsCast
{
    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @param DateTimeZone $payload
     *
     * @return string
     */
    public function get($payload): string
    {
        return $payload->getName();
    }

    public function set($payload): DateTimeZone
    {
        return new DateTimeZone($payload);
    }
}
