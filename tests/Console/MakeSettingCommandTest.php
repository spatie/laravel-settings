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
