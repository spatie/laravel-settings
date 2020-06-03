<?php

namespace Tests\Support\Settings;

use App\Support\Settings\Exceptions\InvalidSettingName;
use App\Support\Settings\Exceptions\InvalidSplittingConfig;
use App\Support\Settings\Exceptions\SettingAlreadyExists;
use App\Support\Settings\Exceptions\SettingDoesNotExist;
use App\Support\Settings\SettingsBlueprint;
use App\Support\Settings\SettingsConnection\DatabaseSettingsConnection;
use App\Support\Settings\SettingsMigrator;
use App\Support\Settings\SettingsProperty;

class SettingsMigratorTest extends TestCase
{
    private SettingsMigrator $settingsMigrator;

    protected function setUp() : void
    {
        parent::setUp();

        $this->settingsMigrator = new SettingsMigrator(
            new DatabaseSettingsConnection()
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

        $this->assertEquals(0, SettingsProperty::count());
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
    public function it_can_merge_settings_into_one(): void
    {
        $this->settingsMigrator->add('user.first_name', 'Ruben');
        $this->settingsMigrator->add('user.last_name', 'Van Assche');

        $this->settingsMigrator->merge(
            ['user.first_name', 'user.last_name'],
            'user.name',
            fn (string $first, string $last) => "{$first} {$last}"
        );

        $this->assertDatabaseHasSetting('user.name', 'Ruben Van Assche');
        $this->assertDatabaseDoesNotHaveSetting('user.first_name');
        $this->assertDatabaseDoesNotHaveSetting('user.last_name');
    }

    /** @test */
    public function it_cannot_merge_non_existing_settings(): void
    {
        $this->expectException(SettingDoesNotExist::class);

        $this->settingsMigrator->add('user.first_name', 'Ruben');

        $this->settingsMigrator->merge(
            ['user.first_name', 'user.last_name'],
            'user.name',
            fn (string $first, string $last) => "{$first} {$last}"
        );
    }

    /** @test */
    public function it_cannot_merge_into_already_existing_settings(): void
    {
        $this->expectException(SettingAlreadyExists::class);

        $this->settingsMigrator->add('user.first_name', 'Ruben');
        $this->settingsMigrator->add('user.last_name', 'Van Assche');

        $this->settingsMigrator->add('user.name', 'Ruben');

        $this->settingsMigrator->merge(
            ['user.first_name', 'user.last_name'],
            'user.name',
            fn (string $first, string $last) => "{$first} {$last}"
        );
    }

    /** @test */
    public function it_split_a_setting(): void
    {
        $this->settingsMigrator->add('user.name', 'Brent Roose');

        $this->settingsMigrator->split(
            'user.name',
            ['user.first_name', 'user.last_name'],
            fn (string $name) => explode(' ', $name)[0],
            fn (string $name) => explode(' ', $name)[1]
        );

        $this->assertDatabaseHasSetting('user.first_name', 'Brent');
        $this->assertDatabaseHasSetting('user.last_name', 'Roose');
        $this->assertDatabaseDoesNotHaveSetting('user.name');
    }

    /** @test */
    public function it_cannot_split_a_non_existing_setting(): void
    {
        $this->expectException(SettingDoesNotExist::class);

        $this->settingsMigrator->split(
            'user.name',
            ['user.first_name', 'user.last_name'],
            fn (string $name) => explode(' ', $name)[0],
            fn (string $name) => explode(' ', $name)[1]
        );
    }

    /** @test */
    public function it_cannot_split_to_a_setting_that_already_exists(): void
    {
        $this->expectException(SettingAlreadyExists::class);

        $this->settingsMigrator->add('user.name', 'Brent Roose');
        $this->settingsMigrator->add('user.last_name', 'Roose');

        $this->settingsMigrator->split(
            'user.name',
            ['user.first_name', 'user.last_name'],
            fn (string $name) => explode(' ', $name)[0],
            fn (string $name) => explode(' ', $name)[1]
        );
    }

    /** @test */
    public function it_cannot_split_when_there_are_not_the_right_amount_of_closures(): void
    {
        $this->expectException(InvalidSplittingConfig::class);

        $this->settingsMigrator->add('user.name', 'Brent Roose');

        $this->settingsMigrator->split(
            'user.name',
            ['user.first_name', 'user.last_name'],
            fn (string $name) => explode(' ', $name)[0],
        );
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
