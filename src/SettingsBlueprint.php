<?php

namespace Spatie\LaravelSettings;

use Closure;

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

    private function prependWithGroup(string $name): string
    {
        return "{$this->group}.{$name}";
    }
}
