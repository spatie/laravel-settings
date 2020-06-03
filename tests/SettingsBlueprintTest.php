<?php

namespace Tests\Support\Settings;

use App\Support\Settings\SettingsBlueprint;
use App\Support\Settings\SettingsConnection\DatabaseSettingsConnection;
use App\Support\Settings\SettingsMigrator;

class SettingsBlueprintTest extends TestCase
{
    private SettingsMigrator $migrator;

    private SettingsBlueprint $blueprint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = new SettingsMigrator(
            new DatabaseSettingsConnection()
        );

        $this->blueprint = new SettingsBlueprint('test', $this->migrator);
    }

    /**
     * @test
     * @dataProvider renameProvider
     */
    public function it_can_rename_properties(string $from, string $to, string $expected): void
    {
        $this->migrator->add('test.property', 'payload');

        $this->blueprint->rename($from, $to);

        $this->assertDatabaseHasSetting($expected, 'payload');
    }

    /** @test */
    public function it_can_add_properties(): void
    {
        $this->blueprint->add('test.property', 'payload');
        $this->blueprint->add('otherProperty', 'payload');

        $this->assertDatabaseHasSetting('test.property', 'payload');
        $this->assertDatabaseHasSetting('test.otherProperty', 'payload');
    }

    /** @test */
    public function it_can_delete_properties(): void
    {
        $this->migrator->add('test.property', 'payload');
        $this->migrator->add('test.otherProperty', 'payload');

        $this->blueprint->delete('test.property');
        $this->blueprint->delete('otherProperty');

        $this->assertDatabaseDoesNotHaveSetting('test.property');
        $this->assertDatabaseDoesNotHaveSetting('test.otherProperty');
    }

    /** @test */
    public function it_can_update_properties(): void
    {
        $this->migrator->add('test.property', 'payload');
        $this->migrator->add('test.otherProperty', 'payload');

        $this->blueprint->update('test.property', fn () => 'otherPayload');
        $this->blueprint->update('otherProperty', fn () => 'otherPayload');

        $this->assertDatabaseHasSetting('test.property', 'otherPayload');
        $this->assertDatabaseHasSetting('test.otherProperty', 'otherPayload');
    }

    public function renameProvider(): array
    {
        return [
            ['test.property', 'other.property', 'other.property'],
            ['property', 'other.property', 'other.property'],
            ['test.property', 'otherProperty', 'test.otherProperty'],
            ['property', 'otherProperty', 'test.otherProperty'],
        ];
    }
}
