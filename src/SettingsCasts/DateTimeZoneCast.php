<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use DateTimeZone;

class DateTimeZoneCast implements SettingsCast
{
    protected string $type;

    public function __construct(?string $type)
    {
        $this->type = $type ?? DateTimeZone::class;
    }

    public function get($payload): ?DateTimeZone
    {
        return $payload !== null
            ? new DateTimeZone($payload)
            : null;
    }

    /**
     * @param DateTimeZone|null $payload
     *
     * @return string
     */
    public function set($payload): ?string
    {
        return $payload !== null
            ? $payload->getName()
            : null;
    }
}
