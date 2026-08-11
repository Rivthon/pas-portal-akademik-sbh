<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Daftar guard yang didukung beserta mapping user_type.
     * Urutan penting: guard spesifik dicek lebih dulu.
     */
    protected array $guardMap = [
        'mahasiswa' => 'mahasiswa',
        'dosen' => 'dosen',
        'web' => 'admin',
    ];

    /**
     * Log aktivitas dengan auto-detect user dari guard aktif.
     *
     * @param  string  $aktivitas  Nama aktivitas (e.g., 'login', 'akses_krs')
     * @param  string|null  $deskripsi  Keterangan tambahan (opsional)
     */
    public function log(string $aktivitas, ?string $deskripsi = null): ?ActivityLog
    {
        $guard = $this->resolveActiveGuard();

        if (! $guard) {
            return null; // Tidak ada user yang login
        }

        $user = Auth::guard($guard)->user();

        if (! $user) {
            return null;
        }

        $userType = $this->guardMap[$guard];
        $userId = $this->resolveUserId($user, $userType);

        return $this->createLog($userId, $userType, $aktivitas, $deskripsi);
    }

    /**
     * Log aktivitas untuk user tertentu (eksplisit).
     * Berguna saat logout (user masih tersedia sebelum session dihapus).
     *
     * @param  mixed  $user  Instance model user
     * @param  string  $userType  Tipe user: 'admin', 'mahasiswa', 'dosen'
     * @param  string  $aktivitas  Nama aktivitas
     * @param  string|null  $deskripsi  Keterangan tambahan
     */
    public function logFor($user, string $userType, string $aktivitas, ?string $deskripsi = null): ?ActivityLog
    {
        if (! $user) {
            return null;
        }

        $userId = $this->resolveUserId($user, $userType);

        return $this->createLog($userId, $userType, $aktivitas, $deskripsi);
    }

    /**
     * Resolve guard mana yang sedang aktif.
     *
     * @return string|null Nama guard aktif atau null jika tidak ada
     */
    public function resolveActiveGuard(): ?string
    {
        foreach ($this->guardMap as $guard => $type) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }

    /**
     * Resolve user_type dari guard aktif.
     */
    public function resolveUserType(): ?string
    {
        $guard = $this->resolveActiveGuard();

        return $guard ? ($this->guardMap[$guard] ?? null) : null;
    }

    /**
     * Resolve user ID berdasarkan tipe user.
     * Karena setiap model punya primary key berbeda.
     *
     * @param  mixed  $user
     */
    protected function resolveUserId($user, string $userType): string
    {
        return (string) match ($userType) {
            'mahasiswa' => $user->mahasiswa_id,
            'dosen' => $user->dosen_id,
            'admin' => $user->id,
            default => $user->getKey(),
        };
    }

    /**
     * Buat record log di database.
     */
    protected function createLog(string $userId, string $userType, string $aktivitas, ?string $deskripsi): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $userId,
            'user_type' => $userType,
            'aktivitas' => $aktivitas,
            'deskripsi' => $deskripsi,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
