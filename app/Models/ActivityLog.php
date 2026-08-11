<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use MassPrunable;

    /**
     * Tentukan query untuk log yang akan dihapus (prune).
     * Log yang berumur lebih dari 90 hari akan dihapus otomatis
     * ketika command php artisan model:prune dijalankan.
     */
    public function prunable()
    {
        return static::where('created_at', '<=', now()->subDays(90));
    }

    /**
     * Tabel yang digunakan oleh model.
     */
    protected $table = 'activity_logs';

    /**
     * Log bersifat immutable — hanya insert, tidak pernah update.
     * Kolom created_at dihandle via DB default (useCurrent).
     */
    public $timestamps = false;

    /**
     * Kolom yang dapat diisi secara massal.
     */
    protected $fillable = [
        'user_id',
        'user_type',
        'aktivitas',
        'deskripsi',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * Cast attributes.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS (Dynamic berdasarkan user_type)
    // =========================================================================

    /**
     * Ambil user terkait berdasarkan user_type.
     * Menggunakan morphTo-style manual karena guard berbeda.
     */
    public function user()
    {
        return match ($this->user_type) {
            'admin' => $this->belongsTo(User::class, 'user_id', 'id'),
            'mahasiswa' => $this->belongsTo(Mahasiswa::class, 'user_id', 'mahasiswa_id'),
            'dosen' => $this->belongsTo(Dosen::class, 'user_id', 'dosen_id'),
            default => null,
        };
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Filter log berdasarkan user tertentu.
     */
    public function scopeByUser($query, string $userId, string $userType)
    {
        return $query->where('user_id', $userId)
            ->where('user_type', $userType);
    }

    /**
     * Filter log berdasarkan tipe user.
     */
    public function scopeByType($query, string $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Filter log berdasarkan aktivitas.
     */
    public function scopeByAktivitas($query, string $aktivitas)
    {
        return $query->where('aktivitas', $aktivitas);
    }

    /**
     * Filter log berdasarkan rentang tanggal.
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Log terbaru dulu (default ordering).
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
