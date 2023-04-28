<?php

namespace Spatie\LaravelSettings\SettingsRepositories;

use Illuminate\Redis\RedisManager;

class RedisSettingsRepository implements SettingsRepository
{
    /** @var \Redis */
    protected $connection;

    protected string $prefix;

    public function __construct(array $config, RedisManager $connection)
    {
        $this->connection = $connection
            ->connection($config['connection'] ?? null)
            ->client();

        $this->prefix = array_key_exists('prefix', $config)
            ? "{$config['prefix']}."
            : '';
    }

    public function getPropertiesInGroup(string $group): array
    {
        return collect($this->connection->hGetAll($this->prefix . $group))
            ->mapWithKeys(function ($payload, string $name) {
                return [$name => json_decode($payload, true)];
            })->toArray();
    }

    public function checkIfPropertyExists(string $group, string $name): bool
    {
        return $this->connection->hExists($this->prefix . $group, $name);
    }

    public function getPropertyPayload(string $group, string $name)
    {
        return json_decode($this->connection->hGet($this->prefix . $group, $name));
    }

    public function createProperty(string $group, string $name, $payload): void
    {
        $this->connection->hSet($this->prefix . $group, $name, json_encode($payload));
    }

    public function updatePropertiesPayload(string $group, array $properties): void
    {
        $properties = collect($properties)->mapWithKeys(function ($payload, $name) {
            return [$name => json_encode($payload)];
        })->toArray();

        $this->connection->hmset($this->prefix . $group, $properties);
    }

    public function deleteProperty(string $group, string $name): void
    {
        $this->connection->hDel($this->prefix . $group, $name);
    }

    public function lockProperties(string $group, array $properties): void
    {
        $this->connection->sAdd($this->getLocksSetKey($group), ...$properties);
    }

    public function unlockProperties(string $group, array $properties): void
    {
        $this->connection->sRem($this->getLocksSetKey($group), ...$properties);
    }

    public function getLockedProperties(string $group): array
    {
        return $this->connection->sMembers($this->getLocksSetKey($group));
    }

    protected function getLocksSetKey(string $group): string
    {
        return $this->prefix . 'locks.' . $group;
    }
}
