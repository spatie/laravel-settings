<?php

namespace Spatie\LaravelSettings\Tests;

use Carbon\Carbon;
use DateTimeImmutable;
use Exception;
use Spatie\LaravelSettings\Exceptions\MissingSettingsException;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\SettingsCasts\CarbonCast;
use Spatie\LaravelSettings\SettingsCasts\DateTimeImmutableCast;
use Spatie\LaravelSettings\SettingsConfig;
use Spatie\LaravelSettings\SettingsMapper;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;
use Spatie\LaravelSettings\Tests\TestClasses\DummyDto;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;

class SettingsTest extends TestCase
{
    private SettingsMigrator $migrator;

    private SettingsMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = resolve(SettingsMigrator::class);

        $this->mapper = new SettingsMapper(
            new DatabaseSettingsRepository(config('settings.repositories.database')),
            new SettingsConfig(),
        );
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
            $blueprint->add('dto_collection', [
                ['name' => 'Seb'],
                ['name' => 'Adriaan'],
            ]);
            $blueprint->add('date_time', (new DateTimeImmutableCast())->set($dateTime));
            $blueprint->add('carbon', (new CarbonCast())->set($carbon));
        });

        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySettings $settings */
        $settings = $this->mapper->load(DummySettings::class);

        $this->assertEquals('Ruben', $settings->string);
        $this->assertEquals(false, $settings->bool);
        $this->assertEquals(42, $settings->int);
        $this->assertEquals(['John', 'Ringo', 'Paul', 'George'], $settings->array);
        $this->assertEquals(null, $settings->nullable_string);
        $this->assertEquals('default', $settings->default_string);
        $this->assertEquals(new DummyDto(['name' => 'Freek']), $settings->dto);
        $this->assertEquals([
            new DummyDto(['name' => 'Seb']),
            new DummyDto(['name' => 'Adriaan']),
        ], $settings->dto_collection);
        $this->assertEquals($dateTime, $settings->date_time);
        $this->assertEquals($carbon, $settings->carbon);
    }

    /** @test */
    public function it_will_fail_loading_when_settings_are_missing(): void
    {
        $this->expectException(MissingSettingsException::class);

        $this->mapper->load(DummySettings::class);
    }

    /** @test */
    public function it_cannot_load_a_non_settings_class(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/which is not a Settings DTO/');

        $this->mapper->load(DummyDto::class);
    }

    /** @test */
    public function it_can_save_settings(): void
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
            $blueprint->add('dto_collection', [
                ['name' => 'Seb'],
                ['name' => 'Adriaan'],
            ]);
            $blueprint->add('date_time', (new DateTimeImmutableCast())->set($dateTime));
            $blueprint->add('carbon', (new CarbonCast())->set($carbon));
        });

        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummySettings $settings */
        $settings = $this->mapper->load(DummySettings::class);

        $settings->fill([
            'string' => 'Brent',
            'bool' => true,
            'int' => 69,
            'array' => ['Bono', 'Adam', 'The Edge'],
            'nullable_string' => null,
            'default_string' => 'another',
            'dto' => new DummyDto(['name' => 'Rias']),
            'dto_collection' => [
                new DummyDto(['name' => 'Wouter']),
                new DummyDto(['name' => 'Jef']),
            ],
        ]);

        $settings->save();

        $this->assertDatabaseHasSetting('dummy.string', 'Brent');
        $this->assertDatabaseHasSetting('dummy.bool', true);
        $this->assertDatabaseHasSetting('dummy.int', 69);
        $this->assertDatabaseHasSetting('dummy.array', ['Bono', 'Adam', 'The Edge']);
        $this->assertDatabaseHasSetting('dummy.nullable_string', null);
        $this->assertDatabaseHasSetting('dummy.default_string', 'another');
        $this->assertDatabaseHasSetting('dummy.dto', ['name' => 'Rias']);
        $this->assertDatabaseHasSetting('dummy.dto_collection', [
            ['name' => 'Wouter'],
            ['name' => 'Jef'],
        ]);
        $this->assertEquals($dateTime, $settings->date_time);
        $this->assertEquals($carbon, $settings->carbon);
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
            'default_string' => 'another',
            'dto' => ['name' => 'Rias'],
            'dto_collection' => [
                ['name' => 'Wouter'],
                ['name' => 'Jef'],
            ],
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

        $settings = $this->mapper->load(DummySimpleSettings::class);

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

        $settings = $this->mapper->load(DummySimpleSettings::class);

        $settings->fill([
            'name' => 'Nina Simone',
        ]);

        $settings->save();

        $this->assertEquals('Nina Simone', $settings->name);
        $this->assertEquals('Hello Dolly', $settings->description);
    }
}
