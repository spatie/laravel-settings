<?php

namespace Spatie\LaravelSettings\Tests\Support;

use Closure;
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
use Spatie\LaravelSettings\Tests\TestCase;
use Spatie\LaravelSettings\Tests\TestClasses\DummyDto;

class PropertyReflectorTest extends TestCase
{
    /** @test */
    public function it_wont_reflect_non_typed_properties()
    {
        $reflection = $this->fakeReflection(fn () => new class() {
            public $property;
        });

        $this->assertEquals(
            null,
            PropertyReflector::resolveType($reflection)
        );
    }

    /** @test */
    public function it_wont_reflect_build_in_typed_properties()
    {
        $reflection = $this->fakeReflection(fn () => new class() {
            public int $property;
        });

        $this->assertEquals(
            null,
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            public float $property;
        });

        $this->assertEquals(
            null,
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            public bool $property;
        });

        $this->assertEquals(
            null,
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            public string $property;
        });

        $this->assertEquals(
            null,
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            public array $property;
        });

        $this->assertEquals(
            null,
            PropertyReflector::resolveType($reflection)
        );
    }

    /** @test */
    public function it_can_reflect_property_types()
    {
        $reflection = $this->fakeReflection(fn () => new class() {
            public DummyDto $property;
        });

        $this->assertEquals(
            new Object_(new Fqsen('\\' . DummyDto::class)),
            PropertyReflector::resolveType($reflection)
        );
    }

    /** @test */
    public function it_can_reflect_docblock_types()
    {
        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummyDto */
            public DummyDto $property;
        });

        $this->assertEquals(
            new Object_(new Fqsen('\\' . DummyDto::class)),
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var \Spatie\LaravelSettings\Tests\TestClasses\DummyDto */
            public $property;
        });

        $this->assertEquals(
            new Object_(new Fqsen('\\' . DummyDto::class)),
            PropertyReflector::resolveType($reflection)
        );
    }

    /** @test */
    public function it_can_reflect_arrays()
    {
        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var int[] */
            public $property;
        });

        $this->assertEquals(
            new Array_(new Integer(), new Compound([new String_(), new Integer()])),
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var array<string, int> */
            public $property;
        });

        $this->assertEquals(
            new Array_(new Integer(), new String_()),
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var int[] */
            public array $property;
        });

        $this->assertEquals(
            new Array_(new Integer(), new Compound([new String_(), new Integer()])),
            PropertyReflector::resolveType($reflection)
        );
    }

    /** @test */
    public function it_can_reflect_arrays_with_different_types()
    {
        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var int[] */
            public $property;
        });

        $this->assertEquals(
            new Array_(new Integer(), new Compound([new String_(), new Integer()])),
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var string[] */
            public $property;
        });

        $this->assertEquals(
            new Array_(new String_(), new Compound([new String_(), new Integer()])),
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var float[] */
            public $property;
        });

        $this->assertEquals(
            new Array_(new Float_(), new Compound([new String_(), new Integer()])),
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var bool[] */
            public $property;
        });

        $this->assertEquals(
            new Array_(new Boolean(), new Compound([new String_(), new Integer()])),
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var DateTime[] */
            public $property;
        });

        $this->assertEquals(
            new Array_(new Object_(new Fqsen('\\' . DateTime::class)), new Compound([new String_(), new Integer()])),
            PropertyReflector::resolveType($reflection)
        );
    }

    /** @test */
    public function it_crashes_when_using_a_non_sensible_array_type()
    {
        $this->expectException(Exception::class);

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var self[] */
            public $property;
        });

        PropertyReflector::resolveType($reflection);
    }

    /** @test */
    public function it_can_handle_a_nullable_type()
    {
        $reflection = $this->fakeReflection(fn () => new class() {
            public ?DateTime $property;
        });

        $this->assertEquals(
            new Nullable(new Object_(new Fqsen('\\' . DateTime::class))),
            PropertyReflector::resolveType($reflection)
        );
    }

    /** @test */
    public function it_can_handle_a_nullable_docblock_type()
    {
        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var ?DateTime */
            public $property;
        });

        $this->assertEquals(
            new Nullable(new Object_(new Fqsen('\\' . DateTime::class))),
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var ?int */
            public $property;
        });

        $this->assertEquals(
            new Nullable(new Integer()),
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var ?int[] */
            public $property;
        });

        $this->assertEquals(
            new Array_(new Nullable(new Integer()), new Compound([new String_(), new Integer()])),
            PropertyReflector::resolveType($reflection)
        );
    }

    /** @test */
    public function it_can_handle_a_compound_nullable()
    {
        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var DateTime|null */
            public $property;
        });

        $this->assertEquals(
            new Nullable(new Object_(new Fqsen('\\' . DateTime::class))),
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var int|null */
            public $property;
        });

        $this->assertEquals(
            new Nullable(new Integer()),
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var null|int */
            public $property;
        });

        $this->assertEquals(
            new Nullable(new Integer()),
            PropertyReflector::resolveType($reflection)
        );

        $reflection = $this->fakeReflection(fn () => new class() {
            /** @var int[]|null */
            public $property;
        });

        $this->assertEquals(
            new Nullable(new Array_(new Integer(), new Compound([new String_(), new Integer()]))),
            PropertyReflector::resolveType($reflection)
        );
    }

    private function fakeReflection(Closure $closure): ReflectionProperty
    {
        $fake = $closure();

        return new ReflectionProperty($fake, 'property');
    }
}
