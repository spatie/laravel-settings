<?php

namespace Spatie\LaravelSettings\Tests;

use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;
use Spatie\LaravelSettings\Tests\TestClasses\FakeAction;

class SettingsContainerTest extends TestCase
{
    private SettingsMigrator $migrator;

    protected function setUp() : void
    {
        parent::setUp();

        $this->setRegisteredSettings([
            DummySimpleSettings::class,
        ]);

        $this->migrator = resolve(SettingsMigrator::class);

        $this->migrator->inGroup('dummy_simple', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('name', 'Louis Armstrong');
            $blueprint->add('description', 'Hello Dolly');
        });

        resolve(SettingsContainer::class)->registerBindings();
    }

    /** @test */
    public function it_will_not_fetch_data_from_the_repository_twice()
    {
        DB::connection()->enableQueryLog();

        $settingsA = resolve(DummySimpleSettings::class);
        $settingsB = resolve(DummySimpleSettings::class);

        $settingsA->name;
        $settingsB->name;

        $log = DB::connection()->getQueryLog();

        $this->assertCount(1, $log);
    }

    /** @test */
    public function it_wont_fetch_data_from_the_repository_when_injected_only()
    {
        DB::connection()->enableQueryLog();

        resolve(DummySimpleSettings::class);

        $log = DB::connection()->getQueryLog();

        $this->assertCount(0, $log);
    }

    /** @test */
    public function settings_are_shared_between_instances()
    {
        $settingsA = resolve(DummySimpleSettings::class);
        $settingsB = resolve(DummySimpleSettings::class);

        $settingsA->name = 'Nina Simone';

        $this->assertEquals('Nina Simone', $settingsB->name);

        $settingsB->lock('name');

        $settingsB->save();

        $this->assertEquals('Louis Armstrong', $settingsA->name);
    }

    /** @test */
    public function it_can_refresh_settings()
    {
        $settings = resolve(DummySimpleSettings::class);

        $fakeAction = resolve(FakeAction::class);

        $fakeAction->updateSettings();

        $this->assertEquals('updated', $settings->name);
    }
}
