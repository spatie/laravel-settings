<?php

namespace Spatie\LaravelSettings\Tests\SettingsRepositories;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;

beforeEach(function () {
    $this->repository = new DatabaseSettingsRepository(config('settings.repositories.database'));
});

it('can get the properties in a group', function () {
    $this->repository->createProperty('test', 'a', 'Alpha');
    $this->repository->createProperty('test', 'b', true);
    $this->repository->createProperty('test', 'c', ['night', 'day']);
    $this->repository->createProperty('test', 'd', null);
    $this->repository->createProperty('test', 'e', 42);

    $this->repository->createProperty('not-test', 'a', 'Alpha');

    $properties = $this->repository->getPropertiesInGroup('test');

    expect($properties)
        ->toHaveCount(5)
        ->toEqual([
            'a' => 'Alpha',
            'b' => true,
            'c' => ['night', 'day'],
            'd' => null,
            'e' => 42,
        ]);
});

it('can check if a property exists', function () {
    $this->repository->createProperty('test', 'a', 'a');

    expect($this->repository->checkIfPropertyExists('test', 'a'))->toBeTrue();
    expect($this->repository->checkIfPropertyExists('test', 'b'))->toBeFalse();
});

it('can get the property payload', function () {
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

    expect($this->repository->getPropertyPayload('test', 'a'))->toEqual('Alpha');
    expect($this->repository->getPropertyPayload('test', 'b'))->toBeTrue();
    expect($this->repository->getPropertyPayload('test', 'c'))->toEqual(['night', 'day']);
    expect($this->repository->getPropertyPayload('test', 'd'))->toBeNull();
    expect($this->repository->getPropertyPayload('test', 'e'))->toEqual(42);
});

it('can create a property', function () {
    $this->repository->createProperty('test', 'a', 'Alpha');
    $this->repository->createProperty('test', 'b', true);
    $this->repository->createProperty('test', 'c', ['night', 'day']);
    $this->repository->createProperty('test', 'd', null);
    $this->repository->createProperty('test', 'e', 42);

    $properties = SettingsProperty::all();

    expect($properties)
        ->toHaveCount(5);

    expect($properties[0]->group)->toEqual('test');
    expect($properties[0]->name)->toEqual('a');
    expect(json_decode($properties[0]->payload))->toEqual('Alpha');

    expect($properties[1]->group)->toEqual('test');
    expect($properties[1]->name)->toEqual('b');
    expect(json_decode($properties[1]->payload))->toBeTrue();

    expect($properties[2]->group)->toEqual('test');
    expect($properties[2]->name)->toEqual('c');
    expect(json_decode($properties[2]->payload, true))->toEqual(['night', 'day']);

    expect($properties[3]->group)->toEqual('test');
    expect($properties[3]->name)->toEqual('d');
    expect(json_decode($properties[3]->payload, true))->toBeNull();

    expect($properties[4]->group)->toEqual('test');
    expect($properties[4]->name)->toEqual('e');
    expect(json_decode($properties[4]->payload, true))->toEqual(42);
});

it('can update a property payload', function () {
    $this->repository->createProperty('test', 'a', 'Alpha');
    $this->repository->createProperty('test', 'b', true);
    $this->repository->createProperty('test', 'c', ['night', 'day']);
    $this->repository->createProperty('test', 'd', null);
    $this->repository->createProperty('test', 'e', 42);

    $this->repository->updatePropertiesPayload('test', [
        'a' => null,
        'b' => false,
        'c' => ['light', 'dark'],
        'd' => 'Alpha',
        'e' => 69,
    ]);

    expect($this->repository->getPropertyPayload('test', 'a'))->toBeNull();
    expect($this->repository->getPropertyPayload('test', 'b'))->toBeFalse();
    expect($this->repository->getPropertyPayload('test', 'c'))->toEqual(['light', 'dark']);
    expect($this->repository->getPropertyPayload('test', 'd'))->toEqual('Alpha');
    expect($this->repository->getPropertyPayload('test', 'e'))->toEqual(69);
});

it('can utilize custom encoders', function () {
    config()->set('settings.encoder', fn ($value) => str_rot13(json_encode($value)));

    $this->repository->createProperty('test', 'a', 'Alpha');

    expect(SettingsProperty::all()->first()->payload)->toEqual('"Nycun"');
});

it('can utilize custom decoders', function () {
    config()->set('settings.decoder', fn ($payload, $assoc) => json_decode(str_rot13($payload), $assoc));

    $this->repository->createProperty('test', 'a', 'Nycun');

    expect($this->repository->getPropertyPayload('test', 'a'))->toEqual('Alpha');
});

it('can delete a property', function () {
    $this->repository->createProperty('test', 'a', 'Alpha');

    $this->repository->deleteProperty('test', 'a');

    $this->assertDatabaseDoesNotHaveSetting('test.a');
});

it('can lock settings', function () {
    $this->repository->createProperty('test', 'a', 'Alpha');
    $this->repository->createProperty('test', 'b', 'Beta');
    $this->repository->createProperty('test', 'c', 'Gamma');

    $propertyA = getSettingsProperty('test', 'a');
    $propertyB = getSettingsProperty('test', 'b');
    $propertyC = getSettingsProperty('test', 'c');

    $this->repository->lockProperties('test', ['a', 'c']);

    expect($propertyA->refresh()->locked)->toBeTrue();
    expect($propertyB->refresh()->locked)->toBeFalse();
    expect($propertyC->refresh()->locked)->toBeTrue();
});

it('can unlock settings', function () {
    $this->repository->createProperty('test', 'a', 'Alpha');
    $this->repository->createProperty('test', 'b', 'Beta');
    $this->repository->createProperty('test', 'c', 'Gamma');

    $propertyA = getSettingsProperty('test', 'a');
    $propertyB = getSettingsProperty('test', 'b');
    $propertyC = getSettingsProperty('test', 'c');

    foreach ([$propertyA, $propertyB, $propertyC] as $property) {
        $property->update(['locked' => true]);
    }

    $this->repository->unlockProperties('test', ['a', 'c']);

    expect($propertyA->refresh()->locked)->toBeFalse();
    expect($propertyB->refresh()->locked)->toBeTrue();
    expect($propertyC->refresh()->locked)->toBeFalse();
});

it('can get the locked properties', function () {
    $this->repository->createProperty('test', 'a', 'Alpha');
    $this->repository->createProperty('test', 'b', 'Beta');
    $this->repository->createProperty('test', 'c', 'Gamma');

    getSettingsProperty('test', 'a')->update(['locked' => true]);
    getSettingsProperty('test', 'c')->update(['locked' => true]);

    $lockedProperties = $this->repository->getLockedProperties('test');

    expect($lockedProperties)
        ->toHaveCount(2)
        ->toContain('a')
        ->toContain('c');
});

it('can have different configuration options', function ($repositoryFactory) {
    prepareOtherConnection();

    $otherRepository = $repositoryFactory;

    $otherRepository->createProperty('test', 'a', 'Alpha');

    expect($this->repository->getPropertiesInGroup('test'))->toBeEmpty();
    expect($otherRepository->getPropertiesInGroup('test'))->toEqual(['a' => 'Alpha']);

    $otherRepository->createProperty('test', 'b', 'Beta');
    $otherRepository->updatePropertiesPayload('test', ['b' => 'Beta updated']);

    expect($otherRepository->getPropertyPayload('test', 'b'))->toEqual('Beta updated');

    $otherRepository->lockProperties('test', ['b']);

    expect($otherRepository->getLockedProperties('test'))->toEqual(['b']);

    $otherRepository->unlockProperties('test', ['b']);

    expect($otherRepository->getLockedProperties('test'))->toBeEmpty();

    $otherRepository->deleteProperty('test', 'b');

    expect($otherRepository->getPropertiesInGroup('test'))->toEqual([
        'a' => 'Alpha',
    ]);
})
->with('configurationsProvider');

it('can have a different table name', function () {
    Schema::create('spatie_settings', function (Blueprint $table): void {
        $table->id();

        $table->string('group')->index();
        $table->string('name');
        $table->boolean('locked')->default(false);
        $table->json('payload');

        $table->timestamps();

        $table->unique(['group', 'name']);
    });

    $repository = new DatabaseSettingsRepository([
        'table' => 'spatie_settings',
    ]);

    $repository->createProperty('test', 'a', 'Alpha');

    expect(DB::table('spatie_settings')->count())->toEqual(1);
    expect(DB::table('settings')->count())->toEqual(0);
});
