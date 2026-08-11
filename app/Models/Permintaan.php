<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permintaan extends Model
{
    use HasFactory;

    protected $table = 'permintaan_perubahans';

    protected $fillable = [
        'mahasiswa_id',
        'dosen_id',
        'jenis_permintaan',
        'judul',
        'deskripsi',
        'prioritas',
        'file_lampiran',
        'status',
        'komentar_admin',
    ];

    public function userMahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id', 'dosen_id');
    }

    public function getPemohonAttribute()
    {
        return $this->dosen_id ? $this->dosen : $this->mahasiswa;
    }

    public function getJenisPemohonAttribute(): string
    {
        return $this->dosen_id ? 'Dosen' : 'Mahasiswa';
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function updateStatus(string $newStatus)
    {
        $this->update(['status' => $newStatus]);
    }
}
