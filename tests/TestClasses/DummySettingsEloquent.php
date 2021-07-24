<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

use Spatie\LaravelSettings\SettingsEloquent;

class DummySettingsEloquent extends SettingsEloquent
{
    public string $fname;

    public string $lname;

    public string $description;

    public static function group(): string
    {
        return 'settings_eloquent';
    }

    public function getFullNameAttribute()
    {
        return $this->fname . ' ' . $this->lname;
    }
}
