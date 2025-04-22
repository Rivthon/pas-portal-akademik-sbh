<?php

namespace App\Models;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengajuanTranskrip extends Model
{
   use HasFactory;

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

    // Helper untuk update status lebih mudah
    public function updateStatus(string $newStatus)
    {
        $this->update(['status' => $newStatus]);
    }
}