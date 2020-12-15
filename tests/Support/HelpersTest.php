<?php

namespace Spatie\LaravelSettings\Tests\Support;

use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestCase;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Exceptions\MissingSettings;
use DateTimeZone;
use DateTimeImmutable;
use Carbon\Carbon;
use Spatie\LaravelSettings\Tests\TestClasses\DummyDto;

class HelpersTest extends TestCase
{
    private SettingsMigrator $migrator;

    protected function setUp(): void
    {
        parent::setup();

        $this->app['config']->set('settings.settings', [
            DummySettings::class,
        ]);

        $this->migrator = resolve(SettingsMigrator::class);

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

        resolve(SettingsContainer::class)->registerBindings();
    }

    /** @test */
    public function it_reads_value_using_setting_helper_function()
    {
        $dateTime = new DateTimeImmutable('16-05-1994 12:00:00');
        $carbon = new Carbon('16-05-1994 12:00:00');
        $dateTimeZone = new DateTimeZone('europe/brussels');

        $this->assertEquals('Ruben', setting('dummy', 'string'));
        $this->assertFalse(setting('dummy', 'bool'));
        $this->assertEquals(42, setting('dummy', 'int'));
        $this->assertEquals(['John', 'Ringo', 'Paul', 'George'], setting('dummy', 'array'));
        $this->assertNull(setting('dummy', 'nullable_string'));
        $this->assertNull(setting('dummy', 'default_string'));
        $this->assertEquals(new DummyDto(['name' => 'Freek']), setting('dummy', 'dto'));

        $this->assertEquals([
            new DummyDto(['name' => 'Seb']),
            new DummyDto(['name' => 'Adriaan']),
        ], setting('dummy', 'dto_array'));

        $this->assertNull(setting('dummy', 'dto_collection'));

        $this->assertEquals($dateTime, setting('dummy', 'date_time'));
        $this->assertEquals($carbon, setting('dummy', 'carbon'));
        $this->assertEquals($dateTimeZone, setting('dummy', 'nullable_date_time_zone'));
    }

    /** @test */
    public function it_throws_missing_setting_exception_if_group_setting_not_found()
    {
        $this->expectException(MissingSettings::class);
        setting('not_found', 'string');
    }

    /** @test */
    public function it_updates_single_value()
    {
        $this->assertInstanceOf(DummySettings::class, setting_update('dummy', 'string', 'New name'));
        $this->assertEquals('New name', setting('dummy', 'string'));

        $this->assertInstanceOf(DummySettings::class, setting_update('dummy', 'bool', true));
        $this->assertTrue(setting('dummy', 'bool'));

        $this->assertInstanceOf(DummySettings::class, setting_update('dummy', 'int', 1995));
        $this->assertEquals(1995, setting('dummy', 'int'));

        $this->assertInstanceOf(DummySettings::class, setting_update('dummy', 'array', ['black', 'white']));
        $this->assertEquals(['black', 'white'], setting('dummy', 'array'));

        $this->assertInstanceOf(DummySettings::class, setting_update('dummy', 'dto', new DummyDto(['name' => 'Amro'])));
        $this->assertEquals(new DummyDto(['name' => 'Amro']), setting('dummy', 'dto'));

        $this->assertInstanceOf(DummySettings::class, setting_update('dummy', 'dto_array', [
            new DummyDto(['name' => 'Khaled']),
            new DummyDto(['name' => 'Freek']),
        ]));
        $this->assertEquals([
            new DummyDto(['name' => 'Khaled']),
            new DummyDto(['name' => 'Freek']),
        ], setting('dummy', 'dto_array'));

        $dateTime = new DateTimeImmutable('15-12-2020 03:00:00');
        $this->assertInstanceOf(DummySettings::class, setting_update('dummy', 'date_time', $dateTime));
        $this->assertEquals($dateTime, setting('dummy', 'date_time'));

        $carbon = new Carbon('15-12-2020 03:00:00');
        $this->assertInstanceOf(DummySettings::class, setting_update('dummy', 'carbon', $carbon));
        $this->assertEquals($carbon, setting('dummy', 'carbon'));

        $dateTimeZone = new DateTimeZone('africa/cairo');
        $this->assertInstanceOf(DummySettings::class, setting_update('dummy', 'nullable_date_time_zone', $dateTimeZone));
        $this->assertEquals($dateTimeZone, setting('dummy', 'nullable_date_time_zone'));
    }

    /** @test */
    public function it_updates_multiple_values()
    {
        $dateTime = new DateTimeImmutable('26-08-1995 12:00:00');
        $carbon = new Carbon('26-08-1995 12:00:00');
        $dateTimeZone = new DateTimeZone('europe/london');

        $update_settings = setting_update('dummy', [
            'string' => 'Hello world',
            'bool' => false,
            'int' => 3100,
            'array' => ['orange', 'gray'],
            'dto' => new DummyDto(['name' => 'Freek']),
            'dto_array' => [
                new DummyDto(['name' => 'John']),
                new DummyDto(['name' => 'Paul']),
            ],
            'date_time' => $dateTime,
            'carbon' => $carbon,
            'nullable_date_time_zone' => $dateTimeZone,
        ]);

        $this->assertInstanceOf(DummySettings::class, $update_settings);
        $this->assertEquals('Hello world', setting('dummy', 'string'));
        $this->assertFalse(setting('dummy', 'bool'));
        $this->assertEquals(3100, setting('dummy', 'int'));
        $this->assertEquals(['orange', 'gray'], setting('dummy', 'array'));
        $this->assertEquals(new DummyDto(['name' => 'Freek']), setting('dummy', 'dto'));

        $this->assertEquals([
            new DummyDto(['name' => 'John']),
            new DummyDto(['name' => 'Paul']),
        ], setting('dummy', 'dto_array'));

        $this->assertEquals($dateTime, setting('dummy', 'date_time'));
        $this->assertEquals($carbon, setting('dummy', 'carbon'));
        $this->assertEquals($dateTimeZone, setting('dummy', 'nullable_date_time_zone'));
    }
}
