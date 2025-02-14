<?php

namespace Spatie\LaravelSettings\Tests;

use Carbon\CarbonImmutable;
use Orchestra\Testbench\TestCase as BaseTestCase;
use PHPUnit\Framework\Assert as PHPUnit;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\LaravelSettings\LaravelSettingsServiceProvider;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Support\Crypto;

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

        $migration = require(__DIR__ . '/../database/migrations/create_settings_table.php.stub');

        (new $migration)->up();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelDataServiceProvider::class,
            LaravelSettingsServiceProvider::class,
        ];
    }

    protected function setRegisteredSettings(array $settings): self
    {
        $settingsContainer = resolve(SettingsContainer::class);

        $settingsContainer->clearCache();

        config()->set('settings.settings', $settings);

        $settingsContainer->registerBindings();

        return $this;
    }

    protected function migrateDummySimpleSettings(
        string $name = 'Louis Armstrong',
        string $description = 'Hello Dolly'
    ): self {
        resolve(SettingsMigrator::class)->inGroup('dummy_simple', function (SettingsBlueprint $blueprint) use ($description, $name): void {
            $blueprint->add('name', $name);
            $blueprint->add('description', $description);
        });

        return $this;
    }

    protected function migrateDummySettings(CarbonImmutable $date): self
    {
        resolve(SettingsMigrator::class)->inGroup('dummy', function (SettingsBlueprint $blueprint) use ($date): void {
            $blueprint->add('string', 'Ruben');
            $blueprint->add('bool', false);
            $blueprint->add('int', 42);
            $blueprint->add('array', ['John', 'Ringo', 'Paul', 'George']);
            $blueprint->add('nullable_string', null);
            $blueprint->add('default_string', null);
            $blueprint->add('dto', ['name' => 'Freek']);
            $blueprint->add('dto_array', [
                ['name' => 'Seb'],
                ['name' => 'Adriaan'],
            ]);

            $blueprint->add('date_time', $date->toAtomString());
            $blueprint->add('carbon', $date->toAtomString());
            $blueprint->add('illuminate_carbon', $date->toAtomString());
            $blueprint->add('nullable_date_time_zone', 'europe/brussels');
        });

        return $this;
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

        PHPUnit::assertNotSame($value, json_decode($setting->payload, true));
        PHPUnit::assertSame($value, Crypto::decrypt(json_decode($setting->payload, true)));
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

    protected function skipIfPHPLowerThen(string $version)
    {
        [$currentMajor, $currentMinor] = explode('.', phpversion());
        [$major, $minor] = explode('.', $version);

        if ($currentMajor < $major || ($currentMajor === $major && $currentMinor < $minor)) {
            $this->markTestSkipped("PHP version {$version} required for this test");
        }
    }
}
