<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanTranskrip extends Model
{
    use HasFactory;

    public const ACTIVE_STATUSES = ['pending', 'diproses'];

    protected $table = 'pengajuan_transkrip';

    protected $fillable = [
        'mahasiswa_id',
        'jenis',
        'keperluan',
        'bukti',
        'status',
        'catatan',
    ];

    // Relasi ke mahasiswa
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    // Scope untuk filter berdasarkan status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    // Helper untuk update status lebih mudah
    public function updateStatus(string $newStatus)
    {
        $this->update(['status' => $newStatus]);
    }
}
