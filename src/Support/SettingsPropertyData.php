<?php

namespace Spatie\LaravelSettings\Support;

use Exception;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;

class SettingsPropertyData
{
    protected string $name;

    /** @var mixed */
    protected $payload;

    protected ?SettingsCast $cast;

    protected bool $locked;

    protected bool $nullable;

    protected bool $encrypted;

    public function __construct(
        string $name,
        $payload,
        ?SettingsCast $cast,
        bool $locked,
        bool $nullable,
        bool $encrypted
    ) {
        $this->name = $name;
        $this->payload = $payload;
        $this->cast = $cast;
        $this->locked = $locked;
        $this->nullable = $nullable;
        $this->encrypted = $encrypted;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRawPayload()
    {
        return $this->payload;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function getValue()
    {
        $value = $this->payload;

        if ($this->encrypted) {
            $value = Crypto::decrypt($value);
        }

        if ($this->cast !== null) {
            $value = $this->cast->get($value);
        }

        $this->ensureCorrectType($value);

        return $value;
    }

    public function setValue($value): void
    {
        if ($this->locked) {
            return;
        }

        if ($this->cast) {
            $value = $this->cast->set($value);
        }

        $this->ensureCorrectType($value);

        if ($this->encrypted) {
            $value = Crypto::encrypt($value);
        }

        $this->payload = $value;
    }

    protected function ensureCorrectType($value)
    {
        if ($value === null && $this->nullable === false) {
            throw new Exception("Property {$this->name} cannot be null");
        }
    }
}
