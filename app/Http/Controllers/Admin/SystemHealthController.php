<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SystemHealthController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:system-health-list')->only('index');
        $this->middleware('permission:system-backup-create')->only('createBackup');
        $this->middleware('permission:system-backup-download')->only('downloadBackup');
    }

    public function index(SystemHealthService $health)
    {
        $report = $health->report();
        $errors = auth()->user()->can('system-error-log-list') ? $health->recentErrors() : [];
        $backups = auth()->user()->can('system-backup-list') ? $health->backupFiles() : [];

        activity_log('lihat_kesehatan_sistem', 'Admin melihat dashboard kesehatan sistem');

        return view('admin.system-health.index', compact('report', 'errors', 'backups'));
    }

    public function createBackup(): RedirectResponse
    {
        $exitCode = Artisan::call('system:backup');
        $output = trim(Artisan::output());

        activity_log('buat_backup_sistem', 'Admin menjalankan backup manual. Exit code: '.$exitCode);

        return back()->with(
            $exitCode === 0 ? 'success' : 'error',
            $output ?: ($exitCode === 0 ? 'Backup berhasil dibuat.' : 'Backup gagal dibuat.')
        );
    }

    public function downloadBackup(Request $request, string $filename): BinaryFileResponse
    {
        abort_unless($filename === basename($filename) && str_ends_with(strtolower($filename), '.zip'), 404);

        $disk = Storage::disk(config('system.backup.disk', 'private'));
        $path = config('system.backup.directory', 'system-backups').'/'.$filename;
        abort_unless($disk->exists($path), 404);

        activity_log('download_backup_sistem', 'Admin mengunduh backup '.$filename);

        return response()->download($disk->path($path), $filename, [
            'Content-Type' => 'application/zip',
            'Cache-Control' => 'no-store, private',
        ]);
    }
}
