<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SystemHealthService
{
    public function report(): array
    {
        return [
            'checks' => [
                $this->databaseCheck(),
                $this->storageCheck(),
                $this->cacheCheck(),
                $this->queueCheck(),
                $this->schedulerCheck(),
                $this->backupCheck(),
                $this->runtimeCheck(),
                $this->diskCheck(),
            ],
            'version' => $this->version(),
            'generated_at' => now(),
        ];
    }

    public function recentErrors(?int $limit = null): array
    {
        $paths = glob(storage_path('logs/laravel*.log')) ?: [];
        usort($paths, fn (string $a, string $b) => filemtime($b) <=> filemtime($a));
        $path = $paths[0] ?? null;
        if (! $path || ! is_file($path) || ! is_readable($path)) {
            return [];
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }

        try {
            $size = filesize($path) ?: 0;
            $readSize = min($size, 1024 * 1024);
            if ($readSize > 0) {
                fseek($handle, -$readSize, SEEK_END);
            }
            $contents = stream_get_contents($handle) ?: '';
        } finally {
            fclose($handle);
        }

        preg_match_all(
            '/^\[(?<date>[^\]]+)]\s+[^.]+\.ERROR:\s+(?<message>.*)$/m',
            $contents,
            $matches,
            PREG_SET_ORDER
        );

        return collect($matches)
            ->reverse()
            ->take($limit ?? config('system.monitoring.error_limit', 20))
            ->map(function (array $match) {
                $message = preg_split('/\s+\{"(?:userId|exception)"/', $match['message'], 2)[0];

                return [
                    'date' => $match['date'],
                    'message' => Str::limit(trim($message), 700),
                ];
            })
            ->values()
            ->all();
    }

    public function backupFiles(): array
    {
        $disk = Storage::disk(config('system.backup.disk', 'private'));
        $directory = config('system.backup.directory', 'system-backups');

        return collect($disk->files($directory))
            ->filter(fn (string $path) => str_ends_with(strtolower($path), '.zip'))
            ->map(fn (string $path) => [
                'path' => $path,
                'name' => basename($path),
                'size' => $disk->size($path),
                'updated_at' => $disk->lastModified($path),
            ])
            ->sortByDesc('updated_at')
            ->values()
            ->all();
    }

    private function databaseCheck(): array
    {
        return $this->check('Database', function () {
            DB::select('SELECT 1');

            return ['healthy', 'Koneksi database normal.'];
        });
    }

    private function storageCheck(): array
    {
        return $this->check('Storage', function () {
            $directory = storage_path('framework/health');
            File::ensureDirectoryExists($directory);
            $path = $directory.'/'.Str::uuid().'.tmp';
            File::put($path, 'ok');
            $written = File::get($path) === 'ok';
            File::delete($path);

            return $written
                ? ['healthy', 'Folder storage dapat ditulis.']
                : ['danger', 'Folder storage tidak dapat diverifikasi.'];
        });
    }

    private function cacheCheck(): array
    {
        return $this->check('Cache', function () {
            $key = 'system-health:'.Str::uuid();
            Cache::put($key, 'ok', 30);
            $valid = Cache::get($key) === 'ok';
            Cache::forget($key);

            return $valid
                ? ['healthy', 'Cache '.config('cache.default').' normal.']
                : ['danger', 'Cache gagal menyimpan atau membaca data.'];
        });
    }

    private function queueCheck(): array
    {
        $connection = config('queue.default');

        return [
            'name' => 'Queue',
            'status' => app()->environment('production') && $connection === 'sync' ? 'warning' : 'healthy',
            'message' => $connection === 'sync'
                ? 'Queue menggunakan sync; pekerjaan berjalan di request pengguna.'
                : 'Queue menggunakan koneksi '.$connection.'.',
        ];
    }

    private function schedulerCheck(): array
    {
        $disk = Storage::disk('local');
        $path = 'monitoring/scheduler-heartbeat.json';
        if (! $disk->exists($path)) {
            return [
                'name' => 'Scheduler',
                'status' => 'danger',
                'message' => 'Heartbeat belum ditemukan. Pastikan cron schedule:run aktif.',
            ];
        }

        $age = (int) now()->diffInMinutes(
            Carbon::createFromTimestamp($disk->lastModified($path)),
            true
        );
        $stale = $age > config('system.monitoring.scheduler_stale_minutes', 5);

        return [
            'name' => 'Scheduler',
            'status' => $stale ? 'danger' : 'healthy',
            'message' => $stale
                ? "Heartbeat terakhir {$age} menit lalu."
                : "Heartbeat aktif ({$age} menit lalu).",
        ];
    }

    private function backupCheck(): array
    {
        $disk = Storage::disk(config('system.backup.disk', 'private'));
        $metadataPath = config('system.backup.directory', 'system-backups').'/latest.json';
        if (! $disk->exists($metadataPath)) {
            return [
                'name' => 'Backup',
                'status' => 'warning',
                'message' => 'Belum ada backup yang tercatat.',
            ];
        }

        $metadata = json_decode($disk->get($metadataPath), true) ?: [];
        $finishedAt = isset($metadata['finished_at']) ? now()->parse($metadata['finished_at']) : null;
        $age = $finishedAt ? (int) $finishedAt->diffInHours(now()) : null;
        $success = ($metadata['status'] ?? null) === 'success';
        $stale = $age === null || $age > config('system.monitoring.backup_stale_hours', 36);

        return [
            'name' => 'Backup',
            'status' => ! $success ? 'danger' : ($stale ? 'warning' : 'healthy'),
            'message' => ! $success
                ? 'Backup terakhir gagal: '.($metadata['message'] ?? 'Tidak ada detail.')
                : 'Backup terakhir '.($age ?? '?').' jam lalu.',
        ];
    }

    private function runtimeCheck(): array
    {
        $missing = collect(['pdo_mysql', 'mbstring', 'openssl', 'zip'])
            ->reject(fn (string $extension) => extension_loaded($extension))
            ->values();
        $debugRisk = app()->environment('production') && config('app.debug');

        return [
            'name' => 'Runtime PHP',
            'status' => $missing->isNotEmpty() || $debugRisk ? 'danger' : 'healthy',
            'message' => $missing->isNotEmpty()
                ? 'Ekstensi belum tersedia: '.$missing->implode(', ').'.'
                : ($debugRisk ? 'APP_DEBUG masih aktif di production.' : 'PHP '.PHP_VERSION.' dan ekstensi utama tersedia.'),
        ];
    }

    private function diskCheck(): array
    {
        $free = disk_free_space(storage_path());
        if ($free === false) {
            return ['name' => 'Disk', 'status' => 'warning', 'message' => 'Kapasitas disk tidak dapat dibaca.'];
        }

        $warning = $free < 1024 * 1024 * 1024;

        return [
            'name' => 'Disk',
            'status' => $warning ? 'danger' : 'healthy',
            'message' => 'Ruang kosong '.number_format($free / 1024 / 1024 / 1024, 2).' GB.',
        ];
    }

    private function version(): array
    {
        $headPath = base_path('.git/HEAD');
        $branch = 'deployment';
        $commit = env('APP_VERSION', 'Tidak diketahui');

        if (is_readable($headPath)) {
            $head = trim((string) file_get_contents($headPath));
            if (str_starts_with($head, 'ref: ')) {
                $ref = substr($head, 5);
                $branch = basename($ref);
                $refPath = base_path('.git/'.$ref);
                if (is_readable($refPath)) {
                    $commit = trim((string) file_get_contents($refPath));
                }
            } elseif ($head !== '') {
                $commit = $head;
            }
        }

        $deployment = null;
        if (Storage::disk('local')->exists('monitoring/deployment.json')) {
            $deployment = json_decode(
                Storage::disk('local')->get('monitoring/deployment.json'),
                true
            );
        }

        return [
            'branch' => $branch,
            'commit' => Str::limit($commit, 12, ''),
            'environment' => app()->environment(),
            'laravel' => app()->version(),
            'php' => PHP_VERSION,
            'deployed_at' => $deployment['deployed_at'] ?? null,
        ];
    }

    private function check(string $name, callable $callback): array
    {
        try {
            [$status, $message] = $callback();

            return compact('name', 'status', 'message');
        } catch (Throwable $e) {
            report($e);

            return [
                'name' => $name,
                'status' => 'danger',
                'message' => Str::limit($e->getMessage(), 250),
            ];
        }
    }
}
