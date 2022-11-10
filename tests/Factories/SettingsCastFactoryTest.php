<?php

namespace Spatie\LaravelSettings\Tests\Factories;

use DateTime;
use ReflectionProperty;
use Spatie\LaravelSettings\Factories\SettingsCastFactory;
use Spatie\LaravelSettings\SettingsCasts\ArraySettingsCast;
use Spatie\LaravelSettings\SettingsCasts\DataCast;
use Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast;
use Spatie\LaravelSettings\SettingsCasts\EnumCast;
use Spatie\LaravelSettings\Tests\TestClasses\DummyData;
use Spatie\LaravelSettings\Tests\TestClasses\DummyIntEnum;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettingsWithImportedType;
use Spatie\LaravelSettings\Tests\TestClasses\DummyStringEnum;
use Spatie\LaravelSettings\Tests\TestClasses\DummyUnitEnum;

it('will not resolve a cast for built in types', function () {
    $fake = new class() {
        public int $integer;
    };

    $reflectionProperty = new ReflectionProperty($fake, 'integer');

    $cast = SettingsCastFactory::resolve($reflectionProperty, []);

    expect($cast)->toBeNull();
});

it('can resolve a global cast', function () {
    $fake = new class() {
        public DateTime $datetime;
    };

    $reflectionProperty = new ReflectionProperty($fake, 'datetime');

    $cast = SettingsCastFactory::resolve($reflectionProperty, []);

    expect($cast)->toEqual(new DateTimeInterfaceCast(DateTime::class));
});

it('can resolve a global cast as docblock', function () {
    $fake = new class() {
        /** @var DateTime */
        public $datetime;
    };

    $reflectionProperty = new ReflectionProperty($fake, 'datetime');

    $cast = SettingsCastFactory::resolve($reflectionProperty, []);

    expect($cast)->toEqual(new DateTimeInterfaceCast(DateTime::class));
});

it('can have no type and no cast', function () {
    $fake = new class() {
        public $noType;
    };

    $reflectionProperty = new ReflectionProperty($fake, 'noType');

    $cast = SettingsCastFactory::resolve($reflectionProperty, []);

    expect($cast)->toBeNull();
});

it('can have a global cast with an array', function () {
    $fake = new class() {
        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummyData[] */
        public array $dto_array;
    };

    $reflectionProperty = new ReflectionProperty($fake, 'dto_array');

    $cast = SettingsCastFactory::resolve($reflectionProperty, []);

    expect($cast)->toEqual(new ArraySettingsCast(new DataCast(DummyData::class)));
});

it('can have a global cast with an array without array type', function () {
    $fake = new class() {
        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummyData[] */
        public $dto_array;
    };

    $reflectionProperty = new ReflectionProperty($fake, 'dto_array');

    $cast = SettingsCastFactory::resolve($reflectionProperty, []);

    expect($cast)->toEqual(new ArraySettingsCast(new DataCast(DummyData::class)));
});

it('can have a plain array without cast', function () {
    $fake = new class() {
        public array $array;
    };

    $reflectionProperty = new ReflectionProperty($fake, 'array');

    $cast = SettingsCastFactory::resolve($reflectionProperty, []);

    expect($cast)->toBeNull();
});

it('can have a nullable cast', function () {
    $fake = new class() {
        public ?DateTime $array;
    };

    $reflectionProperty = new ReflectionProperty($fake, 'array');

    $cast = SettingsCastFactory::resolve($reflectionProperty, []);

    expect($cast)->toEqual(new DateTimeInterfaceCast(DateTime::class));
});

it('can have a nullable docblock cast', function () {
    $fake = new class() {
        /** @var ?\DateTime */
        public $array;
    };

    $reflectionProperty = new ReflectionProperty($fake, 'array');

    $cast = SettingsCastFactory::resolve($reflectionProperty, []);

    expect($cast)->toEqual(new DateTimeInterfaceCast(DateTime::class));
});

it('can create a local cast without arguments', function () {
    withoutGlobalCasts();

    $fake = new class() {
        public DateTime $datetime;
    };

    $reflectionProperty = new ReflectionProperty($fake, 'datetime');

    $cast = SettingsCastFactory::resolve($reflectionProperty, [
        'datetime' => DateTimeInterfaceCast::class,
    ]);

    expect($cast)->toEqual(new DateTimeInterfaceCast(DateTime::class));
});

it('can create a local cast with class identifier and arguments', function () {
    $fake = new class() {
        public $dto;
    };

    $reflectionProperty = new ReflectionProperty($fake, 'dto');

    $cast = SettingsCastFactory::resolve($reflectionProperty, [
        'dto' => DataCast::class . ':' . DummyData::class,
    ]);

    expect($cast)->toEqual(new DataCast(DummyData::class));
});

it('can create a local cast with an already constructed cast', function () {
    $fake = new class() {
        public DummyData $dto;
    };

    $reflectionProperty = new ReflectionProperty($fake, 'dto');

    $cast = SettingsCastFactory::resolve($reflectionProperty, [
        'dto' => new DataCast(DummyData::class),
    ]);

    expect($cast)->toEqual(new DataCast(DummyData::class));
});

it('will not resolve a cast for a primitive type', function () {
    $fake = new class() {
        /** @var int */
        public $int;

        /** @var ?int */
        public $a_nullable_int;

        /** @var int|null */
        public $another_nullable_int;

        /** @var int[]|null */
        public $an_array_of_ints_or_null;
    };

    expect(SettingsCastFactory::resolve(new ReflectionProperty($fake, 'int'), []))->toBeNull();
    expect(SettingsCastFactory::resolve(new ReflectionProperty($fake, 'a_nullable_int'), []))->toBeNull();
    expect(SettingsCastFactory::resolve(new ReflectionProperty($fake, 'another_nullable_int'), []))->toBeNull();
    expect(SettingsCastFactory::resolve(new ReflectionProperty($fake, 'an_array_of_ints_or_null'), []))->toBeNull();
});

it('will resolve an enum cast for native enums', function () {
    $this->skipIfPHPLowerThen('8.1');

    $fake = new class() {
        public DummyUnitEnum $unit;
        public DummyIntEnum $int;
        public DummyStringEnum $string;

        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummyStringEnum */
        public $annotated;
    };

    expect(new EnumCast(DummyUnitEnum::class))->toEqual(SettingsCastFactory::resolve(new ReflectionProperty($fake, 'unit'), []));
    expect(new EnumCast(DummyIntEnum::class))->toEqual(SettingsCastFactory::resolve(new ReflectionProperty($fake, 'int'), []));
    expect(new EnumCast(DummyStringEnum::class))->toEqual(SettingsCastFactory::resolve(new ReflectionProperty($fake, 'string'), []));

    expect(new EnumCast(DummyStringEnum::class))->toEqual(SettingsCastFactory::resolve(new ReflectionProperty($fake, 'annotated'), []));
});

it('will resolve imported annotated casts', function () {
    $reflectionProperty = new ReflectionProperty(DummySettingsWithImportedType::class, 'dto_array');

    $cast = SettingsCastFactory::resolve($reflectionProperty, []);

    expect($cast)->toEqual(new ArraySettingsCast(new DataCast(DummyData::class)));
});
