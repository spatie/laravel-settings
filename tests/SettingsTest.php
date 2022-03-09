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
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\LaravelSettings\Events\LoadingSettings;
use Spatie\LaravelSettings\Events\SavingSettings;
use Spatie\LaravelSettings\Events\SettingsLoaded;
use Spatie\LaravelSettings\Events\SettingsSaved;
use Spatie\LaravelSettings\Exceptions\MissingSettings;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsCache;
use Spatie\LaravelSettings\Tests\Fakes\FakeSettingsContainer;
use Spatie\LaravelSettings\Tests\TestClasses\DummyDto;
use Spatie\LaravelSettings\Tests\TestClasses\DummyEncryptedSettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummyIntEnum;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettingsWithCast;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummyStringEnum;
use Spatie\LaravelSettings\Tests\TestClasses\DummyUnitEnum;
use Spatie\Snapshots\MatchesSnapshots;

use function Pest\Laravel\{assertDatabaseHas, assertDatabaseMissing};

uses(MatchesSnapshots::class);

beforeEach(function () {
    $this->migrator = resolve(SettingsMigrator::class);
});

it('will handle loading settings correctly', function () {
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

    expect($settings)
        ->string->toEqual('Ruben')
        ->bool->toBeFalse()
        ->int->toEqual(42)
        ->array->toEqual(['John', 'Ringo', 'Paul', 'George'])
        ->nullable_string->toBeNull()
        ->dto->toEqual(new DummyDto(['name' => 'Freek']))
        ->dto_array->toEqual([
            new DummyDto(['name' => 'Seb']),
            new DummyDto(['name' => 'Adriaan']),
        ])
        ->date_time->toEqual($dateTime)
        ->carbon->toEqual($carbon);
});

it('will fail loading when settings are missing', function () {
    resolve(DummySettings::class)->int;
})->throws(MissingSettings::class);

it('cannot get settings that do not exist', function () {
    $this->migrateDummySimpleSettings();

    resolve(DummySimpleSettings::class)->band;
})->throws(ErrorException::class);

it('can save settings', function () {
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

    expect($settings)
        ->date_time->toEqual($dateTime)
        ->carbon->toEqual($carbon)
        ->nullable_date_time_zone->toBeNull();
});

it('cannot save settings that do not exist', function () {
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
})->throws(MissingSettings::class);

it('can fake settings', function () {
    $this->migrateDummySimpleSettings();

    DummySimpleSettings::fake([
        'description' => 'La vie en rose',
    ]);

    $settings = resolve(DummySimpleSettings::class);

    expect($settings)
        ->name->toEqual('Louis Armstrong')
        ->description->toEqual('La vie en rose');
});

it('will only load settings from the repository that were not given', function () {
    $this->migrator->inGroup('dummy_simple', function (SettingsBlueprint $blueprint): void {
        $blueprint->add('name', 'Rick Astley');
    });

    DummySimpleSettings::fake([
        'description' => 'Never gonna give you up',
    ]);

    $settings = resolve(DummySimpleSettings::class);

    expect($settings)
        ->name->toEqual('Rick Astley')
        ->description->toEqual('Never gonna give you up');
});

it('can lock settings', function () {
    $this->migrateDummySimpleSettings();

    $settings = resolve(DummySimpleSettings::class);

    $settings->lock('description');

    $settings->name = 'Nina Simone';
    $settings->description = 'Sinnerman';

    $settings->save();

    expect($settings)
        ->name->toEqual('Nina Simone')
        ->description->toEqual('Hello Dolly')
        ->getLockedProperties()->toEqual(['description']);
});

it('locking and unlocking settings can be done between saves', function () {
    $this->migrateDummySimpleSettings();

    $settings = resolve(DummySimpleSettings::class);

    $settings->lock('name');
    $settings->name = 'Nina Simone';
    $settings->save();

    expect($settings)->name->toEqual('Louis Armstrong');

    $settings->unlock('name');
    $settings->name = 'Nina Simone';
    $settings->save();

    expect($settings)->name->toEqual('Nina Simone');
});

it('can fill settings', function () {
    $this->migrateDummySimpleSettings();

    $settings = resolve(DummySimpleSettings::class)
        ->fill([
            'name' => 'Nina Simone',
        ])
        ->save();

    expect($settings)
        ->name->toEqual('Nina Simone')
        ->description->toEqual('Hello Dolly');
});

it('can save individual settings', function () {
    $this->migrateDummySimpleSettings();

    $settings = resolve(DummySimpleSettings::class);
    $settings->name = 'Nina Simone';
    $settings->save();

    expect($settings)
        ->name->toEqual('Nina Simone')
        ->description->toEqual('Hello Dolly');
});

it('will emit an event when loading settings', function () {
    Event::fake([LoadingSettings::class]);

    $this->migrateDummySimpleSettings();

    resolve(DummySimpleSettings::class)->name;

    Event::assertDispatched(LoadingSettings::class, function (LoadingSettings $event) {
        expect($event)
            ->settingsClass->toEqual(DummySimpleSettings::class)
            ->properties->toHaveCount(2);

        return true;
    });
});

it('can overload the properties when loading', function () {
    $this->migrateDummySimpleSettings();

    Event::listen(LoadingSettings::class, function (LoadingSettings $event) {
        $event->properties->put('name', 'Nina Simone');
    });

    expect(resolve(DummySimpleSettings::class)->name)->toEqual('Nina Simone');
});

it('will emit an event when loaded settings', function () {
    Event::fake([SettingsLoaded::class]);

    $this->migrateDummySimpleSettings();

    $settings = resolve(DummySimpleSettings::class);
    $settings->name;

    Event::assertDispatched(SettingsLoaded::class, function (SettingsLoaded $event) use ($settings) {
        expect($event->settings)->toEqual($settings);

        return true;
    });
});

it('will emit an event when saving settings', function () {
    Event::fake([SavingSettings::class]);

    $this->migrateDummySimpleSettings();

    $settings = resolve(DummySimpleSettings::class);
    $settings->name = 'New Name';
    $settings->save();

    Event::assertDispatched(SavingSettings::class, function (SavingSettings $event) use ($settings) {
        expect($event)
            ->properties->toHaveCount(2)
            ->settings->name->toEqual('New Name')
            ->originalValues->toHaveCount(2)
            ->originalValues->toContain('Louis Armstrong')
            ->settings->toEqual($settings);

        return true;
    });
});

it('can update the properties in an event when saving', function () {
    $this->migrateDummySimpleSettings();

    Event::listen(SavingSettings::class, function (SavingSettings $event) {
        $event->properties->put('name', 'Nina Simone');
    });

    $settings = resolve(DummySimpleSettings::class)->save();

    expect($settings->name)->toEqual('Nina Simone');
});

it('will emit an event when saved settings', function () {
    Event::fake([SettingsSaved::class]);

    $this->migrateDummySimpleSettings();

    $settings = resolve(DummySimpleSettings::class)->save();

    Event::assertDispatched(SettingsSaved::class, function (SettingsSaved $event) use ($settings) {
        expect($event->settings)->toEqual($settings);

        return true;
    });
});

it('can encrypt settings', function () {
    $dateTime = new DateTime('16-05-1994 12:00:00');

    $this->migrator->inGroup('dummy_encrypted', function (SettingsBlueprint $blueprint) use ($dateTime): void {
        $blueprint->add('string', 'Hello', true);
        $blueprint->add('nullable', null, true);
        $blueprint->add('cast', $dateTime->format(DATE_ATOM), true);
    });

    $stringProperty = SettingsProperty::get('dummy_encrypted.string');

    expect('Hello')
        ->not->toEqual($stringProperty)
        ->toEqual(decrypt($stringProperty));

    $nullableProperty = SettingsProperty::get('dummy_encrypted.nullable');

    expect($nullableProperty)->toBeNull();

    $castProperty = SettingsProperty::get('dummy_encrypted.cast');

    expect($dateTime)
        ->not->toEqual($castProperty)
        ->format(DATE_ATOM)
        ->toEqual(decrypt($castProperty));

    /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummyEncryptedSettings $settings */
    $settings = resolve(DummyEncryptedSettings::class);

    expect($settings)
        ->string->toEqual('Hello')
        ->nullable->toBeNull()
        ->cast->toEqual($dateTime);

    $updatedDateTime = new DateTime('16-05-2020 12:00:00');

    $settings->string = "Is is it me you're looking for";
    $settings->nullable = 'Not null anymore';
    $settings->cast = $updatedDateTime;

    $settings->save();

    $stringProperty = SettingsProperty::get('dummy_encrypted.string');

    expect("Is is it me you're looking for")
        ->not->toEqual($stringProperty)
        ->toEqual(decrypt($stringProperty));

    $nullableProperty = SettingsProperty::get('dummy_encrypted.nullable');

    expect('Not null anymore')
        ->not->toEqual($nullableProperty)
        ->toEqual(decrypt($nullableProperty));

    $castProperty = SettingsProperty::get('dummy_encrypted.cast');

    expect($updatedDateTime)
        ->not->toEqual($castProperty)
        ->format(DATE_ATOM)
        ->toEqual(decrypt($castProperty));
});

it('will remigrate when the schema was dumped', function () {
    config()->set('settings.migrations_paths', [__DIR__ . '/Migrations']);

    $this->loadMigrationsFrom(__DIR__ . '/Migrations');

    assertDatabaseHas('migrations', ['migration' => '2018_11_21_091111_create_fake_settings']);
    assertDatabaseHas('migrations', ['migration' => '2018_11_21_091111_create_fake_table']);

    event(new SchemaLoaded(
        DB::connection(),
        'fake-path'
    ));

    assertDatabaseMissing('migrations', ['migration' => '2018_11_21_091111_create_fake_settings']);
    assertDatabaseHas('migrations', ['migration' => '2018_11_21_091111_create_fake_table']);
})->skip(fn () => Str::startsWith(app()->version(), '7'), 'No support for dumping migrations in Laravel 7');

it('will not contact the repository when loading cached settings', function () {
    useEnabledCache($this->app);

    resolve(SettingsCache::class)->put(new DummySimpleSettings(
        ['name' => 'Louis Armstrong', 'description' => 'Hello dolly']
    ));

    $this->setRegisteredSettings([
        DummySimpleSettings::class,
    ]);

    DB::connection()->enableQueryLog();

    $name = resolve(DummySimpleSettings::class)->name;

    $log = DB::connection()->getQueryLog();

    expect($name)->toEqual('Louis Armstrong');
    expect($log)->toHaveCount(0);
});

it('can clear a settings cache', function () {
    useEnabledCache($this->app);

    $this->setRegisteredSettings([
        DummySimpleSettings::class,
    ]);

    resolve(SettingsCache::class)->put(new DummySimpleSettings(
        ['name' => 'Louis Armstrong', 'description' => 'Hello dolly']
    ));

    cache()->put('other_cache_entry', 'do-not-delete-this');

    expect(cache()->has('settings.' . DummySimpleSettings::class))->toBeTrue();

    resolve(SettingsCache::class)->clear();

    expect(cache()->has('other_cache_entry'))->toBeTrue();
    expect(cache()->has('settings.' . DummySimpleSettings::class))->toBeFalse();
});

it('will load settings from the repository when a serialized setting cannot be loaded', function () {
    $this->migrateDummySimpleSettings();

    Cache::put('settings.' . DummySimpleSettings::class, 'not-a-settings-class');

    $this->setRegisteredSettings([
        DummySimpleSettings::class,
    ]);

    DB::connection()->enableQueryLog();

    $name = resolve(DummySimpleSettings::class)->name;

    $log = DB::connection()->getQueryLog();

    expect($name)->toEqual('Louis Armstrong');
    expect($log)->toHaveCount(1);
});

it('can serialize settings', function () {
    $this->migrateDummySettings(CarbonImmutable::create('2020-05-16')->startOfDay());

    $settings = resolve(DummySettings::class);

    $serialized = serialize($settings);

    $unserializedSettings = unserialize($serialized);

    expect($settings->toArray())->toEqual($unserializedSettings->toArray());
});

it('can update unserialized settings', function () {
    $this->migrateDummySimpleSettings();

    $serialized = serialize(resolve(DummySimpleSettings::class));

    /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings $settings */
    $settings = unserialize($serialized);

    $settings->name = 'Nina Simone';
    $settings->save();

    $this->assertDatabaseHasSetting('dummy_simple.name', 'Nina Simone');
});

it('can change the locks on unserialized settings', function () {
    $this->migrateDummySimpleSettings();

    $serialized = serialize(resolve(DummySimpleSettings::class));

    /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings $settings */
    $settings = unserialize($serialized);

    $settings->lock('name');

    expect($settings->getLockedProperties())->toEqual(['name']);
});

it('can refresh the settings properties', function () {
    $this->migrateDummySimpleSettings(
        'Louis Armstrong',
        'What a wonderful world'
    );

    /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings $settings */
    $settings = resolve(DummySimpleSettings::class);

    expect($settings)
        ->name->toEqual('Louis Armstrong')
        ->description->toEqual('What a wonderful world');

    $repository = $settings->getRepository();

    $repository->updatePropertyPayload('dummy_simple', 'name', 'Rick Astley');
    $repository->updatePropertyPayload('dummy_simple', 'description', 'Never gonna give you up');

    $settings->refresh();

    expect($settings)
        ->name->toEqual('Rick Astley')
        ->description->toEqual('Never gonna give you up');
});

it('can refresh the settings locks', function () {
    $this->migrateDummySimpleSettings();

    /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings $settings */
    $settings = resolve(DummySimpleSettings::class);

    $this->assertEmpty($settings->getLockedProperties());

    $repository = $settings->getRepository();

    $repository->lockProperties('dummy_simple', ['name']);

    $settings->refresh();

    expect($settings->getLockedProperties())->toEqual(['name']);
});

it('can check if a setting is locked or unlocked', function () {
    $this->migrateDummySimpleSettings();

    /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings $settings */
    $settings = resolve(DummySimpleSettings::class);

    expect($settings->getLockedProperties())->toBeEmpty();

    $repository = $settings->getRepository();

    $repository->lockProperties('dummy_simple', ['name']);

    $settings->refresh();

    expect($settings)
        ->getLockedProperties()->toEqual(['name'])
        ->isLocked('name')->toBeTrue()
        ->isUnlocked('name')->toBeFalse();
});

it('can check if a property has been set if properties are not loaded', function () {
    $this->migrateDummySimpleSettings();

    /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings $settings */
    $settings = resolve(DummySimpleSettings::class);

    expect(empty($settings->name))
        ->toBeFalse()
        ->and(empty($settings->non_existing))
        ->toBeTrue();
});

it('has support for native enums', function () {
    $this->skipIfPHPLowerThen('8.1');

    $settings = new class extends Settings {
        public DummyUnitEnum $unit;

        public DummyIntEnum $int;

        public DummyStringEnum $string;

        public static function group(): string
        {
            return 'enums';
        }
    };

    FakeSettingsContainer::setUp()->addSettingsClass(get_class($settings));

    resolve(SettingsMigrator::class)->inGroup('enums', function (SettingsBlueprint $blueprint): void {
        $blueprint->add('unit', DummyUnitEnum::Y);
        $blueprint->add('int', DummyIntEnum::THREE);
        $blueprint->add('string', DummyStringEnum::ARCHIVED);
    });

    expect($settings)
        ->unit->toEqual(DummyUnitEnum::Y)->toBeInstanceOf(DummyUnitEnum::class)
        ->int->toEqual(DummyIntEnum::THREE)->toBeInstanceOf(DummyIntEnum::class)
        ->string->toEqual(DummyStringEnum::ARCHIVED)->toBeInstanceOf(DummyStringEnum::class);
});

it('supports complex types with casts when caching Settings', function () {
    useEnabledCache($this->app);

    $collection = collect(['A', 'B', 'C']);

    resolve(SettingsMigrator::class)->inGroup(DummySettingsWithCast::group(), function (SettingsBlueprint $blueprint) use ($collection): void {
        $blueprint->add('collection', $collection);
    });

    resolve(SettingsCache::class)->put(resolve(DummySettingsWithCast::class));

    /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySettingsWithCast $cachedSettings */
    $cachedSettings = resolve(SettingsCache::class)->get(DummySettingsWithCast::class);

    expect($cachedSettings)
        ->collection
        ->toEqual($collection)
        ->toBeInstanceOf(Collection::class);
});
