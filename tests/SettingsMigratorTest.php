<?php

namespace Spatie\LaravelSettings\Tests;

use DateTimeZone;
use Spatie\LaravelSettings\Exceptions\InvalidSettingName;
use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;
use Spatie\LaravelSettings\Exceptions\SettingDoesNotExist;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\SettingsCasts\DateTimeZoneCast;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;

beforeEach(function () {
    $this->settingsMigrator = new SettingsMigrator(
        new DatabaseSettingsRepository(config('settings.repositories.database'))
    );
});


it('can add a setting', function () {
    $this->settingsMigrator->add('compliance.enabled', true);

    $this->assertDatabaseHasSetting('compliance.enabled', true);
});

it('cannot add the same setting twice', function () {
    $this->settingsMigrator->add('compliance.enabled', true);
    $this->settingsMigrator->add('compliance.enabled', true);
})->throws(SettingAlreadyExists::class);

it('can add a setting with the same name in different groups', function () {
    $this->settingsMigrator->add('compliance.enabled', true);
    $this->settingsMigrator->add('eduction.enabled', false);

    $this->assertDatabaseHasSetting('compliance.enabled', true);
    $this->assertDatabaseHasSetting('eduction.enabled', false);
});

it('cannot provide a invalid name when creating a setting', function () {
    $this->settingsMigrator->add('compliance', true);
})->throws(InvalidSettingName::class);

it('can rename a setting', function () {
    $this->settingsMigrator->add('compliance.enabled', true);

    $this->settingsMigrator->rename('compliance.enabled', 'eduction.enabled');

    $this->assertDatabaseHasSetting('eduction.enabled', true);
    $this->assertDatabaseDoesNotHaveSetting('compliance.enabled');
});

it('cannot rename a property that does not exist', function () {
    $this->settingsMigrator->rename('compliance.enabled', 'eduction.enabled');
})->throws(SettingDoesNotExist::class);

it('cannot rename a property to another existing property', function () {
    $this->settingsMigrator->add('eduction.enabled', true);
    $this->settingsMigrator->add('compliance.enabled', true);

    $this->settingsMigrator->rename('eduction.enabled', 'compliance.enabled');
})->throws(SettingAlreadyExists::class);

it('cannot rename from an invalid property', function () {
    $this->settingsMigrator->rename('eduction', 'compliance.enabled');
})->throws(InvalidSettingName::class);

it('cannot rename to an invalid property', function () {
    $this->settingsMigrator->add('eduction.enabled', true);

    $this->settingsMigrator->rename('eduction.enabled', 'compliance');
})->throws(InvalidSettingName::class);

it('can delete a setting', function () {
    $this->settingsMigrator->add('eduction.enabled', true);

    $this->settingsMigrator->delete('eduction.enabled');

    $this->assertDatabaseDoesNotHaveSetting('eduction.enabled');
});

it('cannot delete a setting that does not exist', function () {
    $this->settingsMigrator->delete('eduction.enabled');
})->throws(SettingDoesNotExist::class);

it('cannot delete a setting with an invalid property', function () {
    $this->settingsMigrator->delete('eductions');
})->throws(InvalidSettingName::class);

it('can update a setting', function () {
    $this->settingsMigrator->add('user.name', 'Brent Roose');

    $this->settingsMigrator->update('user.name', fn (string $name) => 'Ruben Van Assche');

    $this->assertDatabaseHasSetting('user.name', 'Ruben Van Assche');
});

it('cannot update a setting that does not exist', function () {
    $this->settingsMigrator->update('user.name', fn (string $name) => 'Ruben Van Assche');
})->throws(SettingDoesNotExist::class);

it('can perform migrations within a group', function () {
    $this->settingsMigrator->inGroup('test', function (SettingsBlueprint $blueprint): void {
        $blueprint->add('a', 'Alpha');
    });

    $this->assertDatabaseHasSetting('test.a', 'Alpha');
});

it('can add a setting encrypted', function () {
    $this->settingsMigrator->addEncrypted('compliance.enabled', true);

    $this->assertDatabaseHasEncryptedSetting('compliance.enabled', true);
});

it('can update an encrypted setting', function () {
    $this->settingsMigrator->addEncrypted('user.name', 'Brent Roose');

    $this->settingsMigrator->updateEncrypted('user.name', fn (string $name) => 'Ruben Van Assche');

    $this->assertDatabaseHasEncryptedSetting('user.name', 'Ruben Van Assche');
});

it('can encrypt a setting', function () {
    $this->settingsMigrator->add('user.name', 'Brent Roose');

    $this->settingsMigrator->encrypt('user.name');

    $this->assertDatabaseHasEncryptedSetting('user.name', 'Brent Roose');
});

it('can decrypt a setting', function () {
    $this->settingsMigrator->addEncrypted('user.name', 'Brent Roose');

    $this->settingsMigrator->decrypt('user.name');

    $this->assertDatabaseHasSetting('user.name', 'Brent Roose');
});

it('can cast on migration', function () {
    $this->setRegisteredSettings([
        DummySettings::class,
    ]);

    $timezoneMontreal = new DateTimeZone('America/Montreal');

    $this->settingsMigrator->add('dummy.nullable_date_time_zone', $timezoneMontreal);

    $this->assertDatabaseHasSetting('dummy.nullable_date_time_zone', (new DateTimeZoneCast(null))->set($timezoneMontreal));

    $timezoneBrussels = new DateTimeZone('Europe/Brussels');

    $this->settingsMigrator->update('dummy.nullable_date_time_zone', function (DateTimeZone $savedTimezone) use ($timezoneMontreal, $timezoneBrussels) {
        $this->assertEquals($timezoneMontreal, $savedTimezone);

        return $timezoneBrussels;
    });

    $this->assertDatabaseHasSetting('dummy.nullable_date_time_zone', (new DateTimeZoneCast(null))->set($timezoneBrussels));
});
