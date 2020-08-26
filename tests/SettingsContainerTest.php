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

        config()->set('settings.settings', [
            DummySimpleSettings::class
        ]);

        $this->migrator = resolve(SettingsMigrator::class);

        $this->migrator->inGroup('dummy_simple', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('name', 'Louis Armstrong');
            $blueprint->add('description', 'Hello Dolly');
        });

        resolve(SettingsContainer::class)->registerBindings();
    }

    /** @test */
    public function it_will_not_fetch_data_from_the_database_twice()
    {
        DB::connection()->enableQueryLog();

        resolve(DummySimpleSettings::class);
        resolve(DummySimpleSettings::class);

        $log = DB::connection()->getQueryLog();

        $this->assertCount(1, $log);
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
