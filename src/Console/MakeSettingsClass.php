<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;


class MakeSettingsClass extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:setting {name : Setting Class name} {--group= : The group name}';

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
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Setting Class';

    protected function getStub()
    {
        return __DIR__.'/stubs/settingClass.stub'; // todo: add stubs to package!

    }

    protected function buildClass($name): array|string
    {
        $class = parent::buildClass($name); // todo: uncomment after stubs added to package!

        if ($this->option('group')) {
            $class = str_replace(['DummyView','{{ group }}'], $this->option('group'), $class);
        }

        return $class;
    }
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Settings';
    }


}
