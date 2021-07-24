<?php

namespace Spatie\LaravelSettings\Support;

use Illuminate\Support\Str;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Throwable;

class DiscoverSettings
{
    protected array $directories = [];

    protected string $basePath = '';

    protected string $rootNamespace = '';

    protected array $ignoredFiles = [];

    public function __construct()
    {
        $this->basePath = app_path();
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
            ->reject(fn (SplFileInfo $file) => in_array($file->getPathname(), $this->ignoredFiles))
            ->map(fn (SplFileInfo $file) => $this->fullQualifiedClassNameFromFile($file))
            ->filter(function (string $settingsClass) {
                var_dump($settingsClass);
                var_dump(s_subclass_of($settingsClass, Settings::class) );
                try {
                    return is_subclass_of($settingsClass, Settings::class) || is_subclass_of($settingsClass, SettingsEloquent::class);
                } catch (Throwable $e) {
                    return false;
                }
            })
            ->flatten()
            ->toArray();
    }

    protected function fullQualifiedClassNameFromFile(SplFileInfo $file): string
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
