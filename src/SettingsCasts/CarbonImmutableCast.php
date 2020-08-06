<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use Carbon\CarbonImmutable;

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
