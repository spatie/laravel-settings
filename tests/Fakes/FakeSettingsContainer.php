<?php

namespace Spatie\LaravelSettings\Tests\Fakes;

use Spatie\LaravelSettings\SettingsContainer;

class FakeSettingsContainer extends SettingsContainer
{
    public function __construct()
    {
        // We're fake
    }

    public static function setUp(): FakeSettingsContainer
    {
        $container = new self();

        app()->bind(SettingsContainer::class, fn () => $container);

        $container->clearCache();

        if ($container::$settingsClasses === null) {
            $container::$settingsClasses = collect();
        }

        return $container;
    }

    public function addSettingsClass(string $class)
    {
        static::$settingsClasses[] = $class;
    }
}
