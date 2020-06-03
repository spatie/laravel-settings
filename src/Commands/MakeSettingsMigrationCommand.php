<?php

namespace Spatie\LaravelSettings\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MakeSettingsMigrationCommand extends Command
{
    protected $signature = 'make:settings-migration {name}';

    protected $description = 'Create a new settings migration file';

    private Filesystem $files;

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

        $this->files->put(
            $path = $this->getPath($name, $path),
            str_replace('{{ class }}', $name, $this->getStub())
        );
    }

    private function getStub(): string
    {
        return <<<EOT
<?php

use Spatie\LaravelSettings\SettingsMigration;

class {{ class }} extends SettingsMigration
{
    public function up(): void
    {

    }
}

EOT;
    }

    private function ensureMigrationDoesntAlreadyExist($name, $migrationPath = null): void
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

    private function getPath($name, $path)
    {
        return $path . '/' . date('Y_m_d_His') . '_' . Str::snake($name) . '.php';
    }
}
