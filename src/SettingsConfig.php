<?php

namespace Spatie\LaravelSettings;

class SettingsConfig
{
    private static ?array  $casts = null;

    /**
     * @return array<string, \Illuminate\Contracts\Database\Eloquent\CastsAttributes>
     */
    public function getCasts(): array
    {
        return self::$casts ??= array_map(
            fn(string $castsSettings) => new $castsSettings,
            config('settings.casts')
        );
    }
}
