<?php

namespace Spatie\LaravelSettings;

use Illuminate\Database\Eloquent\Model;

class SettingsProperty extends Model
{
    protected $table = 'settings';

    protected $guarded = [];

    protected $casts = [
        'locked' => 'boolean',
    ];

    public static function getTableName()
    {
        return (new self())->getTable();
    }

    public static function get(string $property)
    {
        [$group, $name] = explode('.', $property);

        $setting = self::query()
            ->where('group', $group)
            ->where('name', $name)
            ->first('payload');

        return json_decode($setting->get('payload'));
    }
}
