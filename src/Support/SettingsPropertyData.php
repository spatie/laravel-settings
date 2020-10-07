<?php

namespace Spatie\LaravelSettings\Support;

use Exception;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;

class SettingsPropertyData
{
    private string $name;

    /** @var mixed */
    private $payload;

    private ?SettingsCast $cast;

    private bool $locked;

    private bool $nullable;

    private bool $present;

    public function __construct(
        string $name,
        $payload,
        ?SettingsCast $cast,
        bool $locked,
        bool $nullable,
        bool $present
    ) {
        $this->name = $name;
        $this->payload = $payload;
        $this->cast = $cast;
        $this->locked = $locked;
        $this->nullable = $nullable;
        $this->present = $present;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function isPresent(): bool
    {
        return $this->present;
    }

    public function getValue()
    {
        $value = $this->cast !== null
            ? $this->cast->get($this->payload)
            : $this->payload;

        $this->ensureCorrectType($value);

        return $value;
    }

    public function setValue($value): void
    {
        if ($this->locked) {
            return;
        }

        $this->ensureCorrectType($value);

        if ($this->cast === null || $value === null) {
            $this->payload = $value;

            return;
        }

        $this->payload = $this->cast->set($value);
    }

    private function ensureCorrectType($value)
    {
        if ($value === null && $this->nullable === false) {
            throw new Exception("Property {$this->name} cannot be null");
        }
    }
}
