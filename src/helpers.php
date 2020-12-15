<?php

if (!function_exists('setting')) {
    /**
     * Get setting from setting group class
     *
     * @param string $group general_value
     * @param string $name site_name
     * @param  $fallback_value
     */
    function setting(string $group, string $name, $fallback_value = null)
    {
        $settingClass = app(get_setting_group_class($group));
        return isset($settingClass->$name) ? $settingClass->$name : $fallback_value;
    }
}

if (!function_exists('setting_update')) {
    /**
     * Update setting by using setting group class
     *
     * @param string $group general_value
     * @param string,array $name site_name, ['site_name' => 'new website']
     * @param $new_value
     * @return object GeneralSettings
     */
    function setting_update(string $group, $name, $new_value = null)
    {
        $settingClass = app(get_setting_group_class($group));

        if (is_array($name)) {
            foreach ($name as $key => $value)
                $settingClass->$key = $value;
        } else {
            $settingClass->$name = $new_value;
        }

        return $settingClass->save();
    }
}

if (!function_exists('get_setting_group_class')) {
    /**
     * Get setting group class
     *
     * Incase of ClassSetting similarity,
     * it returns the first class from settings.settings
     *
     * The group class name should ends with settings keyword
     * ex: GeneralSettings
     *
     * @param string $group general_values
     * @return object
     */
    function get_setting_group_class(string $group)
    {
        $group = implode('', array_map(fn ($group_piece) => ucfirst($group_piece), explode('_', $group)));
        $groupSettings = "${group}Settings";
        $namespace = preg_grep("~${groupSettings}~", config('settings.settings'));

        if (count($namespace) > 0)
            return array_values($namespace)[0];
        else
            throw new Spatie\LaravelSettings\Exceptions\MissingSettings();
    }
}
