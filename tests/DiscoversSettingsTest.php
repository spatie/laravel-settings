<?php

namespace Spatie\LaravelSettings\Tests;

use Spatie\LaravelSettings\Support\Composer;
use Spatie\LaravelSettings\Support\DiscoverSettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummyEncryptedSettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettings;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettingsEloquent;
use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;

class DiscoversSettingsTest extends TestCase
{
    /** @test */
    public function it_can_get_all_classes_that_are_settings()
    {
        $pathToComposerJson = __DIR__.'/../composer.json';

        $discovered = (new DiscoverSettings())
            ->within([__DIR__.'/TestClasses'])
            ->useBasePath(realpath(__DIR__.'/../'))
            ->useRootNamespace('Spatie\LaravelSettings\\')
            ->ignoringFiles(Composer::getAutoloadedFiles($pathToComposerJson))
            ->discover();
        $this->assertEqualsCanonicalizing([
            DummySimpleSettings::class,
            DummySettings::class,
            DummyEncryptedSettings::class,
            DummySettingsEloquent::class
        ], $discovered);
    }
}
