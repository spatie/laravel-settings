<?php

namespace Spatie\LaravelSettings\Tests\TestClasses;

enum DummyStringEnum:string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
