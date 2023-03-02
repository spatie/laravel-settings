<?php

namespace Spatie\LaravelSettings\Tests\Console;

use Carbon\Carbon;

it('creates a new test settings migration on specified path', function () {
    $tmpDir = sys_get_temp_dir();

    Carbon::setTestNow(Carbon::create(2023, 2, 22, 12, 0, 0));

    $this->artisan('make:settings-migration', [
        'name' => 'CreateNewTestSettingsMigration',
        'path' => $tmpDir,
    ])
        ->expectsOutput(sprintf('Setting migration [%s/2023_02_22_120000_create_new_test_settings_migration.php] created successfully.', $tmpDir))
        ->assertExitCode(0);

    $tmpList = glob(sprintf('%s/*_create_new_test_settings_migration.php', $tmpDir));

    expect($tmpList)->toHaveCount(1);

    // Remove test file.
    unlink($tmpList[0]);
});
