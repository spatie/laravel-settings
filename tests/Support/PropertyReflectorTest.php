<?php

namespace Spatie\LaravelSettings\Tests\Support;

use DateTime;
use Exception;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use ReflectionProperty;
use Spatie\LaravelSettings\Support\PropertyReflector;
use Spatie\LaravelSettings\Tests\TestClasses\DummyDto;
use Spatie\LaravelSettings\Tests\TestClasses\DummySettingsWithImportedType;

it('wont reflect non typed properties', function () {
    $reflection = fakeReflection(fn () => new class() {
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))->toBeNull();
});

it('wont reflect build in typed properties', function () {
    $reflection = fakeReflection(fn () => new class() {
        public int $property;
    });

    expect(PropertyReflector::resolveType($reflection))->toBeNull();

    $reflection = fakeReflection(fn () => new class() {
        public float $property;
    });

    expect(PropertyReflector::resolveType($reflection))->toBeNull();

    $reflection = fakeReflection(fn () => new class() {
        public bool $property;
    });

    expect(PropertyReflector::resolveType($reflection))->toBeNull();

    $reflection = fakeReflection(fn () => new class() {
        public string $property;
    });

    expect(PropertyReflector::resolveType($reflection))->toBeNull();

    $reflection = fakeReflection(fn () => new class() {
        public array $property;
    });

    expect(PropertyReflector::resolveType($reflection))->toBeNull();
});

it('can reflect property types', function () {
    $reflection = fakeReflection(fn () => new class() {
        public DummyDto $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Object_(new Fqsen('\\' . DummyDto::class)));
});

it('can reflect docblock types', function () {
    $reflection = fakeReflection(fn () => new class() {
        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummyDto */
        public DummyDto $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Object_(new Fqsen('\\' . DummyDto::class)));

    $reflection = fakeReflection(fn () => new class() {
        /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummyDto */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Object_(new Fqsen('\\' . DummyDto::class)));
});

it('can reflect arrays', function () {
    $reflection = fakeReflection(fn () => new class() {
        /** @var int[] */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Array_(new Integer(), new Compound([new String_(), new Integer()])));

    $reflection = fakeReflection(fn () => new class() {
        /** @var array<string, int> */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Array_(new Integer(), new String_()));

    $reflection = fakeReflection(fn () => new class() {
        /** @var int[] */
        public array $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Array_(new Integer(), new Compound([new String_(), new Integer()])));
});

it('can reflect arrays with different types', function () {
    $reflection = fakeReflection(fn () => new class() {
        /** @var int[] */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Array_(new Integer(), new Compound([new String_(), new Integer()])));

    $reflection = fakeReflection(fn () => new class() {
        /** @var string[] */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Array_(new String_(), new Compound([new String_(), new Integer()])));

    $reflection = fakeReflection(fn () => new class() {
        /** @var float[] */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Array_(new Float_(), new Compound([new String_(), new Integer()])));

    $reflection = fakeReflection(fn () => new class() {
        /** @var bool[] */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Array_(new Boolean(), new Compound([new String_(), new Integer()])));

    $reflection = fakeReflection(fn () => new class() {
        /** @var DateTime[] */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Array_(new Object_(new Fqsen('\\' . DateTime::class)), new Compound([new String_(), new Integer()])));
});

it('crashes when using a non sensible array type', function () {
    $reflection = fakeReflection(fn () => new class() {
        /** @var self[] */
        public $property;
    });

    PropertyReflector::resolveType($reflection);
})->throws(Exception::class);

it('can handle a nullable type', function () {
    $reflection = fakeReflection(fn () => new class() {
        public ?DateTime $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Nullable(new Object_(new Fqsen('\\' . DateTime::class))));
});

it('can handle a nullable docblock type', function () {
    $reflection = fakeReflection(fn () => new class() {
        /** @var ?DateTime */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Nullable(new Object_(new Fqsen('\\' . DateTime::class))));

    $reflection = fakeReflection(fn () => new class() {
        /** @var ?int */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Nullable(new Integer()));

    $reflection = fakeReflection(fn () => new class() {
        /** @var ?int[] */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Array_(new Nullable(new Integer()), new Compound([new String_(), new Integer()])));
});

it('can handle a compound nullable', function () {
    $reflection = fakeReflection(fn () => new class() {
        /** @var DateTime|null */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Nullable(new Object_(new Fqsen('\\' . DateTime::class))));

    $reflection = fakeReflection(fn () => new class() {
        /** @var int|null */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Nullable(new Integer()));

    $reflection = fakeReflection(fn () => new class() {
        /** @var null|int */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Nullable(new Integer()));

    $reflection = fakeReflection(fn () => new class() {
        /** @var int[]|null */
        public $property;
    });

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Nullable(new Array_(new Integer(), new Compound([new String_(), new Integer()]))));
});

it('can handle an imported type', function () {
    $reflection = new ReflectionProperty(DummySettingsWithImportedType::class, 'dto_array');

    expect(PropertyReflector::resolveType($reflection))
        ->toEqual(new Array_(
            new Object_(new Fqsen('\\' . DummyDto::class)),
            new Compound([new String_(), new Integer()])
        ));
});
