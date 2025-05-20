<?php

namespace App\Models;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sertifikasi extends Model
{
    /** @use HasFactory<\Database\Factories\SertifikasiFactory> */
    use HasFactory;
    protected $table = 'sertifikasi_profesi_kompetensi';   // nama tabel migration
    protected $fillable = [
        'mahasiswa_id',
        'nama_kegiatan',
        'penyelenggara',
        'tingkat_kegiatan',
        'prestasi',
        'tanggal',
        'dokumen_pendukung',
        'jenis_sertifikat',
        'file_sertifikat',

        'status_validasi',
        'catatan_validator',
        'bobot',
    ];
     /* ──────── Relasi ──────── */
     public function mahasiswa()
     {
         return $this->belongsTo(Mahasiswa::class);
     }

}
