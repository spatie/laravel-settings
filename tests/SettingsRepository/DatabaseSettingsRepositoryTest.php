<?php

namespace Spatie\LaravelSettings\Tests\SettingsRepository;

use Spatie\LaravelSettings\SettingsProperty;
use Spatie\LaravelSettings\SettingsRepository\DatabaseSettingsRepository;
use Spatie\LaravelSettings\Tests\TestCase;

class DatabaseSettingsRepositoryTest extends TestCase
{
    private DatabaseSettingsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DatabaseSettingsRepository();
    }

    /** @test */
    public function it_can_get_the_properties_in_a_group(): void
    {
        $this->repository->createProperty('test', 'a', 'Alpha');
        $this->repository->createProperty('test', 'b', true);
        $this->repository->createProperty('test', 'c', ['night', 'day']);
        $this->repository->createProperty('test', 'd', null);
        $this->repository->createProperty('test', 'e', 42);

        $this->repository->createProperty('not-test', 'a', 'Alpha');

        $properties = $this->repository->getPropertiesInGroup('test');

        $this->assertCount(5, $properties);
        $this->assertEquals([
            'a' => 'Alpha',
            'b' => true,
            'c' => ['night', 'day'],
            'd' => null,
            'e' => 42,
        ], $properties);
    }

    /** @test */
    public function it_can_check_if_a_property_exists(): void
    {
        $this->repository->createProperty('test', 'a', 'a');

        $this->assertTrue($this->repository->checkIfPropertyExists('test', 'a'));
        $this->assertFalse($this->repository->checkIfPropertyExists('test', 'b'));
    }

    /** @test */
    public function it_can_get_the_property_payload(): void
    {
        SettingsProperty::create([
            'group' => 'test',
            'name' => 'a',
            'payload' => json_encode('Alpha'),
        ]);

        SettingsProperty::create([
            'group' => 'test',
            'name' => 'b',
            'payload' => json_encode(true),
        ]);

        SettingsProperty::create([
            'group' => 'test',
            'name' => 'c',
            'payload' => json_encode(['night', 'day']),
        ]);

        SettingsProperty::create([
            'group' => 'test',
            'name' => 'd',
            'payload' => json_encode(null),
        ]);

        SettingsProperty::create([
            'group' => 'test',
            'name' => 'e',
            'payload' => json_encode(42),
        ]);

        $this->assertEquals('Alpha', $this->repository->getPropertyPayload('test', 'a'));
        $this->assertEquals(true, $this->repository->getPropertyPayload('test', 'b'));
        $this->assertEquals(['night', 'day'], $this->repository->getPropertyPayload('test', 'c'));
        $this->assertEquals(null, $this->repository->getPropertyPayload('test', 'd'));
        $this->assertEquals(42, $this->repository->getPropertyPayload('test', 'e'));
    }

    /** @test */
    public function it_can_create_a_property(): void
    {
        $this->repository->createProperty('test', 'a', 'Alpha');
        $this->repository->createProperty('test', 'b', true);
        $this->repository->createProperty('test', 'c', ['night', 'day']);
        $this->repository->createProperty('test', 'd', null);
        $this->repository->createProperty('test', 'e', 42);

        $properties = SettingsProperty::all();

        $this->assertCount(5, $properties);

        $this->assertEquals('test', $properties[0]->group);
        $this->assertEquals('a', $properties[0]->name);
        $this->assertEquals('Alpha', json_decode($properties[0]->payload));

        $this->assertEquals('test', $properties[1]->group);
        $this->assertEquals('b', $properties[1]->name);
        $this->assertEquals(true, json_decode($properties[1]->payload));

        $this->assertEquals('test', $properties[2]->group);
        $this->assertEquals('c', $properties[2]->name);
        $this->assertEquals(['night', 'day'], json_decode($properties[2]->payload, true));

        $this->assertEquals('test', $properties[3]->group);
        $this->assertEquals('d', $properties[3]->name);
        $this->assertEquals(null, json_decode($properties[3]->payload, true));

        $this->assertEquals('test', $properties[4]->group);
        $this->assertEquals('e', $properties[4]->name);
        $this->assertEquals(42, json_decode($properties[4]->payload, true));
    }

    /** @test */
    public function it_can_update_a_property_payload(): void
    {
        $this->repository->createProperty('test', 'a', 'Alpha');
        $this->repository->createProperty('test', 'b', true);
        $this->repository->createProperty('test', 'c', ['night', 'day']);
        $this->repository->createProperty('test', 'd', null);
        $this->repository->createProperty('test', 'e', 42);

        $this->repository->updatePropertyPayload('test', 'a', null);
        $this->repository->updatePropertyPayload('test', 'b', false);
        $this->repository->updatePropertyPayload('test', 'c', ['light', 'dark']);
        $this->repository->updatePropertyPayload('test', 'd', 'Alpha');
        $this->repository->updatePropertyPayload('test', 'e', 69);

        $this->assertEquals(null, $this->repository->getPropertyPayload('test', 'a'));
        $this->assertEquals(false, $this->repository->getPropertyPayload('test', 'b'));
        $this->assertEquals(['light', 'dark'], $this->repository->getPropertyPayload('test', 'c'));
        $this->assertEquals('Alpha', $this->repository->getPropertyPayload('test', 'd'));
        $this->assertEquals(69, $this->repository->getPropertyPayload('test', 'e'));
    }

    /** @test */
    public function it_can_delete_a_property(): void
    {
        $this->repository->createProperty('test', 'a', 'Alpha');

        $this->repository->deleteProperty('test', 'a');

        $this->assertDatabaseDoesNotHaveSetting('test.a');
    }

    /** @test */
    public function it_can_import_settings(): void
    {
        $this->repository->import([
            'test' => [
                'a' => 'Alpha',
                'b' => true,
            ],
            'check' => [
                'a' => 42,
            ],
        ]);

        $this->assertEquals('Alpha', $this->repository->getPropertyPayload('test', 'a'));
        $this->assertEquals(true, $this->repository->getPropertyPayload('test', 'b'));
        $this->assertEquals(42, $this->repository->getPropertyPayload('check', 'a'));
    }

    /** @test */
    public function it_will_update_payloads_when_importing_settings(): void
    {
        $this->repository->createProperty('test', 'a', 'Alpha');

        $this->repository->import([
            'test' => [
                'a' => 'Beta',
            ],
        ]);

        $this->assertEquals('Beta', $this->repository->getPropertyPayload('test', 'a'));
    }

    /** @test */
    public function it_can_export_settings(): void
    {
        $this->repository->createProperty('test', 'a', 'Alpha');
        $this->repository->createProperty('test', 'b', true);

        $this->repository->createProperty('check', 'a', 42);

        $this->assertEquals([
            'test' => [
                'a' => 'Alpha',
                'b' => true,
            ],
            'check' => [
                'a' => 42,
            ],
        ], $this->repository->export());
    }
}
