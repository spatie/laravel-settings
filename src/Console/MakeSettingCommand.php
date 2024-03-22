<?php

namespace Spatie\LaravelSettings\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

class MakeSettingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:setting {name : The name of the setting class} {--group=default : The group name} {--path= : Path to write the setting class file to}';

    /**
     * The console command name.
     *
     * @var string
     */

    protected $name = 'make:setting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Settings Class';

    /**
     * @var Filesystem
     */
    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $name = trim($this->input->getArgument('name'));
        $group = trim($this->input->getOption('group'));
        $path = trim($this->input->getOption('path'));

        if (empty($path)) {
            $path = $this->resolveSettingsPath();
        }

        $this->ensureSettingClassDoesntAlreadyExist($name, $path);

        $this->files->ensureDirectoryExists($path);

        $this->files->put(
            $this->getPath($name, $path),
            $this->getContent($name, $group, $path)
        );
    }

    protected function getStub(): string
    {
        return <<<EOT
<?php

namespace {{ namespace }};

use Spatie\LaravelSettings\Settings;

class {{ class }} extends Settings
{

    public static function group(): string
    {
        return '{{ group }}';
    }
}
EOT;
    }

    protected function getContent($name, $group, $path)
    {
        return str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ group }}'],
            [$this->getNamespace($path), $name, $group],
            $this->getStub()
        );
    }

    protected function ensureSettingClassDoesntAlreadyExist($name, $path): void
    {
        if ($this->files->exists($this->getPath($name, $path))) {
            throw new InvalidArgumentException(sprintf('%s already exists!', $name));
        }
    }

    protected function resolveSettingsPath(): string
    {
        return config('settings.setting_class_path', app_path('Settings'));
    }

    protected function getPath($name, $path): string
    {
        return $path . '/' . $name . '.php';
    }

    protected function getNamespace($path): string
    {
        $path = preg_replace(
            [
                '/^(' . preg_quote(base_path(), '/') . ')/',
                '/\//',
            ],
            [
                '',
                '\\',
            ],
            $path
        );

        $namespace = implode('\\', array_map(fn ($directory) => ucfirst($directory), explode('\\', $path)));

        // Remove leading backslash if present
        if (substr($namespace, 0, 1) === '\\') {
            $namespace = substr($namespace, 1);
        }

        return $namespace;
    }
}
