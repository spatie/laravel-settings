<?php

namespace Spatie\LaravelSettings\Tests;

use Spatie\LaravelSettings\Tests\TestClasses\DummySimpleSettings;

it('can add a prefix to group name', function() {
    $this->migrateDummySimpleSettings();
    $this->migrateDummyGroupPrefixSettings();

    $settings = resolve(DummySimpleSettings::class);
    expect($settings)
        ->name->toEqual('Louis Armstrong')
        ->description->toEqual('Hello Dolly');

    $settings->setGroupPrefixer('some_id:');
    expect($settings)
        ->name->toEqual('John Doe')
        ->description->toEqual('John, Again?');

    $settings->setGroupPrefixer('');
    expect($settings)
        ->name->toEqual('Louis Armstrong')
        ->description->toEqual('Hello Dolly');
});
