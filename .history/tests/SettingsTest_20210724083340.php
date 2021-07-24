<?php

namespace Spatie\LaravelSettings\Tests;

use Cache;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use DB;
use ErrorException;
use Event;
use Illuminate\Database\Events\SchemaLoaded;
use Illuminate\Support\Str;
use Spatie\LaravelSettings\Events\LoadingSettings;
use Spatie\LaravelSettings\Events\SavingSettings;
use Spatie\LaravelSettings\Events\SettingsLoaded;
use Spatie\LaravelSettings\Events\SettingsSaved;
use Spatie\LaravelSettings\Exceptions\MissingSettings;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\SettingsCache;
use Spatie\LaravelSettings\Tests\TestClasses\DummyDto;
use Spatie\LaravelSettings\Tests\TestClasses\DummyEncryptedSettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;
use Spatie\Snapshots\MatchesSnapshots;

class SettingsTest extends TestCase
{
    use MatchesSnapshots;

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

            $blueprint->add('date_time', $dateTime->format(DATE_ATOM));
            $blueprint->add('carbon', $carbon->toAtomString());
            $blueprint->add('nullable_date_time_zone', null);
        });

        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySettings $settings */
        $settings = resolve(DummySettings::class);

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
        $this->assertEquals($dateTime, $settings->date_time);
        $this->assertEquals($carbon, $settings->carbon);
    }

    /** @test */
    public function it_will_fail_loading_when_settings_are_missing(): void
    {
        $this->expectException(MissingSettings::class);

        resolve(DummySettings::class)->int;
    }

    /** @test */
    public function it_cannot_get_settings_that_do_not_exist()
    {
        $this->migrateDummySimpleSettings();

        $this->expectException(ErrorException::class);

        resolve(DummySimpleSettings::class)->band;
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
        $settings = resolve(DummySettings::class);

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
        $this->assertEquals($dateTime, $settings->date_time);
        $this->assertEquals($carbon, $settings->carbon);
        $this->assertNull($settings->nullable_date_time_zone);
    }

    /** @test */
    public function it_cannot_save_settings_that_do_not_exist(): void
    {
        $this->expectException(MissingSettings::class);

        $settings = resolve(DummySettings::class);

        $settings->fill([
            'string' => 'Brent',
            'bool' => true,
            'int' => 69,
            'array' => ['Bono', 'Adam', 'The Edge'],
            'nullable_string' => null,
            'dto' => new DummyDto(['name' => 'Rias']),
            'date_time' => new DateTimeImmutable(),
            'carbon' => Carbon::now(),
        ]);

        $settings->save();
    }

    /** @test */
    public function it_can_fake_settings()
    {
        $this->migrateDummySimpleSettings();

        DummySimpleSettings::fake([
            'description' => 'La vie en rose',
        ]);

        $settings = resolve(DummySimpleSettings::class);

        $this->assertEquals('Louis Armstrong', $settings->name);
        $this->assertEquals('La vie en rose', $settings->description);
    }

    /** @test */
    public function it_will_only_load_settings_from_the_repository_that_were_not_given()
    {
        $this->migrator->inGroup('dummy_simple', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('name', 'Rick Astley');
        });

        DummySimpleSettings::fake([
            'description' => 'Never gonna give you up',
        ]);

        $settings = resolve(DummySimpleSettings::class);

        $this->assertEquals('Rick Astley', $settings->name);
        $this->assertEquals('Never gonna give you up', $settings->description);
    }

    /** @test */
    public function it_can_lock_settings()
    {
        $this->migrateDummySimpleSettings();

        $settings = resolve(DummySimpleSettings::class);

        $settings->lock('description');

        $settings->name = 'Nina Simone';
        $settings->description = 'Sinnerman';

        $settings->save();

        $this->assertEquals('Nina Simone', $settings->name);
        $this->assertEquals('Hello Dolly', $settings->description);
        $this->assertEquals(['description'], $settings->getLockedProperties());
    }

    /** @test */
    public function locking_and_unlocking_settings_can_be_done_between_saves()
    {
        $this->migrateDummySimpleSettings();

        $settings = resolve(DummySimpleSettings::class);

        $settings->lock('name');
        $settings->name = 'Nina Simone';
        $settings->save();

        $this->assertEquals('Louis Armstrong', $settings->name);

        $settings->unlock('name');
        $settings->name = 'Nina Simone';
        $settings->save();

        $this->assertEquals('Nina Simone', $settings->name);
    }

    /** @test */
    public function it_can_fill_settings()
    {
        $this->migrateDummySimpleSettings();

        $settings = resolve(DummySimpleSettings::class)
            ->fill([
                'name' => 'Nina Simone',
            ])
            ->save();

        $this->assertEquals('Nina Simone', $settings->name);
        $this->assertEquals('Hello Dolly', $settings->description);
    }

    /** @test */
    public function it_can_save_individual_settings()
    {
        $this->migrateDummySimpleSettings();

        $settings = resolve(DummySimpleSettings::class);
        $settings->name = 'Nina Simone';
        $settings->save();

        $this->assertEquals('Nina Simone', $settings->name);
        $this->assertEquals('Hello Dolly', $settings->description);
    }

    /** @test */
    public function it_will_emit_an_event_when_loading_settings()
    {
        Event::fake([LoadingSettings::class]);

        $this->migrateDummySimpleSettings();

        resolve(DummySimpleSettings::class)->name;

        Event::assertDispatched(LoadingSettings::class, function (LoadingSettings $event) {
            $this->assertEquals(DummySimpleSettings::class, $event->settingsClass);
            $this->assertCount(2, $event->properties);

            return true;
        });
    }

    /** @test */
    public function it_can_overload_the_properties_when_loading()
    {
        $this->migrateDummySimpleSettings();

        Event::listen(LoadingSettings::class, function (LoadingSettings $event) {
            $event->properties->put('name', 'Nina Simone');
        });

        $this->assertEquals('Nina Simone', resolve(DummySimpleSettings::class)->name);
    }

    /** @test */
    public function it_will_emit_an_event_when_loaded_settings()
    {
        Event::fake([SettingsLoaded::class]);

        $this->migrateDummySimpleSettings();

        $settings = resolve(DummySimpleSettings::class);
        $settings->name;

        Event::assertDispatched(SettingsLoaded::class, function (SettingsLoaded $event) use ($settings) {
            $this->assertEquals($settings, $event->settings);

            return true;
        });
    }

    /** @test */
    public function it_will_emit_an_event_when_saving_settings()
    {
        Event::fake([SavingSettings::class]);

        $this->migrateDummySimpleSettings();

        $settings = resolve(DummySimpleSettings::class);
        $settings->name = 'New Name';
        $settings->save();

        Event::assertDispatched(SavingSettings::class, function (SavingSettings $event) use ($settings) {
            $this->assertCount(2, $event->properties);
            $this->assertEquals('New Name', $event->settings->name);
            $this->assertCount(2, $event->originalValues);
            $this->assertEquals('Louis Armstrong', $event->originalValues['name']);
            $this->assertEquals($settings, $event->settings);

            return true;
        });
    }

    /** @test */
    public function it_can_update_the_properties_in_an_event_when_saving()
    {
        $this->migrateDummySimpleSettings();

        Event::listen(SavingSettings::class, function (SavingSettings $event) {
            $event->properties->put('name', 'Nina Simone');
        });

        $settings = resolve(DummySimpleSettings::class)->save();

        $this->assertEquals('Nina Simone', $settings->name);
    }

    /** @test */
    public function it_will_emit_an_event_when_saved_settings()
    {
        Event::fake([SettingsSaved::class]);

        $this->migrateDummySimpleSettings();

        $settings = resolve(DummySimpleSettings::class)->save();

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
        $settings = resolve(DummyEncryptedSettings::class);

        $this->assertEquals('Hello', $settings->string);
        $this->assertNull($settings->nullable);
        $this->assertEquals($dateTime, $settings->cast);

        $updatedDateTime = new DateTime('16-05-2020 12:00:00');

        $settings->string = "Is is it me you're looking for";
        $settings->nullable = 'Not null anymore';
        $settings->cast = $updatedDateTime;

        $settings->save();

        $stringProperty = SettingsProperty::get('dummy_encrypted.string');
        $this->assertNotEquals("Is is it me you're looking for", $stringProperty);
        $this->assertEquals("Is is it me you're looking for", decrypt($stringProperty));

        $nullableProperty = SettingsProperty::get('dummy_encrypted.nullable');
        $this->assertNotEquals('Not null anymore', $nullableProperty);
        $this->assertEquals('Not null anymore', decrypt($nullableProperty));

        $castProperty = SettingsProperty::get('dummy_encrypted.cast');
        $this->assertNotEquals($updatedDateTime, $castProperty);
        $this->assertEquals($updatedDateTime->format(DATE_ATOM), decrypt($castProperty));
    }

    /** @test */
    public function it_will_remigrate_when_the_schema_was_dumped()
    {
        if (Str::startsWith(app()->version(), '7')) {
            $this->markTestSkipped('No support for dumping migrations in Laravel 7');
        }

        config()->set('settings.migrations_path', __DIR__ . '/Migrations');

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        $this
            ->assertDatabaseHas('migrations', ['migration' => '2018_11_21_091111_create_fake_settings'])
            ->assertDatabaseHas('migrations', ['migration' => '2018_11_21_091111_create_fake_table']);

        event(new SchemaLoaded(
            DB::connection(),
            'fake-path'
        ));

        $this
            ->assertDatabaseMissing('migrations', ['migration' => '2018_11_21_091111_create_fake_settings'])
            ->assertDatabaseHas('migrations', ['migration' => '2018_11_21_091111_create_fake_table']);
    }

    /**
     * @test
     * @environment-setup useEnabledCache
     */
    public function it_will_not_contact_the_repository_when_loading_cached_settings()
    {
        resolve(SettingsCache::class)->put(new DummySimpleSettings(
            ['name' => 'Louis Armstrong', 'description' => 'Hello dolly']
        ));

        $this->setRegisteredSettings([
            DummySimpleSettings::class,
        ]);

        DB::connection()->enableQueryLog();

        $name = resolve(DummySimpleSettings::class)->name;

        $log = DB::connection()->getQueryLog();

        $this->assertEquals('Louis Armstrong', $name);
        $this->assertCount(0, $log);
    }

    /**
     * @test
     * @environment-setup useEnabledCache
     */
    public function it_can_clear_a_settings_cache()
    {
        $this->setRegisteredSettings([
            DummySimpleSettings::class,
        ]);

        resolve(SettingsCache::class)->put(new DummySimpleSettings(
            ['name' => 'Louis Armstrong', 'description' => 'Hello dolly']
        ));

        cache()->put('other_cache_entry', 'do-not-delete-this');

        $this->assertTrue(cache()->has('settings.' . DummySimpleSettings::class));

        resolve(SettingsCache::class)->clear();

        $this->assertTrue(cache()->has('other_cache_entry'));
        $this->assertFalse(cache()->has('settings.' . DummySimpleSettings::class));
    }

    /** @test */
    public function it_will_load_settings_from_the_repository_when_a_serialized_setting_cannot_be_loaded()
    {
        $this->migrateDummySimpleSettings();

        Cache::put('settings.' . DummySimpleSettings::class, 'not-a-settings-class');

        $this->setRegisteredSettings([
            DummySimpleSettings::class,
        ]);

        DB::connection()->enableQueryLog();

        $name = resolve(DummySimpleSettings::class)->name;

        $log = DB::connection()->getQueryLog();

        $this->assertEquals('Louis Armstrong', $name);
        $this->assertCount(1, $log);
    }

    /** @test */
    public function it_can_serialize_settings()
    {
        $this->migrateDummySettings(CarbonImmutable::create('2020-05-16')->startOfDay());

        $settings = resolve(DummySettings::class);

        $serialized = serialize($settings);

        $unserializedSettings = unserialize($serialized);

        $this->assertEquals($settings->toArray(), $unserializedSettings->toArray());
    }

    /** @test */
    public function it_can_update_unserialized_settings()
    {
        $this->migrateDummySimpleSettings();

        $serialized = serialize(resolve(DummySimpleSettings::class));

        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings $settings */
        $settings = unserialize($serialized);

        $settings->name = 'Nina Simone';
        $settings->save();

        $this->assertDatabaseHasSetting('dummy_simple.name', 'Nina Simone');
    }

    /** @test */
    public function it_can_change_the_locks_on_unserialized_settings()
    {
        $this->migrateDummySimpleSettings();

        $serialized = serialize(resolve(DummySimpleSettings::class));

        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings $settings */
        $settings = unserialize($serialized);

        $settings->lock('name');

        $this->assertEquals(['name'], $settings->getLockedProperties());
    }

    /** @test */
    public function it_can_refresh_the_settings_properties()
    {
        $this->migrateDummySimpleSettings(
            'Louis Armstrong',
            'What a wonderful world'
        );

        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings $settings */
        $settings = resolve(DummySimpleSettings::class);

        $this->assertEquals('Louis Armstrong', $settings->name);
        $this->assertEquals('What a wonderful world', $settings->description);

        $repository = $settings->getRepository();

        $repository->updatePropertyPayload('dummy_simple', 'name', 'Rick Astley');
        $repository->updatePropertyPayload('dummy_simple', 'description', 'Never gonna give you up');

        $settings->refresh();

        $this->assertEquals('Rick Astley', $settings->name);
        $this->assertEquals('Never gonna give you up', $settings->description);
    }

    /** @test */
    public function it_can_refresh_the_settings_locks()
    {
        $this->migrateDummySimpleSettings();

        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings $settings */
        $settings = resolve(DummySimpleSettings::class);

        $this->assertEmpty($settings->getLockedProperties());

        $repository = $settings->getRepository();

        $repository->lockProperties('dummy_simple', ['name']);

        $settings->refresh();

        $this->assertEquals(['name'], $settings->getLockedProperties());
    }

    /** @test */
    public function it_can_check_if_a_property_has_been_set_if_properties_are_not_loaded()
    {
        $this->migrateDummySimpleSettings();

        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings $settings */
        $settings = app(DummySimpleSettings::class);
        var_dump($settings);die;
        $this->assertFalse(empty($settings->name));
        $this->assertTrue(empty($settings->non_existing));
    }
}
