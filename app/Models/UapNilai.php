<?php

namespace App\Models;

use Database\Factories\UapNilaiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UapNilai extends Model
{
    /** @use HasFactory<UapNilaiFactory> */
    use HasFactory;

    protected $table = 'uap_nilai';

    protected $fillable = [
        'mahasiswa_id',
        'program_studi_id',
        'tahun_ajaran_id',
        'uap_tulis',
        'uap_praktik',
        'keterangan',
        'tanggal_input',
    ];

    // Relasi ke Mahasiswa
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    // Relasi ke Program Studi
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    // Relasi ke Tahun Ajaran
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAkademik::class);
    }
}
