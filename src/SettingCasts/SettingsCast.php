<?php

namespace Spatie\LaravelSettings\SettingCasts;

interface SettingsCast
{
    public function get($payload);

    public function set($payload);
}
