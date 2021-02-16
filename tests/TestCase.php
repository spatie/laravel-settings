<?php

namespace Spatie\LaravelSettings\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use PHPUnit\Framework\Assert as PHPUnit;
use Spatie\LaravelSettings\LaravelSettingsServiceProvider;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Support\Crypto;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;

class TestCase extends BaseTestCase
{
    public function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('app.key', 'base64:yDt5+GiUDRGNCFMLd5L9L7/dIc3wg/7ZmNhNVZEL8SA=');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->artisan('migrate', ['--database' => 'testing']);

        include_once __DIR__ . '/../database/migrations/create_settings_table.php.stub';
        (new \CreateSettingsTable())->up();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelSettingsServiceProvider::class,
        ];
    }

    protected function setRegisteredSettings(array $settings)
    {
        resolve(SettingsContainer::class)->clearCache();

        config()->set('settings.settings', $settings);
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

    protected function assertDatabaseHasEncryptedSetting(string $property, $value): void
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

        PHPUnit::assertNotEquals($value, json_decode($setting->payload, true));
        PHPUnit::assertEquals($value, Crypto::decrypt(json_decode($setting->payload, true)));
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
