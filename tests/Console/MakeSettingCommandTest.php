<?php

namespace Spatie\LaravelSettings\Tests\Console;

it('creates a new test settings class on specified path', function () {
    $tmpDir = sys_get_temp_dir();

    $this->artisan('make:setting', [
        'name' => 'TestSetting',
        '--path' => $tmpDir,
    ])->assertSuccessful();

    $tmpList = glob(sprintf('%s/TestSetting.php', $tmpDir));

    expect($tmpList)->toHaveCount(1);

    // Remove test file.
    unlink($tmpList[0]);
});

it('creates a settings migration when the migration option is passed', function () {
    $tmpDir = sys_get_temp_dir() . '/settings-test-' . uniqid();
    $migrationDir = sys_get_temp_dir() . '/settings-migration-' . uniqid();

    config()->set('settings.migrations_paths', [$migrationDir]);

    $this->artisan('make:setting', [
        'name' => 'TestSetting',
        '--path' => $tmpDir,
        '--migration' => true,
    ])->assertSuccessful();

    $settingFiles = glob(sprintf('%s/TestSetting.php', $tmpDir));
    $migrationFiles = glob(sprintf('%s/*_create_test_setting_settings.php', $migrationDir));

    expect($settingFiles)->toHaveCount(1);
    expect($migrationFiles)->toHaveCount(1);

    // Remove test files.
    unlink($settingFiles[0]);
    unlink($migrationFiles[0]);
    rmdir($tmpDir);
    rmdir($migrationDir);
});
