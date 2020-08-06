<?php

namespace Spatie\LaravelSettings\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\LaravelSettings\Settings;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class DiscoverSettings
{
    private array $directories = [];

    private string $basePath = '';

    private string $rootNamespace = '';

    private array $ignoredFiles = [];

    public function __construct()
    {
        $this->basePath = app()->path();
    }

    public function within(array $directories): self
    {
        $this->directories = $directories;

        return $this;
    }

    public function useBasePath(string $basePath): self
    {
        $this->basePath = $basePath;

        return $this;
    }

    public function useRootNamespace(string $rootNamespace): self
    {
        $this->rootNamespace = $rootNamespace;

        return $this;
    }

    public function ignoringFiles(array $ignoredFiles): self
    {
        $this->ignoredFiles = $ignoredFiles;

        return $this;
    }

    public function discover(): array
    {
        if (empty($this->directories)) {
            return [];
        }

        $files = (new Finder())->files()->in($this->directories);

        return collect($files)
            ->reject(fn(SplFileInfo $file) => in_array($file->getPathname(), $this->ignoredFiles))
            ->map(fn(SplFileInfo $file) => $this->fullQualifiedClassNameFromFile($file))
            ->filter(fn(string $settingsClass) => is_subclass_of($settingsClass, Settings::class))
            ->flatten()
            ->toArray();
    }

    private function fullQualifiedClassNameFromFile(SplFileInfo $file): string
    {
        $class = trim(Str::replaceFirst($this->basePath, '', $file->getRealPath()), DIRECTORY_SEPARATOR);

        $class = str_replace(
            [DIRECTORY_SEPARATOR, 'App\\'],
            ['\\', app()->getNamespace()],
            ucfirst(Str::replaceLast('.php', '', $class))
        );

        return $this->rootNamespace . $class;
    }
}
