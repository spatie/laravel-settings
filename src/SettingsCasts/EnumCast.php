<?php

namespace Spatie\LaravelSettings\SettingsCasts;

use BackedEnum;
use Exception;
use UnitEnum;

class EnumCast implements SettingsCast
{
    private string $enum;

    public function __construct(string $enum)
    {
        $this->enum = $enum;
    }

    public function get($payload): ?UnitEnum
    {
        if ($payload === null) {
            return null;
        }

        if (is_a($this->enum, BackedEnum::class, true)) {
            return $this->enum::from($payload);
        }

        if (is_a($this->enum, UnitEnum::class, true)) {
            foreach ($this->enum::cases() as $enum) {
                if ($enum->name === $payload) {
                    return $enum;
                }
            }
        }

        throw new Exception('Invalid enum');
    }

    public function set($payload): string|int|null
    {
        if ($payload === null) {
            return null;
        }

        if ($payload instanceof BackedEnum) {
            return $payload->value;
        }

        if ($payload instanceof UnitEnum) {
            return $payload->name;
        }

        throw new Exception('Invalid enum');
    }
}
