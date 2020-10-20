<?php

namespace Spatie\LaravelSettings\SettingsCasts;

interface SettingsCast
{
    /**
     * Will be used to when retrieving a value from the repository, and
     * inserting it into the settings DTO.
     */
    public function get($payload);

    /**
     * Will be used to when retrieving a value from the settings DTO, and
     * inserting it into the repository.
     */
    public function set($payload);
}
