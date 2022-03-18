<?php

namespace Spatie\LaravelSettings\Tests;

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;

beforeEach(function () {
    $this->migrator = new SettingsMigrator(
        new DatabaseSettingsRepository(config('settings.repositories.database'))
    );

    $this->blueprint = new SettingsBlueprint('test', $this->migrator);
});

it('can rename properties', function () {
    $this->migrator->add('test.property', 'payload');

    $this->blueprint->rename('property', 'otherProperty');

    $this->assertDatabaseHasSetting('test.otherProperty', 'payload');
    $this->assertDatabaseDoesNotHaveSetting('test.property');
});

it('can add properties', function () {
    $this->blueprint->add('property', 'payload');

    $this->assertDatabaseHasSetting('test.property', 'payload');
});

it('can delete properties', function () {
    $this->migrator->add('test.property', 'payload');

    $this->blueprint->delete('property');

    $this->assertDatabaseDoesNotHaveSetting('test.property');
});

it('can update properties', function () {
    $this->migrator->add('test.property', 'payload');

    $this->blueprint->update('property', fn () => 'otherPayload');

    $this->assertDatabaseHasSetting('test.property', 'otherPayload');
});

it('can add a property encrypted', function () {
    $this->blueprint->addEncrypted('property', 'payload');

    $this->assertDatabaseHasEncryptedSetting('test.property', 'payload');
});

it('can update an encrypted property', function () {
    $this->blueprint->addEncrypted('property', 'payload');

    $this->blueprint->updateEncrypted('property', fn () => 'otherPayload');

    $this->assertDatabaseHasEncryptedSetting('test.property', 'otherPayload');
});

it('can encrypt a setting', function () {
    $this->blueprint->add('property', 'payload');

    $this->blueprint->encrypt('property');

    $this->assertDatabaseHasEncryptedSetting('test.property', 'payload');
});

it('can decrypt a setting', function () {
    $this->blueprint->addEncrypted('property', 'payload');

    $this->blueprint->decrypt('property');

    $this->assertDatabaseHasSetting('test.property', 'payload');
});
