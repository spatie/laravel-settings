<?php

namespace Spatie\LaravelSettings\Migrations;

use Illuminate\Database\Migrations\Migration;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;

abstract class SettingsMigration extends Migration
{
    protected SettingsMigrator $migrator;

    abstract public function up();

    public function __construct()
    {
        $this->migrator = resolve(SettingsMigrator::class);
    }
}
