<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\Tests\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;

uses(TestCase::class)->in(__DIR__);

function withoutGlobalCasts()
{
    config()->set('settings.global_casts', []);
}

function getSettingsProperty(string $group, string $name): SettingsProperty
{
    /** @var \Spatie\LaravelSettings\Models\SettingsProperty $settingsProperty */
    $settingsProperty = SettingsProperty::query()
        ->where('group', $group)
        ->where('name', $name)
        ->first();

    return $settingsProperty;
}

function prepareOtherConnection(): void
{
    $tempDir = (new TemporaryDirectory())->create();

    file_put_contents($tempDir->path('database.sqlite'), '');

    config()->set('database.connections.other', [
        'driver' => 'sqlite',
        'url' => env('DATABASE_URL'),
        'database' => env('DB_DATABASE', $tempDir->path('database.sqlite')),
        'prefix' => '',
        'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
    ]);

    Schema::connection('other')->create('settings', function (Blueprint $table): void {
        $table->id();

        $table->string('group')->index();
        $table->string('name');
        $table->boolean('locked')->default(false);
        $table->json('payload');

        $table->timestamps();

        $table->unique(['group', 'name']);
    });
}

function fakeReflection(Closure $closure): ReflectionProperty
{
    $fake = $closure();

    return new ReflectionProperty($fake, 'property');
}

function useEnabledCache($app): void
{
    $app['config']->set('settings.cache.enabled', true);
}
