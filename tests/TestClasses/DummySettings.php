<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

use Spatie\LaravelSettings\Settings;

class DummySettings extends Settings
{
    public string $string;

    public bool $bool;

    public int $int;

    public array $array;

    public ?string $nullable_string;

    public string $default_string = 'default';

    public DummyDto $dto;

    /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummyDto[] */
    public array $dto_collection;

    public static function group(): string
    {
        return 'dummy';
    }
}
