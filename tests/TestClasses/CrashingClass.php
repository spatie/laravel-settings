<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

class CrashingClass extends DoesNotExist
{
    // This class should be skipped bu the settings discoverer
    // since it extends from a class that DoesNotExists
}
