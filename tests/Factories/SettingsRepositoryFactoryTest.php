<?php

namespace Spatie\LaravelSettings\Tests\Factories;

use Spatie\LaravelSettings\Factories\SettingsRepositoryFactory;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;
use Spatie\LaravelSettings\Tests\TestCase;

class SettingsRepositoryFactoryTest extends TestCase
{
    /** @test */
    public function it_can_create_the_default_repository()
    {
        $this->assertInstanceOf(
            DatabaseSettingsRepository::class,
            SettingsRepositoryFactory::create()
        );
    }

    /** @test */
    public function it_can_create_a_specific_repository()
    {
        $this->assertInstanceOf(
            DatabaseSettingsRepository::class,
            SettingsRepositoryFactory::create()
        );
    }
}
