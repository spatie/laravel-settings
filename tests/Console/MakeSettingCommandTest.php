<?php

namespace Spatie\LaravelSettings\Tests\Console;

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\TemporaryDirectory\TemporaryDirectory;

beforeEach(function () {
    $this->temporaryDirectory = (new TemporaryDirectory(app_path()))
        ->name('GeneratedSettings' . uniqid())
        ->create();

    config()->set('settings.setting_class_path', $this->temporaryDirectory->path('Settings'));
    config()->set('settings.migrations_paths', [
        $this->temporaryDirectory->path('migrations'),
        $this->temporaryDirectory->path('other-migrations'),
    ]);
});

afterEach(function () {
    $this->temporaryDirectory->delete();
});

it('creates a new test settings class on specified path', function () {
    $path = $this->temporaryDirectory->path('CustomSettings');

    $this->artisan('make:setting', [
        'name' => 'TestSetting',
        '--path' => $path,
    ])->assertSuccessful();

    expect(File::exists("{$path}/TestSetting.php"))->toBeTrue();
    expect(File::files(config('settings.setting_class_path')))->toBeEmpty();
    expect(File::files(config('settings.migrations_paths')[0]))->toBeEmpty();
});

it('creates a runnable settings migration in the first configured path', function (string $option) {
    $this->artisan('make:setting', [
        'name' => 'TestSetting',
        '--group' => 'billing',
        $option => true,
    ])->assertSuccessful();

    $setting = File::get(config('settings.setting_class_path') . '/TestSetting.php');

    expect($setting)
        ->toContain('class TestSetting extends Settings')
        ->toContain("return 'billing';");

    token_get_all($setting, TOKEN_PARSE);

    $migrationFiles = File::glob(config('settings.migrations_paths')[0] . '/*_create_test_setting_settings.php');

    expect($migrationFiles)->toHaveCount(1);
    expect(File::files(config('settings.migrations_paths')[1]))->toBeEmpty();

    $migration = require $migrationFiles[0];

    expect($migration)->toBeInstanceOf(SettingsMigration::class);

    $migration->up();
})->with(['--migration', '-m']);

it('respects the legacy migration path when creating a settings migration', function () {
    $path = $this->temporaryDirectory->path('legacy-migrations');

    config()->set('settings.migrations_path', $path);

    $this->artisan('make:setting', [
        'name' => 'TestSetting',
        '--migration' => true,
    ])->assertSuccessful();

    expect(File::glob("{$path}/*_create_test_setting_settings.php"))->toHaveCount(1);
    expect(File::files(config('settings.migrations_paths')[0]))->toBeEmpty();
});

it('does not create a migration or overwrite an existing settings class', function () {
    $path = config('settings.setting_class_path') . '/TestSetting.php';

    File::put($path, 'existing settings');

    expect(fn () => $this->artisan('make:setting', [
        'name' => 'TestSetting',
        '--migration' => true,
    ])->run())->toThrow(InvalidArgumentException::class, 'TestSetting already exists!');

    expect(File::get($path))->toBe('existing settings');
    expect(File::files(config('settings.migrations_paths')[0]))->toBeEmpty();
});

it('returns the migration command failure status', function () {
    Artisan::all();

    Artisan::registerCommand(new ClosureCommand('make:settings-migration {name} {path?}', fn () => 2));

    $this->artisan('make:setting', [
        'name' => 'TestSetting',
        '--migration' => true,
    ])->assertExitCode(2);
});
