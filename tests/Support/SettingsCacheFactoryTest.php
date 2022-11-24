<?php

namespace Spatie\LaravelSettings\Tests\Support;

use Spatie\LaravelSettings\SettingsCache;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;

it('can get all caches', function () {
    $config = [
        'repositories' => [
            'with_cache' => [
                'cache' => [
                    'enabled' => false,
                    'store' => 'repository',
                    'prefix' => null,
                    'ttl' => null,
                ],
            ],
            'without_cache' => [],
        ],
        'cache' => [
            'enabled' => false,
            'store' => 'default',
            'prefix' => null,
            'ttl' => null,
        ],
    ];

    $factory = (new SettingsCacheFactory($config));

    expect($factory->build('with_cache'))->toEqual(new SettingsCache(false, 'repository', null));

    expect($factory->build('without_cache'))->toEqual(new SettingsCache(false, 'default', null));

    expect($factory->all())->toEqual([
        'default' => new SettingsCache(false, 'default', null),
        'with_cache' => new SettingsCache(false, 'repository', null),
    ]);
});
