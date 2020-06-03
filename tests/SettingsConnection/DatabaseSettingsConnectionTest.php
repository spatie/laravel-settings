<?php

namespace Tests\Support\Settings\SettingsConnection;

use App\Support\Settings\SettingsConnection\DatabaseSettingsConnection;
use App\Support\Settings\SettingsProperty;
use Tests\Support\Settings\TestCase;

class DatabaseSettingsConnectionTest extends TestCase
{
    private DatabaseSettingsConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = new DatabaseSettingsConnection();
    }

    /** @test */
    public function it_can_get_the_properties_in_a_group(): void
    {
        $this->connection->createProperty('test', 'a', 'Alpha');
        $this->connection->createProperty('test', 'b', true);
        $this->connection->createProperty('test', 'c', ['night', 'day']);
        $this->connection->createProperty('test', 'd', null);
        $this->connection->createProperty('test', 'e', 42);

        $this->connection->createProperty('not-test', 'a', 'Alpha');

        $properties = $this->connection->getPropertiesInGroup('test');

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
        $this->connection->createProperty('test', 'a', 'a');

        $this->assertTrue($this->connection->checkIfPropertyExists('test', 'a'));
        $this->assertFalse($this->connection->checkIfPropertyExists('test', 'b'));
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

        $this->assertEquals('Alpha', $this->connection->getPropertyPayload('test', 'a'));
        $this->assertEquals(true, $this->connection->getPropertyPayload('test', 'b'));
        $this->assertEquals(['night', 'day'], $this->connection->getPropertyPayload('test', 'c'));
        $this->assertEquals(null, $this->connection->getPropertyPayload('test', 'd'));
        $this->assertEquals(42, $this->connection->getPropertyPayload('test', 'e'));
    }

    /** @test */
    public function it_can_create_a_property(): void
    {
        $this->connection->createProperty('test', 'a', 'Alpha');
        $this->connection->createProperty('test', 'b', true);
        $this->connection->createProperty('test', 'c', ['night', 'day']);
        $this->connection->createProperty('test', 'd', null);
        $this->connection->createProperty('test', 'e', 42);

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
        $this->connection->createProperty('test', 'a', 'Alpha');
        $this->connection->createProperty('test', 'b', true);
        $this->connection->createProperty('test', 'c', ['night', 'day']);
        $this->connection->createProperty('test', 'd', null);
        $this->connection->createProperty('test', 'e', 42);

        $this->connection->updatePropertyPayload('test', 'a', null);
        $this->connection->updatePropertyPayload('test', 'b', false);
        $this->connection->updatePropertyPayload('test', 'c', ['light', 'dark']);
        $this->connection->updatePropertyPayload('test', 'd', 'Alpha');
        $this->connection->updatePropertyPayload('test', 'e', 69);

        $this->assertEquals(null, $this->connection->getPropertyPayload('test', 'a'));
        $this->assertEquals(false, $this->connection->getPropertyPayload('test', 'b'));
        $this->assertEquals(['light', 'dark'], $this->connection->getPropertyPayload('test', 'c'));
        $this->assertEquals('Alpha', $this->connection->getPropertyPayload('test', 'd'));
        $this->assertEquals(69, $this->connection->getPropertyPayload('test', 'e'));
    }

    /** @test */
    public function it_can_delete_a_property(): void
    {
        $this->connection->createProperty('test', 'a', 'Alpha');

        $this->connection->deleteProperty('test', 'a');

        $this->assertEquals(0, SettingsProperty::count());
    }

    /** @test */
    public function it_can_import_settings(): void
    {
        $this->connection->import([
            'test' => [
                'a' => 'Alpha',
                'b' => true,
            ],
            'check' => [
                'a' => 42,
            ],
        ]);

        $this->assertEquals('Alpha', $this->connection->getPropertyPayload('test', 'a'));
        $this->assertEquals(true, $this->connection->getPropertyPayload('test', 'b'));
        $this->assertEquals(42, $this->connection->getPropertyPayload('check', 'a'));
    }

    /** @test */
    public function it_will_update_payloads_when_importing_settings(): void
    {
        $this->connection->createProperty('test', 'a', 'Alpha');

        $this->connection->import([
            'test' => [
                'a' => 'Beta',
            ],
        ]);

        $this->assertEquals('Beta', $this->connection->getPropertyPayload('test', 'a'));
    }

    /** @test */
    public function it_can_export_settings(): void
    {
        $this->connection->createProperty('test', 'a', 'Alpha');
        $this->connection->createProperty('test', 'b', true);

        $this->connection->createProperty('check', 'a', 42);

        $this->assertEquals([
            'test' => [
                'a' => 'Alpha',
                'b' => true,
            ],
            'check' => [
                'a' => 42,
            ],
        ], $this->connection->export());
    }
}
