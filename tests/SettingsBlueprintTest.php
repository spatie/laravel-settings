<?php

namespace Spatie\LaravelSettings\Tests;

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;

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

        $this->blueprint->update('property', fn () => 'otherPayload');

        $this->assertDatabaseHasSetting('test.property', 'otherPayload');
    }

    /** @test */
    public function it_can_add_a_property_encrypted()
    {
        $this->blueprint->addEncrypted('property', 'payload');

        $this->assertDatabaseHasEncryptedSetting('test.property', 'payload');
    }

    /** @test */
    public function it_can_update_an_encrypted_property()
    {
        $this->blueprint->addEncrypted('property', 'payload');

        $this->blueprint->updateEncrypted('property', fn () => 'otherPayload');

        $this->assertDatabaseHasEncryptedSetting('test.property', 'otherPayload');
    }

    /** @test */
    public function it_can_encrypt_a_setting()
    {
        $this->blueprint->add('property', 'payload');

        $this->blueprint->encrypt('property');

        $this->assertDatabaseHasEncryptedSetting('test.property', 'payload');
    }

    /** @test */
    public function it_can_decrypt_a_setting()
    {
        $this->blueprint->addEncrypted('property', 'payload');

        $this->blueprint->decrypt('property');

        $this->assertDatabaseHasSetting('test.property', 'payload');
    }
}
