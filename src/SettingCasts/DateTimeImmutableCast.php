<?php

namespace Spatie\LaravelSettings\SettingCasts;

use DateTime;
use DateTimeImmutable;

class DateTimeImmutableCast implements SettingsCast
{
    public function get($payload): DateTimeImmutable
    {
        return new DateTimeImmutable($payload);
    }

    /**
     * @param DateTimeImmutable $payload
     *
     * @return string
     */
    public function set($payload): string
    {
        return $payload->format(DATE_ATOM);
    }
}
