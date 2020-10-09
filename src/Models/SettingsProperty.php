<?php

namespace Spatie\LaravelSettings\Models;

use Illuminate\Database\Eloquent\Model;

class SettingsProperty extends Model
{
    protected $table = 'settings';

    protected $guarded = [];

    protected $casts = [
        'locked' => 'boolean',
    ];

    public static function get(string $property)
    {
        [$group, $name] = explode('.', $property);

        $setting = self::query()
            ->where('group', $group)
            ->where('name', $name)
            ->first('payload');

        return json_decode($setting->getAttribute('payload'));
    }
}
