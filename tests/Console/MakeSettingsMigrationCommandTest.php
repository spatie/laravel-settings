<?php

namespace Spatie\LaravelSettings\Tests\Console;

use Spatie\LaravelSettings\Tests\TestCase;

class MakeSettingsMigrationCommandTest extends TestCase
{
    /** @test */
    public function it_creates_a_new_test_settings_migration_on_specified_path()
    {
        $tmpDir = sys_get_temp_dir();

        $this->artisan('make:settings-migration', [
            'name' => 'CreateNewTestSettingsMigration',
            'path' => $tmpDir,
        ])->assertExitCode(0);

        $tmpList = glob(sprintf('%s/*_create_new_test_settings_migration.php', $tmpDir));

        $this->assertCount(1, $tmpList);

        // Remove test file.
        unlink($tmpList[0]);
    }
}
