<?php

namespace Spatie\LaravelSettings\Tests;

use Spatie\LaravelSettings\Exceptions\InvalidSettingName;
use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;
use Spatie\LaravelSettings\Exceptions\SettingDoesNotExist;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;

class SettingsMigratorTest extends TestCase
{
    private SettingsMigrator $settingsMigrator;

    protected function setUp() : void
    {
        parent::setUp();

        $this->settingsMigrator = new SettingsMigrator(
            new DatabaseSettingsRepository(config('settings.repositories.database'))
        );
    }

    /** @test */
    public function it_can_add_a_setting(): void
    {
        $this->settingsMigrator->add('compliance.enabled', true);

        $this->assertDatabaseHasSetting('compliance.enabled', true);
    }

    /** @test */
    public function it_cannot_add_the_same_setting_twice(): void
    {
        $this->expectException(SettingAlreadyExists::class);

        $this->settingsMigrator->add('compliance.enabled', true);
        $this->settingsMigrator->add('compliance.enabled', true);
    }

    /** @test */
    public function it_can_add_a_setting_with_the_same_name_in_different_groups(): void
    {
        $this->settingsMigrator->add('compliance.enabled', true);
        $this->settingsMigrator->add('eduction.enabled', false);

        $this->assertDatabaseHasSetting('compliance.enabled', true);
        $this->assertDatabaseHasSetting('eduction.enabled', false);
    }

    /** @test */
    public function it_cannot_provide_a_invalid_name_when_creating_a_setting(): void
    {
        $this->expectException(InvalidSettingName::class);

        $this->settingsMigrator->add('compliance', true);
    }

    /** @test */
    public function it_can_rename_a_setting(): void
    {
        $this->settingsMigrator->add('compliance.enabled', true);

        $this->settingsMigrator->rename('compliance.enabled', 'eduction.enabled');

        $this->assertDatabaseHasSetting('eduction.enabled', true);
        $this->assertDatabaseDoesNotHaveSetting('compliance.enabled');
    }

    /** @test */
    public function it_cannot_rename_a_property_that_does_not_exist(): void
    {
        $this->expectException(SettingDoesNotExist::class);

        $this->settingsMigrator->rename('compliance.enabled', 'eduction.enabled');
    }

    /** @test */
    public function it_cannot_rename_a_property_to_another_existing_property(): void
    {
        $this->expectException(SettingAlreadyExists::class);

        $this->settingsMigrator->add('eduction.enabled', true);
        $this->settingsMigrator->add('compliance.enabled', true);

        $this->settingsMigrator->rename('eduction.enabled', 'compliance.enabled');
    }

    /** @test */
    public function it_cannot_rename_from_an_invalid_property(): void
    {
        $this->expectException(InvalidSettingName::class);

        $this->settingsMigrator->rename('eduction', 'compliance.enabled');
    }

    /** @test */
    public function it_cannot_rename_to_an_invalid_property(): void
    {
        $this->expectException(InvalidSettingName::class);

        $this->settingsMigrator->add('eduction.enabled', true);

        $this->settingsMigrator->rename('eduction.enabled', 'compliance');
    }

    /** @test */
    public function it_can_delete_a_setting(): void
    {
        $this->settingsMigrator->add('eduction.enabled', true);

        $this->settingsMigrator->delete('eduction.enabled');

        $this->assertDatabaseDoesNotHaveSetting('eduction.enabled');
    }

    /** @test */
    public function it_cannot_delete_a_setting_that_does_not_exist(): void
    {
        $this->expectException(SettingDoesNotExist::class);

        $this->settingsMigrator->delete('eduction.enabled');
    }

    /** @test */
    public function it_cannot_delete_a_setting_with_an_invalid_property(): void
    {
        $this->expectException(InvalidSettingName::class);

        $this->settingsMigrator->delete('eductions');
    }

    /** @test */
    public function it_can_update_a_setting(): void
    {
        $this->settingsMigrator->add('user.name', 'Brent Roose');

        $this->settingsMigrator->update('user.name', fn (string $name) => 'Ruben Van Assche');

        $this->assertDatabaseHasSetting('user.name', 'Ruben Van Assche');
    }

    /** @test */
    public function it_cannot_update_a_setting_that_does_not_exist(): void
    {
        $this->expectException(SettingDoesNotExist::class);

        $this->settingsMigrator->update('user.name', fn (string $name) => 'Ruben Van Assche');
    }

    /** @test */
    public function it_can_perform_migrations_within_a_group(): void
    {
        $this->settingsMigrator->inGroup('test', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('a', 'Alpha');
        });

        $this->assertDatabaseHasSetting('test.a', 'Alpha');
    }
}
