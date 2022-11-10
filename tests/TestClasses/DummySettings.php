<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeZone;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsCasts\DataCast;

class DummySettings extends Settings
{
    public string $string;

    public bool $bool;

    public int $int;

    public array $array;

    public ?string $nullable_string;

    public DummyData $dto;

    /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummyData[] */
    public array $dto_array;

    // Todo: enable this later
//    /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummyDto[] */
//    public array $dto_collection;

    public DateTimeImmutable $date_time;

    public Carbon $carbon;

    public ?DateTimeZone $nullable_date_time_zone;

    public static function group(): string
    {
        return 'dummy';
    }

    public static function casts(): array
    {
        return [
            'dto' => new DataCast(DummyData::class),
        ];
    }
}
