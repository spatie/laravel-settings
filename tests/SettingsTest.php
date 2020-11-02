<?php

namespace Spatie\LaravelSettings\Tests;

use Carbon\Carbon;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Event;
use Exception;
use Spatie\LaravelSettings\Events\SettingsLoaded;
use Spatie\LaravelSettings\Events\LoadingSettings;
use Spatie\LaravelSettings\Events\SettingsSaved;
use Spatie\LaravelSettings\Events\SavingSettings;
use Spatie\LaravelSettings\Exceptions\MissingSettingsException;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\SettingsMapper;
use Spatie\LaravelSettings\Tests\TestClasses\DummyDto;
use Spatie\LaravelSettings\Tests\TestClasses\DummyEncryptedSettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;

class SettingsTest extends TestCase
{
    private SettingsMigrator $migrator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = resolve(SettingsMigrator::class);
    }

    /** @test */
    public function it_will_handle_loading_settings_correctly(): void
    {
        $dateTime = new DateTimeImmutable('16-05-1994 12:00:00');
        $carbon = new Carbon('16-05-1994 12:00:00');

        $this->migrator->inGroup('dummy', function (SettingsBlueprint $blueprint) use ($carbon, $dateTime): void {
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
//            $blueprint->add('dto_collection', [
//                ['name' => 'Seb'],
//                ['name' => 'Adriaan'],
//            ]);
            $blueprint->add('date_time', $dateTime->format(DATE_ATOM));
            $blueprint->add('carbon', $carbon->toAtomString());
            $blueprint->add('nullable_date_time_zone', null);
        });

        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySettings $settings */
        $settings = SettingsMapper::create(DummySettings::class)->load();

        $this->assertEquals('Ruben', $settings->string);
        $this->assertEquals(false, $settings->bool);
        $this->assertEquals(42, $settings->int);
        $this->assertEquals(['John', 'Ringo', 'Paul', 'George'], $settings->array);
        $this->assertEquals(null, $settings->nullable_string);
        $this->assertEquals(new DummyDto(['name' => 'Freek']), $settings->dto);
        $this->assertEquals([
            new DummyDto(['name' => 'Seb']),
            new DummyDto(['name' => 'Adriaan']),
        ], $settings->dto_array);
//        $this->assertEquals([
//            new DummyDto(['name' => 'Seb']),
//            new DummyDto(['name' => 'Adriaan']),
//        ], $settings->dto_collection);
        $this->assertEquals($dateTime, $settings->date_time);
        $this->assertEquals($carbon, $settings->carbon);
    }

    /** @test */
    public function it_will_fail_loading_when_settings_are_missing(): void
    {
        $this->expectException(MissingSettingsException::class);

        SettingsMapper::create(DummySettings::class)->load();
    }

    /** @test */
    public function it_cannot_load_a_non_settings_class(): void
    {
        $this->expectException(Exception::class);

        SettingsMapper::create(DummyDto::class)->load();
    }

    /** @test */
    public function it_can_save_settings(): void
    {
        $dateTime = new DateTimeImmutable('16-05-1994 12:00:00');
        $carbon = new Carbon('16-05-1994 12:00:00');
        $dateTimeZone = new DateTimeZone('europe/brussels');

        $this->migrator->inGroup('dummy', function (SettingsBlueprint $blueprint) use ($dateTimeZone, $carbon, $dateTime): void {
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
            $blueprint->add('dto_collection', [
                ['name' => 'Seb'],
                ['name' => 'Adriaan'],
            ]);
            $blueprint->add('date_time', $dateTime->format(DATE_ATOM));
            $blueprint->add('carbon', $carbon->toAtomString());
            $blueprint->add('nullable_date_time_zone', $dateTimeZone->getName());
        });

        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySettings $settings */
        $settings = SettingsMapper::create(DummySettings::class)->load();

        $settings->fill([
            'string' => 'Brent',
            'bool' => true,
            'int' => 69,
            'array' => ['Bono', 'Adam', 'The Edge'],
            'nullable_string' => null,
            'default_string' => 'another',
            'dto' => new DummyDto(['name' => 'Rias']),
            'dto_array' => [
                new DummyDto(['name' => 'Wouter']),
                new DummyDto(['name' => 'Jef']),
            ],
            'dto_collection' => [
                new DummyDto(['name' => 'Wouter']),
                new DummyDto(['name' => 'Jef']),
            ],
            'nullable_date_time_zone' => null,
        ]);

        $settings->save();

        $this->assertDatabaseHasSetting('dummy.string', 'Brent');
        $this->assertDatabaseHasSetting('dummy.bool', true);
        $this->assertDatabaseHasSetting('dummy.int', 69);
        $this->assertDatabaseHasSetting('dummy.array', ['Bono', 'Adam', 'The Edge']);
        $this->assertDatabaseHasSetting('dummy.nullable_string', null);
        $this->assertDatabaseHasSetting('dummy.dto', ['name' => 'Rias']);
        $this->assertDatabaseHasSetting('dummy.dto_array', [
            ['name' => 'Wouter'],
            ['name' => 'Jef'],
        ]);
//        $this->assertDatabaseHasSetting('dummy.dto_collection', [
//            ['name' => 'Wouter'],
//            ['name' => 'Jef'],
//        ]);
        $this->assertEquals($dateTime, $settings->date_time);
        $this->assertEquals($carbon, $settings->carbon);
        $this->assertNull($settings->nullable_date_time_zone);
    }

    /** @test */
    public function it_cannot_save_settings_that_do_not_exist(): void
    {
        $this->expectException(MissingSettingsException::class);

        $settings = new DummySettings([
            'string' => 'Brent',
            'bool' => true,
            'int' => 69,
            'array' => ['Bono', 'Adam', 'The Edge'],
            'nullable_string' => null,
            'dto' => new DummyDto(['name' => 'Rias']),
//            'dto' => ['name' => 'Rias'],
//            'dto_collection' => [
//                ['name' => 'Wouter'],
//                ['name' => 'Jef'],
//            ],
            'date_time' => new DateTimeImmutable(),
            'carbon' => Carbon::now(),
        ]);

        $settings->save();
    }

    /** @test */
    public function it_can_fake_settings()
    {
        $this->migrator->inGroup('dummy_simple', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('name', 'Rick Astley');
            $blueprint->add('description', 'Never gonna give you up!');
        });

        DummySimpleSettings::fake([
            'description' => 'Together forever',
        ]);

        $settings = resolve(DummySimpleSettings::class);

        $this->assertEquals('Rick Astley', $settings->name);
        $this->assertEquals('Together forever', $settings->description);
    }

    /** @test */
    public function it_can_lock_settings()
    {
        $this->migrator->inGroup('dummy_simple', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('name', 'Louis Armstrong');
            $blueprint->add('description', 'Hello Dolly');
        });

        $settings = SettingsMapper::create(DummySimpleSettings::class)->load();

        $settings->lock('description');

        $settings->name = 'Nina Simone';
        $settings->description = 'Sinnerman';

        $settings->save();

        $this->assertEquals('Nina Simone', $settings->name);
        $this->assertEquals('Hello Dolly', $settings->description);
    }

    /** @test */
    public function it_can_fill_settings()
    {
        $this->migrator->inGroup('dummy_simple', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('name', 'Louis Armstrong');
            $blueprint->add('description', 'Hello Dolly');
        });

        $settings = SettingsMapper::create(DummySimpleSettings::class)->load();

        $settings->fill([
            'name' => 'Nina Simone',
        ]);

        $settings->save();

        $this->assertEquals('Nina Simone', $settings->name);
        $this->assertEquals('Hello Dolly', $settings->description);
    }

    /** @test */
    public function it_will_emit_an_event_when_loading_settings()
    {
        Event::fake([LoadingSettings::class]);

        $this->migrator->inGroup('dummy_simple', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('name', 'Louis Armstrong');
            $blueprint->add('description', 'Hello Dolly');
        });

        SettingsMapper::create(DummySimpleSettings::class)->load();

        Event::assertDispatched(LoadingSettings::class, function (LoadingSettings $event) {
            $this->assertEquals(DummySimpleSettings::class, $event->settingsClass);
            $this->assertCount(2, $event->properties);

            return true;
        });
    }

    /** @test */
    public function it_will_emit_an_event_when_loaded_settings()
    {
        Event::fake([SettingsLoaded::class]);

        $this->migrator->inGroup('dummy_simple', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('name', 'Louis Armstrong');
            $blueprint->add('description', 'Hello Dolly');
        });

        $settings = SettingsMapper::create(DummySimpleSettings::class)->load();

        Event::assertDispatched(SettingsLoaded::class, function (SettingsLoaded $event) use ($settings) {
            $this->assertEquals($settings, $event->settings);

            return true;
        });
    }

    /** @test */
    public function it_will_emit_an_event_when_saving_settings()
    {
        Event::fake([SavingSettings::class]);

        $this->migrator->inGroup('dummy_simple', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('name', 'Louis Armstrong');
            $blueprint->add('description', 'Hello Dolly');
        });

        $settings = SettingsMapper::create(DummySimpleSettings::class)
            ->load()
            ->save();

        Event::assertDispatched(SavingSettings::class, function (SavingSettings $event) use ($settings) {
            $this->assertEquals(DummySimpleSettings::class, $event->settingsClass);
            $this->assertCount(2, $event->properties);
            $this->assertEquals($settings, $event->settings);

            return true;
        });
    }

    /** @test */
    public function it_will_emit_an_event_when_saved_settings()
    {
        Event::fake([SettingsSaved::class]);

        $this->migrator->inGroup('dummy_simple', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('name', 'Louis Armstrong');
            $blueprint->add('description', 'Hello Dolly');
        });

        $settings = SettingsMapper::create(DummySimpleSettings::class)
            ->load()
            ->save();

        Event::assertDispatched(SettingsSaved::class, function (SettingsSaved $event) use ($settings) {
            $this->assertEquals($settings, $event->settings);

            return true;
        });
    }

    /** @test */
    public function it_can_encrypt_settings()
    {
        $dateTime = new DateTime('16-05-1994 12:00:00');

        $this->migrator->inGroup('dummy_encrypted', function (SettingsBlueprint $blueprint) use ($dateTime): void {
            $blueprint->add('string', 'Hello', true);
            $blueprint->add('nullable', null, true);
            $blueprint->add('cast', $dateTime->format(DATE_ATOM), true);
        });

        $stringProperty = SettingsProperty::get('dummy_encrypted.string');
        $this->assertNotEquals('Hello', $stringProperty);
        $this->assertEquals('Hello', decrypt($stringProperty));

        $nullableProperty = SettingsProperty::get('dummy_encrypted.nullable');
        $this->assertNull($nullableProperty);

        $castProperty = SettingsProperty::get('dummy_encrypted.cast');
        $this->assertNotEquals($dateTime, $castProperty);
        $this->assertEquals($dateTime->format(DATE_ATOM), decrypt($castProperty));

        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummyEncryptedSettings $settings */
        $settings = SettingsMapper::create(DummyEncryptedSettings::class)->load();

        $this->assertEquals('Hello', $settings->string);
        $this->assertNull($settings->nullable);
        $this->assertEquals($dateTime, $settings->cast);

        $updatedDateTime = new DateTime('16-05-2020 12:00:00');

        $settings->string = 'Is is it me you\'re looking for';
        $settings->nullable = 'Not null anymore';
        $settings->cast = $updatedDateTime;

        $settings->save();

        $stringProperty = SettingsProperty::get('dummy_encrypted.string');
        $this->assertNotEquals('Is is it me you\'re looking for', $stringProperty);
        $this->assertEquals('Is is it me you\'re looking for', decrypt($stringProperty));

        $nullableProperty = SettingsProperty::get('dummy_encrypted.nullable');
        $this->assertNotEquals('Not null anymore', $nullableProperty);
        $this->assertEquals('Not null anymore', decrypt($nullableProperty));

        $castProperty = SettingsProperty::get('dummy_encrypted.cast');
        $this->assertNotEquals($updatedDateTime, $castProperty);
        $this->assertEquals($updatedDateTime->format(DATE_ATOM), decrypt($castProperty));
    }
}
