<?php

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
