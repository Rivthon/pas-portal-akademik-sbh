<?php

namespace App\Models;

use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UapNilai extends Model
{
    /** @use HasFactory<\Database\Factories\UapNilaiFactory> */
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
