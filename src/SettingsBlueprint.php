<?php

namespace App\Support\Settings;

use Closure;
use Illuminate\Support\Str;

class SettingsBlueprint
{
    private string $group;

    private SettingsMigrator $migrator;

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

    public function add(string $name, $value): void
    {
        $this->migrator->add($this->prependWithGroup($name), $value);
    }

    public function delete(string $name): void
    {
        $this->migrator->delete($this->prependWithGroup($name));
    }

    public function update(string $name, Closure $closure): void
    {
        $this->migrator->update($this->prependWithGroup($name), $closure);
    }

    public function merge(array $from, string $to, Closure $closure): void
    {
        $this->migrator->merge(
            array_map(fn (string $name) => $this->prependWithGroup($name), $from),
            $this->prependWithGroup($to),
            $closure
        );
    }

    public function split(string $from, array $to, Closure ...$closures): void
    {
        $this->migrator->split(
            $this->prependWithGroup($from),
            array_map(fn (string $name) => $this->prependWithGroup($name), $to),
            ...$closures
        );
    }

    private function prependWithGroup(string $name): string
    {
        return Str::contains($name, '.')
            ? $name
            : "{$this->group}.{$name}";
    }
}
