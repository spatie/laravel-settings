<?php

namespace Tests\Support\Settings;

use App\Support\Settings\Exceptions\MissingSettingsException;
use App\Support\Settings\SettingsConnection\DatabaseSettingsConnection;
use App\Support\Settings\SettingsMapper;
use App\Support\Settings\SettingsMigrator;
use Exception;
use Tests\TestClasses\Settings\DummyDto;
use Tests\TestClasses\Settings\DummySettings;

class SettingsTest extends TestCase
{
    private SettingsMigrator $migrator;

    private SettingsMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = resolve(SettingsMigrator::class);

        $this->mapper = new SettingsMapper(
            new DatabaseSettingsConnection()
        );
    }

    /** @test */
    public function it_will_handle_loading_settings_correctly(): void
    {
        $this->migrator->addMany('dummy', [
            'string' => 'Ruben',
            'bool' => false,
            'int' => 42,
            'array' => ['John', 'Ringo', 'Paul', 'George'],
            'nullable_string' => null,
            'default_string' => null,
            'dto' => ['name' => 'Freek'],
            'dto_collection' => [
                ['name' => 'Seb'],
                ['name' => 'Adriaan'],
            ],
        ]);

        /** @var \Tests\TestClasses\Settings\DummySettings $settings */
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
    }

    /** @test */
    public function it_will_fails_loading_when_settings_are_missing(): void
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
        $this->migrator->addMany('dummy', [
            'string' => 'Ruben',
            'bool' => false,
            'int' => 42,
            'array' => ['John', 'Ringo', 'Paul', 'George'],
            'nullable_string' => null,
            'default_string' => null,
            'dto' => ['name' => 'Freek'],
            'dto_collection' => [
                ['name' => 'Seb'],
                ['name' => 'Adriaan'],
            ],
        ]);

        /** @var \Tests\TestClasses\Settings\DummySettings $settings */
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
        ]);

        $settings->save();
    }
}
