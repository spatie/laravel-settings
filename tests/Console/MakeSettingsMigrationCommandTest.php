<?php

namespace Spatie\LaravelSettings\Tests\Console;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Spatie\TemporaryDirectory\TemporaryDirectory;

beforeEach(function () {
    $this->temporaryDirectory = (new TemporaryDirectory())->create();
});

afterEach(function () {
    $this->temporaryDirectory->delete();
});

it('creates a new test settings migration on specified path', function () {
    $tmpDir = $this->temporaryDirectory->path();

    $this->travelTo(Carbon::create(2023, 2, 22, 12, 0, 0));

    $this->artisan('make:settings-migration', [
        'name' => 'CreateNewTestSettingsMigration',
        'path' => $tmpDir,
    ])
        ->expectsOutput(sprintf('Setting migration [%s/2023_02_22_120000_create_new_test_settings_migration.php] created successfully.', $tmpDir))
        ->assertExitCode(0);

    $tmpList = File::glob(sprintf('%s/*_create_new_test_settings_migration.php', $tmpDir));

    expect($tmpList)->toHaveCount(1);
});
