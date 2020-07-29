<?php

namespace Spatie\LaravelSettings\Tests;

use Spatie\LaravelSettings\SettingsBlueprint;
use Spatie\LaravelSettings\SettingsMigrator;
use Spatie\LaravelSettings\SettingsRepository\DatabaseSettingsRepository;

class SettingsBlueprintTest extends TestCase
{
    private SettingsMigrator $migrator;

    private SettingsBlueprint $blueprint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = new SettingsMigrator(
            new DatabaseSettingsRepository(config('settings.repositories.database'))
        );

        $this->blueprint = new SettingsBlueprint('test', $this->migrator);
    }

    /** @test */
    public function it_can_rename_properties(): void
    {
        $this->migrator->add('test.property', 'payload');

        $this->blueprint->rename('property', 'otherProperty');

        $this->assertDatabaseHasSetting('test.otherProperty', 'payload');
        $this->assertDatabaseDoesNotHaveSetting('test.property');
    }

    /** @test */
    public function it_can_add_properties(): void
    {
        $this->blueprint->add('property', 'payload');

        $this->assertDatabaseHasSetting('test.property', 'payload');
    }

    /** @test */
    public function it_can_delete_properties(): void
    {
        $this->migrator->add('test.property', 'payload');

        $this->blueprint->delete('property');

        $this->assertDatabaseDoesNotHaveSetting('test.property');
    }

    /** @test */
    public function it_can_update_properties(): void
    {
        $this->migrator->add('test.property', 'payload');

        $this->blueprint->update('property', fn() => 'otherPayload');

        $this->assertDatabaseHasSetting('test.property', 'otherPayload');
    }
}
