<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use DateTime;

class DateTimeCast implements SettingsCast
{
    public function get($payload): DateTime
    {
        return new DateTime($payload);
    }

    /**
     * @param DateTime $payload
     *
     * @return string
     */
    public function set($payload): string
    {
        return $payload->format(DATE_ATOM);
    }
}
