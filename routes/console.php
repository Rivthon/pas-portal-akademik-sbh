<?php

use App\Models\Jadwal;
use App\Models\LmsQuizAttempt;
use App\Services\GradebookKhsSyncService;
use App\Services\QuizAttemptFinalizationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    Storage::disk('local')->put('monitoring/scheduler-heartbeat.json', json_encode([
        'ran_at' => now()->toIso8601String(),
    ], JSON_PRETTY_PRINT));
})->name('system-scheduler-heartbeat')->everyMinute()->withoutOverlapping();

Schedule::command('system:backup')
    ->dailyAt(config('system.backup.schedule', '01:30'))
    ->withoutOverlapping(180);

Schedule::command('model:prune')
    ->dailyAt('02:30')
    ->withoutOverlapping();

Schedule::call(function () {
    $disk = Storage::disk('private');
    $directory = 'lms/tmp-pengumpulan';
    $expiredBefore = now()
        ->subMinutes((int) config('lms.temporary_task_upload.expires_minutes', 120) + 60)
        ->getTimestamp();

    foreach ($disk->allFiles($directory) as $path) {
        if ($disk->lastModified($path) < $expiredBefore) {
            $disk->delete($path);
        }
    }
})->name('lms-temporary-task-upload-prune')->hourly()->withoutOverlapping();

Schedule::call(function () {
    $finalizer = app(QuizAttemptFinalizationService::class);
    $jadwalUntukSinkronisasi = collect();

    LmsQuizAttempt::query()
        ->with('quiz')
        ->where(function ($query) {
            $query->where('status', 'draft')->orWhere('izinkan_ulang', true);
        })
        ->chunkById(100, function ($attempts) use ($finalizer, $jadwalUntukSinkronisasi) {
            foreach ($attempts as $attempt) {
                if (! $finalizer->isExpired($attempt)) {
                    continue;
                }

                try {
                    $finalized = $finalizer->finalize($attempt);
                    if ($finalized->status === 'graded' && $finalized->quiz?->jadwal_id) {
                        $jadwalUntukSinkronisasi->push((int) $finalized->quiz->jadwal_id);
                    }
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        }, 'attempt_id');

    $syncService = app(GradebookKhsSyncService::class);
    foreach ($jadwalUntukSinkronisasi->unique() as $jadwalId) {
        try {
            $jadwal = Jadwal::find($jadwalId);
            if ($jadwal) {
                $syncService->sync($jadwal);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
})->name('lms-quiz-expired-attempt-finalize')->everyMinute()->withoutOverlapping();
