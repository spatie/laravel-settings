<?php

namespace Spatie\LaravelSettings\Tests;

use Spatie\LaravelSettings\LaravelSettingsServiceProvider;
use Spatie\LaravelSettings\SettingsProperty;
use PHPUnit\Framework\Assert as PHPUnit;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelSettingsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);


        include_once __DIR__ . '/../database/migrations/create_settings_table.php.stub';
        (new \CreateSettingsTable())->up();
    }

    protected function assertDatabaseHasSetting(string $property, $value): void
    {
        [$group, $name] = explode('.', $property);

        $setting = SettingsProperty::query()
            ->where('group', $group)
            ->where('name', $name)
            ->first();

        PHPUnit::assertNotNull(
            $setting,
            "The setting {$group}.{$name} could not be found in the database"
        );

        PHPUnit::assertEquals($value, json_decode($setting->payload, true));
    }

    protected function assertDatabaseDoesNotHaveSetting(string $property): void
    {
        [$group, $name] = explode('.', $property);

        $setting = SettingsProperty::query()
            ->where('group', $group)
            ->where('name', $name)
            ->first();

        PHPUnit::assertNull(
            $setting,
            "The setting {$group}.{$name} should not exist in the database"
        );
    }
}
