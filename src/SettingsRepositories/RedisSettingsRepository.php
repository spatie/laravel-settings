<?php

namespace Spatie\LaravelSettings\SettingsRepositories;

use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Redis;
use Spatie\LaravelSettings\Models\SettingsProperty;

class RedisSettingsRepository implements SettingsRepository
{
    /** @var \Redis */
    private $connection;

    private string $prefix;

    public function __construct(array $config)
    {
        $this->connection = resolve(RedisManager::class)
            ->connection($config['connection'] ?? null)
            ->client();

        $this->prefix = array_key_exists('prefix', $config)
            ? "{$config['prefix']}."
            : '';
    }

    public function updateOrCreatePropertiesInGroup(string $group, array $properties): void
    {
        $properties = collect($properties)->mapWithKeys(function ($payload, string $name) {
            return [$name => json_encode($payload)];
        })->toArray();

        $this->connection->hMSet($this->prefix . $group, $properties);
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

    public function updatePropertyPayload(string $group, string $name, $value): void
    {
        $this->connection->hSet($this->prefix . $group, $name, json_encode($value));
    }

    public function deleteProperty(string $group, string $name)
    {
        $this->connection->hDel($this->prefix . $group, $name);
    }

    public function lockProperties(string $group, array $properties)
    {
        $this->connection->sAdd($this->getLocksSetKey($group), ...$properties);
    }

    public function unlockProperties(string $group, array $properties)
    {
        $this->connection->sRem($this->getLocksSetKey($group), ...$properties);
    }

    public function getLockedProperties(string $group): array
    {
        return $this->connection->sMembers($this->getLocksSetKey($group));
    }

    private function getLocksSetKey(string $group): string
    {
        return $this->prefix . 'locks.' . $group;
    }
}
