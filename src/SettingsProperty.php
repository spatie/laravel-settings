<?php

namespace App\Support\Settings;

use Illuminate\Database\Eloquent\Model;

class SettingsProperty extends Model
{
    protected $table = 'settings';

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
