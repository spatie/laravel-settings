<?php

namespace Spatie\LaravelSettings\Support;

use Illuminate\Support\Str;

class Composer
{
    public static function getAutoloadedFiles($composerJsonPath): array
    {
        if (! file_exists($composerJsonPath)) {
            return [];
        }

        $basePath = Str::before($composerJsonPath, 'composer.json');

        $composerContents = json_decode(file_get_contents($composerJsonPath), true);

        $paths = array_merge(
            $composerContents['autoload']['files'] ?? [],
            $composerContents['autoload-dev']['files'] ?? []
        );

        return array_map(fn (string $path) => realpath($basePath.$path), $paths);
    }
}
