<?php

namespace Spatie\LaravelSettings\SettingCasts;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;

class CarbonImmutableCast implements SettingsCast
{
    public function get($payload): CarbonImmutable
    {
        return new CarbonImmutable($payload);
    }

    /**
     * @param CarbonImmutable $payload
     *
     * @return string
     */
    public function set($payload): string
    {
        return $payload->toAtomString();
    }
}
