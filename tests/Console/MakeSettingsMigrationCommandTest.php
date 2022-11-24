<?php

namespace Spatie\LaravelSettings\Tests\Console;

use function Orchestra\Testbench\artisan;

it('creates a new test settings migration on specified path', function () {
    $tmpDir = sys_get_temp_dir();

    expect(artisan($this, 'make:settings-migration', [
        'name' => 'CreateNewTestSettingsMigration',
        'path' => $tmpDir,
    ]))->toBe(0);

    $tmpList = glob(sprintf('%s/*_create_new_test_settings_migration.php', $tmpDir));

    expect($tmpList)->toHaveCount(1);

    // Remove test file.
    unlink($tmpList[0]);
});
