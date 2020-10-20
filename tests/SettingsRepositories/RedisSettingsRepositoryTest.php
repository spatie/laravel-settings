<?php

namespace Spatie\LaravelSettings\Tests\SettingsRepositories;

use Illuminate\Redis\RedisManager;
use Spatie\LaravelSettings\SettingsRepositories\RedisSettingsRepository;
use Spatie\LaravelSettings\Tests\TestCase;

class RedisSettingsRepositoryTest extends TestCase
{
    private RedisSettingsRepository $repository;

    /** @var mixed|\Redis */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = resolve(RedisManager::class)->client();

        $this->client->flushAll();

        $this->repository = resolve(RedisSettingsRepository::class, [
            'config' => []
        ]);
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
        $this->client->hMSet('test', [
            'a' => json_encode('Alpha'),
            'b' => json_encode(true),
            'c' => json_encode(['night', 'day']),
            'd' => json_encode(null),
            'e' => json_encode(42),
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

        $this->assertEquals(5, $this->client->hLen('test'));

        $values = $this->client->hVals('test');
        $keys = $this->client->hKeys('test');

        $this->assertEquals('a', $keys[0]);
        $this->assertEquals('Alpha', json_decode($values[0]));

        $this->assertEquals('b', $keys[1]);
        $this->assertEquals(true, json_decode($values[1]));

        $this->assertEquals('c', $keys[2]);
        $this->assertEquals(['night', 'day'], json_decode($values[2], true));

        $this->assertEquals('d', $keys[3]);
        $this->assertEquals(null, json_decode($values[3], true));

        $this->assertEquals('e', $keys[4]);
        $this->assertEquals(42, json_decode($values[4], true));
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

        $this->assertFalse($this->client->hExists('test', 'a'));
    }


    /** @test */
    public function it_can_lock_settings()
    {
        $this->repository->createProperty('test', 'a', 'Alpha');
        $this->repository->createProperty('test', 'b', 'Beta');
        $this->repository->createProperty('test', 'c', 'Gamma');

        $this->repository->lockProperties('test', ['a', 'c']);

        $this->assertEqualsCanonicalizing(['a', 'c'], $this->client->sMembers('locks.test'));
    }

    /** @test */
    public function it_can_unlock_settings()
    {
        $this->repository->createProperty('test', 'a', 'Alpha');
        $this->repository->createProperty('test', 'b', 'Beta');
        $this->repository->createProperty('test', 'c', 'Gamma');

        $this->repository->lockProperties('test', ['a', 'b', 'c']);

        $this->repository->unlockProperties('test', ['a', 'c']);

        $this->assertEqualsCanonicalizing(['b'], $this->client->sMembers('locks.test'));
    }

    /** @test */
    public function it_can_get_the_locked_properties()
    {
        $this->repository->createProperty('test', 'a', 'Alpha');
        $this->repository->createProperty('test', 'b', 'Beta');
        $this->repository->createProperty('test', 'c', 'Gamma');

        $this->repository->lockProperties('test', ['a', 'c']);

        $lockedProperties = $this->repository->getLockedProperties('test');

        $this->assertCount(2, $lockedProperties);
        $this->assertContains('a', $lockedProperties);
        $this->assertContains('c', $lockedProperties);
    }

    /** @test */
    public function it_can_use_a_prefix()
    {
        $this->repository = resolve(RedisSettingsRepository::class, [
            'config' => ['prefix' => 'spatie']
        ]);

        $this->repository->createProperty('test', 'a', 'Alpha');

        $this->assertEquals([
            'a' => json_encode('Alpha'),
        ], $this->client->hGetAll('spatie.test'));

        $this->assertEquals([
            'a' => 'Alpha',
        ], $this->repository->getPropertiesInGroup('test'));

        $this->assertTrue($this->repository->checkIfPropertyExists('test', 'a'));

        $this->assertEquals('Alpha', $this->repository->getPropertyPayload('test', 'a'));

        $this->repository->updatePropertyPayload('test', 'a', 'Alpha Updated');

        $this->assertEquals(
            json_encode('Alpha Updated'),
            $this->client->hGet('spatie.test', 'a')
        );

        $this->repository->lockProperties('test', ['a']);

        $this->assertEquals(['a'], $this->repository->getLockedProperties('test'));

        $this->assertEquals(
            ['a'],
            $this->client->sMembers('spatie.locks.test')
        );

        $this->repository->unlockProperties('test', ['a']);

        $this->assertEmpty($this->client->sMembers('spatie.locks.test'));

        $this->repository->deleteProperty('test', 'a');

        $this->assertEmpty($this->repository->getPropertiesInGroup('test'));
        $this->assertEmpty($this->client->hGetAll('spatie.test'));
    }
}
