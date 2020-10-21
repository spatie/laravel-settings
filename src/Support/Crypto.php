<?php

namespace Spatie\LaravelSettings\Support;

class Crypto
{
    public static function encrypt($payload): ?string
    {
        return $payload !== null
            ? encrypt($payload)
            : $payload;
    }

    public static function decrypt(?string $payload)
    {
        return $payload !== null
            ? decrypt($payload)
            : null;
    }
}
