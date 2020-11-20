<?php

namespace Spatie\LaravelSettings\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MakeSettingsMigrationCommand extends Command
{
    protected $signature = 'make:settings-migration {name : The name of the migration}';

    protected $description = 'Create a new settings migration file';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle(): void
    {
        $name = trim($this->input->getArgument('name'));

        $path = config('settings.migrations_path');

        $this->ensureMigrationDoesntAlreadyExist($name, $path);

        $this->files->ensureDirectoryExists($path);

        $this->files->put(
            $this->getPath($name, $path),
            str_replace('{{ class }}', $name, $this->getStub())
        );
    }

    protected function getStub(): string
    {
        return <<<EOT
<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class {{ class }} extends SettingsMigration
{
    public function up(): void
    {

    }
}

EOT;
    }

    protected function ensureMigrationDoesntAlreadyExist($name, $migrationPath = null): void
    {
        if (! empty($migrationPath)) {
            $migrationFiles = $this->files->glob($migrationPath . '/*.php');

            foreach ($migrationFiles as $migrationFile) {
                $this->files->requireOnce($migrationFile);
            }
        }

        if (class_exists($className = Str::studly($name))) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }

    protected function getPath($name, $path): string
    {
        return $path . '/' . date('Y_m_d_His') . '_' . Str::snake($name) . '.php';
    }
}
