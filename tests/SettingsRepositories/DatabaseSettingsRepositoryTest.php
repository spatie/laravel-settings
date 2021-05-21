<?php

namespace Spatie\LaravelSettings\Tests\SettingsRepositories;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;
use Spatie\LaravelSettings\Tests\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class DatabaseSettingsRepositoryTest extends TestCase
{
    private DatabaseSettingsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DatabaseSettingsRepository(config('settings.repositories.database'));
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
            'locked' => false,
        ]);

        SettingsProperty::create([
            'group' => 'test',
            'name' => 'b',
            'payload' => json_encode(true),
            'locked' => false,
        ]);

        SettingsProperty::create([
            'group' => 'test',
            'name' => 'c',
            'payload' => json_encode(['night', 'day']),
            'locked' => false,
        ]);

        SettingsProperty::create([
            'group' => 'test',
            'name' => 'd',
            'payload' => json_encode(null),
            'locked' => false,
        ]);

        SettingsProperty::create([
            'group' => 'test',
            'name' => 'e',
            'payload' => json_encode(42),
            'locked' => false,
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
    public function it_can_lock_settings()
    {
        $this->repository->createProperty('test', 'a', 'Alpha');
        $this->repository->createProperty('test', 'b', 'Beta');
        $this->repository->createProperty('test', 'c', 'Gamma');

        $propertyA = $this->getSettingsProperty('test', 'a');
        $propertyB = $this->getSettingsProperty('test', 'b');
        $propertyC = $this->getSettingsProperty('test', 'c');

        $this->repository->lockProperties('test', ['a', 'c']);

        $this->assertTrue($propertyA->refresh()->locked);
        $this->assertFalse($propertyB->refresh()->locked);
        $this->assertTrue($propertyC->refresh()->locked);
    }

    /** @test */
    public function it_can_unlock_settings()
    {
        $this->repository->createProperty('test', 'a', 'Alpha');
        $this->repository->createProperty('test', 'b', 'Beta');
        $this->repository->createProperty('test', 'c', 'Gamma');

        $propertyA = $this->getSettingsProperty('test', 'a');
        $propertyB = $this->getSettingsProperty('test', 'b');
        $propertyC = $this->getSettingsProperty('test', 'c');

        foreach ([$propertyA, $propertyB, $propertyC] as $property) {
            $property->update(['locked' => true]);
        }

        $this->repository->unlockProperties('test', ['a', 'c']);

        $this->assertFalse($propertyA->refresh()->locked);
        $this->assertTrue($propertyB->refresh()->locked);
        $this->assertFalse($propertyC->refresh()->locked);
    }

    /** @test */
    public function it_can_get_the_locked_properties()
    {
        $this->repository->createProperty('test', 'a', 'Alpha');
        $this->repository->createProperty('test', 'b', 'Beta');
        $this->repository->createProperty('test', 'c', 'Gamma');

        $this->getSettingsProperty('test', 'a')->update(['locked' => true]);
        $this->getSettingsProperty('test', 'c')->update(['locked' => true]);

        $lockedProperties = $this->repository->getLockedProperties('test');

        $this->assertCount(2, $lockedProperties);
        $this->assertContains('a', $lockedProperties);
        $this->assertContains('c', $lockedProperties);
    }

    /**
     * @test
     * @dataProvider configurationsProvider
     *
     * @param \Closure $repositoryFactory
     */
    public function it_can_have_different_configuration_options(Closure $repositoryFactory)
    {
        $this->prepareOtherConnection();

        $otherRepository = $repositoryFactory();

        $otherRepository->createProperty('test', 'a', 'Alpha');

        $this->assertEmpty($this->repository->getPropertiesInGroup('test'));
        $this->assertEquals(['a' => 'Alpha'], $otherRepository->getPropertiesInGroup('test'));

        $otherRepository->createProperty('test', 'b', 'Beta');
        $otherRepository->updatePropertyPayload('test', 'b', 'Beta updated');

        $this->assertEquals('Beta updated', $otherRepository->getPropertyPayload('test', 'b'));

        $otherRepository->lockProperties('test', ['b']);

        $this->assertEquals(['b'], $otherRepository->getLockedProperties('test'));

        $otherRepository->unlockProperties('test', ['b']);

        $this->assertEmpty($otherRepository->getLockedProperties('test'));

        $otherRepository->deleteProperty('test', 'b');

        $this->assertEquals([
            'a' => 'Alpha',
        ], $otherRepository->getPropertiesInGroup('test'));
    }

    public function configurationsProvider(): array
    {
        return [
            [
                function () {
                    return new DatabaseSettingsRepository([
                        'connection' => 'other',
                    ]);
                },
            ], [
                function () {
                    $model = new class() extends SettingsProperty {
                        public function getConnectionName()
                        {
                            return 'other';
                        }
                    };

                    return new DatabaseSettingsRepository([
                        'model' => get_class($model),
                    ]);
                },
            ],
        ];
    }

    private function getSettingsProperty(string $group, string $name): SettingsProperty
    {
        /** @var \Spatie\LaravelSettings\Models\SettingsProperty $settingsProperty */
        $settingsProperty = SettingsProperty::query()
            ->where('group', $group)
            ->where('name', $name)
            ->first();

        return $settingsProperty;
    }

    private function prepareOtherConnection()
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
            $table->boolean('locked');
            $table->json('payload');

            $table->timestamps();
        });
    }
}
