<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class BackupSystemCommand extends Command
{
    protected $signature = 'system:backup
        {--database-only : Hanya backup database}
        {--prune-only : Hanya hapus backup lama tanpa membuat backup baru}';

    protected $description = 'Membuat backup privat database dan file upload PAS';

    public function handle(): int
    {
        $disk = Storage::disk(config('system.backup.disk', 'private'));
        $directory = trim(config('system.backup.directory', 'system-backups'), '/');
        $disk->makeDirectory($directory);

        if ($this->option('prune-only')) {
            $deleted = $this->pruneBackups($disk, $directory);
            $this->info('Pembersihan selesai. '.$deleted.' file backup lama dihapus.');

            return self::SUCCESS;
        }

        $timestamp = now()->format('Ymd_His');
        $filename = 'pas_backup_'.$timestamp.'.zip';
        $relativePath = $directory.'/'.$filename;
        $zipPath = $disk->path($relativePath);
        $sqlPath = $disk->path($directory.'/.database_'.$timestamp.'.sql');

        $this->writeMetadata($disk, $directory, [
            'status' => 'running',
            'started_at' => now()->toIso8601String(),
            'filename' => $filename,
        ]);

        try {
            $this->dumpDatabase($sqlPath);

            $zip = new ZipArchive;
            $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if ($opened !== true) {
                throw new \RuntimeException('Arsip ZIP tidak dapat dibuat. Kode: '.$opened);
            }

            $zip->addFile($sqlPath, 'database/database.sql');

            if (! $this->option('database-only') && config('system.backup.include_uploads', true)) {
                $this->addDirectory($zip, storage_path('app/public'), 'uploads/public');
                $this->addDirectory(
                    $zip,
                    storage_path('app/private'),
                    'uploads/private',
                    $disk->path($directory)
                );
            }

            if (! $zip->close()) {
                throw new \RuntimeException('Arsip ZIP gagal diselesaikan.');
            }

            File::delete($sqlPath);

            $metadata = [
                'status' => 'success',
                'started_at' => now()->toIso8601String(),
                'finished_at' => now()->toIso8601String(),
                'filename' => $filename,
                'size' => File::size($zipPath),
                'includes_uploads' => ! $this->option('database-only') && config('system.backup.include_uploads', true),
            ];
            $this->writeMetadata($disk, $directory, $metadata);
            $deleted = $this->pruneBackups($disk, $directory);

            $this->info('Backup berhasil dibuat: '.$filename);
            if ($deleted > 0) {
                $this->info($deleted.' file backup lama otomatis dihapus.');
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            File::delete([$sqlPath, $zipPath]);
            report($e);
            $this->writeMetadata($disk, $directory, [
                'status' => 'failed',
                'finished_at' => now()->toIso8601String(),
                'message' => $e->getMessage(),
            ]);
            $this->error('Backup gagal: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function dumpDatabase(string $destination): void
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}");
        if (($database['driver'] ?? null) !== 'mysql') {
            throw new \RuntimeException('Backup otomatis saat ini hanya mendukung database MySQL/MariaDB.');
        }

        $arguments = [
            config('system.backup.mysqldump_path', 'mysqldump'),
            '--host='.(string) ($database['host'] ?? '127.0.0.1'),
            '--port='.(string) ($database['port'] ?? '3306'),
            '--user='.(string) ($database['username'] ?? ''),
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--no-tablespaces',
            (string) ($database['database'] ?? ''),
        ];

        $handle = fopen($destination, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('File sementara database tidak dapat dibuat.');
        }

        $stderr = '';
        try {
            $process = new Process($arguments, base_path(), [
                'MYSQL_PWD' => (string) ($database['password'] ?? ''),
            ]);
            $process->setTimeout(3600);
            $process->run(function (string $type, string $buffer) use ($handle, &$stderr) {
                if ($type === Process::OUT) {
                    fwrite($handle, $buffer);
                } else {
                    $stderr .= $buffer;
                }
            });
        } finally {
            fclose($handle);
        }

        if (! isset($process) || ! $process->isSuccessful()) {
            throw new \RuntimeException('mysqldump gagal: '.trim($stderr ?: 'proses tidak berhasil'));
        }
    }

    private function addDirectory(
        ZipArchive $zip,
        string $source,
        string $prefix,
        ?string $excludedDirectory = null
    ): void {
        if (! is_dir($source)) {
            return;
        }

        $source = rtrim(realpath($source) ?: $source, DIRECTORY_SEPARATOR);
        $excludedDirectory = $excludedDirectory
            ? rtrim(realpath($excludedDirectory) ?: $excludedDirectory, DIRECTORY_SEPARATOR)
            : null;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->isLink()) {
                continue;
            }

            $realPath = $file->getRealPath();
            if ($realPath === false || ($excludedDirectory && str_starts_with($realPath, $excludedDirectory))) {
                continue;
            }

            $relative = ltrim(substr($realPath, strlen($source)), DIRECTORY_SEPARATOR);
            $zip->addFile($realPath, $prefix.'/'.str_replace('\\', '/', $relative));
        }
    }

    private function pruneBackups($disk, string $directory): int
    {
        $cutoff = now()->subDays(max(1, config('system.backup.retention_days', 14)))->timestamp;
        $maxFiles = max(1, config('system.backup.max_files', 5));
        $deleted = 0;

        $backups = collect($disk->files($directory))
            ->filter(fn (string $path) => str_ends_with(strtolower($path), '.zip'))
            ->sortByDesc(fn (string $path) => $disk->lastModified($path))
            ->values();

        foreach ($backups as $index => $path) {
            if ($index >= $maxFiles || $disk->lastModified($path) < $cutoff) {
                $disk->delete($path);
                $deleted++;
            }
        }

        return $deleted;
    }

    private function writeMetadata($disk, string $directory, array $metadata): void
    {
        $disk->put(
            $directory.'/latest.json',
            json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
