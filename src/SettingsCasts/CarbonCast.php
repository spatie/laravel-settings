<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use Carbon\Carbon;

class CarbonCast implements SettingsCast
{
    public function get($payload): Carbon
    {
        return new Carbon($payload);
    }

    /**
     * @param Carbon $payload
     *
     * @return string
     */
    public function set($payload): string
    {
        return $payload->toAtomString();
    }
}
