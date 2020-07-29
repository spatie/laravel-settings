<?php

namespace Spatie\LaravelSettings\SettingsRepository;

use DB;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelSettings\SettingsProperty;

class DatabaseSettingsRepository implements SettingsRepository
{
    /** @var \Spatie\LaravelSettings\SettingsProperty|string */
    private string $propertyModel;

    public function __construct(array $config)
    {
        $this->propertyModel = $config['model'];
    }

    public function getPropertiesInGroup(string $group): array
    {
        return $this->propertyModel::query()
            ->where('group', $group)
            ->select(['name', 'payload'])
            ->get()
            ->mapWithKeys(fn($object) => [$object->name => json_decode($object->payload, true)])
            ->toArray();

        /** @var \Spatie\LaravelSettings\SettingsProperty $temp */
        $temp = new $this->propertyModel;

        return DB::connection($temp->getConnectionName())
            ->table($temp->getTable())
            ->where('group', $group)
            ->get(['name', 'payload'])
            ->mapWithKeys(function ($object) {
                return [$object->name => json_decode($object->payload, true)];
            })
            ->toArray();
    }

    public function checkIfPropertyExists(string $group, string $name): bool
    {
        return $this->propertyModel::query()
            ->where('group', $group)
            ->where('name', $name)
            ->exists();
    }

    public function getPropertyPayload(string $group, string $name)
    {
        $setting = $this->propertyModel::query()
            ->where('group', $group)
            ->where('name', $name)
            ->first('payload')
            ->toArray();

        return json_decode($setting['payload']);
    }

    public function createProperty(string $group, string $name, $payload): SettingsProperty
    {
        return $this->propertyModel::create([
            'group' => $group,
            'name' => $name,
            'payload' => json_encode($payload),
            'locked' => false,
        ]);
    }

    public function updatePropertyPayload(string $group, string $name, $value): void
    {
        $this->propertyModel::query()
            ->where('group', $group)
            ->where('name', $name)
            ->update([
                'payload' => json_encode($value),
            ]);
    }

    public function deleteProperty(string $group, string $name): void
    {
        $this->propertyModel::query()
            ->where('group', $group)
            ->where('name', $name)
            ->delete();
    }

    public function lockProperties(string $group, array $properties)
    {
        $this->propertyModel::query()
            ->where('group', $group)
            ->whereIn('name', $properties)
            ->update(['locked' => true]);
    }

    public function unlockProperties(string $group, array $properties)
    {
        $this->propertyModel::query()
            ->where('group', $group)
            ->whereIn('name', $properties)
            ->update(['locked' => false]);
    }

    public function getLockedProperties(string $group): array
    {
        return $this->propertyModel::query()
            ->where('group', $group)
            ->where('locked', true)
            ->pluck('name')
            ->toArray();
    }

    public function import(array $data): void
    {
        foreach ($data as $group => $properties) {
            foreach ($properties as $name => $value) {
                $this->propertyModel::updateOrCreate([
                    'group' => $group,
                    'name' => $name,
                    'locked' => false,
                ], [
                    'payload' => json_encode($value),
                ]);
            }
        }
    }

    public function export(): array
    {
        return $this->propertyModel::all()
            ->groupBy('group')
            ->map(function (Collection $properties) {
                return $properties->mapWithKeys(function (SettingsProperty $property) {
                    return [$property->name => json_decode($property->payload)];
                });
            })
            ->toArray();
    }
}
