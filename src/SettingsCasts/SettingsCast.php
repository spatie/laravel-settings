<?php

namespace Spatie\LaravelSettings\SettingsCasts;

interface SettingsCast
{
    public function get($payload);

    public function set($payload);
}
