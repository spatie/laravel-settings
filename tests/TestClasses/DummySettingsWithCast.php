<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

use Illuminate\Support\Collection;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsCasts\CollectionCast;

class DummySettingsWithCast extends Settings
{
    public Collection $collection;

    public static function group(): string
    {
        return 'dummy_with_collection_cast';
    }

    public static function casts(): array
    {
        return [
            'collection' => CollectionCast::class,
        ];
    }
}
