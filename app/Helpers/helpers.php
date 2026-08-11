<?php

use App\Models\ActivityLog;
use App\Services\ActivityLogService;

if (! function_exists('activity_log')) {
    /**
     * Log aktivitas user yang sedang login (auto-detect guard).
     *
     * @param  string  $aktivitas  Nama aktivitas (e.g., 'login', 'akses_krs')
     * @param  string|null  $deskripsi  Keterangan tambahan (opsional)
     * @return ActivityLog|null
     */
    function activity_log(string $aktivitas, ?string $deskripsi = null)
    {
        return app(ActivityLogService::class)->log($aktivitas, $deskripsi);
    }
}

if (! function_exists('activity_log_for')) {
    /**
     * Log aktivitas untuk user tertentu (eksplisit).
     * Gunakan saat logout atau ketika perlu specify user secara manual.
     *
     * @param  mixed  $user  Instance model user
     * @param  string  $userType  Tipe user: 'admin', 'mahasiswa', 'dosen'
     * @param  string  $aktivitas  Nama aktivitas
     * @param  string|null  $deskripsi  Keterangan tambahan (opsional)
     * @return ActivityLog|null
     */
    function activity_log_for($user, string $userType, string $aktivitas, ?string $deskripsi = null)
    {
        return app(ActivityLogService::class)->logFor($user, $userType, $aktivitas, $deskripsi);
    }
}
