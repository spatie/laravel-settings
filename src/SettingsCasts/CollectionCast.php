<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use Illuminate\Support\Collection;

class CollectionCast implements SettingsCast
{
    public function get($payload): Collection
    {
        return collect($payload);
    }

    public function set($payload): array
    {
        return $payload->toArray();
    }
}
