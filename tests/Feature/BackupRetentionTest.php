<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BackupRetentionTest extends TestCase
{
    #[Test]
    public function prune_only_keeps_the_five_newest_backup_files(): void
    {
        Storage::fake('private');
        config([
            'system.backup.disk' => 'private',
            'system.backup.directory' => 'system-backups',
            'system.backup.retention_days' => 365,
            'system.backup.max_files' => 5,
        ]);

        foreach (range(1, 7) as $day) {
            $path = 'system-backups/pas_backup_2026100'.$day.'_013000.zip';
            Storage::disk('private')->put($path, 'backup-'.$day);
            touch(Storage::disk('private')->path($path), strtotime('2026-10-0'.$day.' 01:30:00'));
        }

        $this->assertSame(0, Artisan::call('system:backup', ['--prune-only' => true]));

        $files = collect(Storage::disk('private')->files('system-backups'))
            ->filter(fn (string $path) => str_ends_with($path, '.zip'))
            ->sort()
            ->values();

        $this->assertCount(5, $files);
        $this->assertFalse($files->contains('system-backups/pas_backup_20261001_013000.zip'));
        $this->assertFalse($files->contains('system-backups/pas_backup_20261002_013000.zip'));
        $this->assertTrue($files->contains('system-backups/pas_backup_20261007_013000.zip'));
    }
}
