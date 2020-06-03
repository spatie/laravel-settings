<?php

namespace App\Support\Settings\SettingsConnection;

use App\Support\Settings\SettingsProperty;
use DB;
use Illuminate\Database\Eloquent\Collection;

class DatabaseSettingsConnection implements SettingsConnection
{
    public function getPropertiesInGroup(string $group): array
    {
        return DB::table('settings')
            ->where('group', $group)
            ->get(['name', 'payload'])
            ->mapWithKeys(function ($object) {
                return [$object->name => json_decode($object->payload, true)];
            })
            ->toArray();
    }

    public function checkIfPropertyExists(string $group, string $name): bool
    {
        return SettingsProperty::query()
            ->where('group', $group)
            ->where('name', $name)
            ->exists();
    }

    public function getPropertyPayload(string $group, string $name)
    {
        $setting = SettingsProperty::query()
            ->where('group', $group)
            ->where('name', $name)
            ->first('payload')
            ->toArray();

        return json_decode($setting['payload']);
    }

    public function createProperty(string $group, string $name, $payload): SettingsProperty
    {
        return SettingsProperty::create([
            'group' => $group,
            'name' => $name,
            'payload' => json_encode($payload),
        ]);
    }

    public function updatePropertyPayload(string $group, string $name, $value): void
    {
        SettingsProperty::query()
            ->where('group', $group)
            ->where('name', $name)
            ->update([
                'payload' => json_encode($value),
            ]);
    }

    public function deleteProperty(string $group, string $name): void
    {
        SettingsProperty::query()
            ->where('group', $group)
            ->where('name', $name)
            ->delete();
    }

    public function import(array $data): void
    {
        foreach ($data as $group => $properties) {
            foreach ($properties as $name => $value) {
                SettingsProperty::updateOrCreate([
                    'group' => $group,
                    'name' => $name,
                ], [
                    'payload' => json_encode($value),
                ]);
            }
        }
    }

    public function export(): array
    {
        return SettingsProperty::all()
            ->groupBy('group')
            ->map(function (Collection $properties) {
                return $properties->mapWithKeys(function (SettingsProperty $property) {
                    return [$property->name => json_decode($property->payload)];
                });
            })
            ->toArray();
    }
}
