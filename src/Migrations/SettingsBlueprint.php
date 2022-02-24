<?php

namespace Spatie\LaravelSettings\Migrations;

use Closure;

class SettingsBlueprint
{
    protected string $group;

    protected SettingsMigrator $migrator;

    public function __construct(string $group, SettingsMigrator $migrator)
    {
        $this->group = $group;

        $this->migrator = $migrator;
    }

    public function rename(string $from, string $to): void
    {
        $this->migrator->rename(
            $this->prependWithGroup($from),
            $this->prependWithGroup($to)
        );
    }

    public function add(string $name, $value = null, bool $encrypted = false): void
    {
        $this->migrator->add($this->prependWithGroup($name), $value, $encrypted);
    }

    public function delete(string $name): void
    {
        $this->migrator->delete($this->prependWithGroup($name));
    }

    public function update(string $name, Closure $closure, bool $encrypted = false): void
    {
        $this->migrator->update($this->prependWithGroup($name), $closure, $encrypted);
    }

    public function addEncrypted(string $name, $value = null): void
    {
        $this->migrator->addEncrypted($this->prependWithGroup($name), $value);
    }

    public function updateEncrypted(string $name, Closure $closure): void
    {
        $this->migrator->updateEncrypted($this->prependWithGroup($name), $closure);
    }

    public function encrypt(string $name): void
    {
        $this->migrator->encrypt($this->prependWithGroup($name));
    }

    public function decrypt(string $name): void
    {
        $this->migrator->decrypt($this->prependWithGroup($name));
    }

    protected function prependWithGroup(string $name): string
    {
        return "{$this->group}.{$name}";
    }
}
