<?php

namespace App\Models;

use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TarifPerSemester extends Model
{
    use HasFactory;

    protected $table = 'tarif_persemester';
    protected $fillable = ['jurusan_id', 'semester', 'tahun_masuk', 'tarif','gelombang_id'];

    // Relasi ke Program Studi
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class,'jurusan_id');
    }

    // Relasi ke Tahun Ajaran
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAkademik::class, 'ta_id');
    }
      public function gelombangs()
    {
        return $this->belongsTo(Gelombang::class, 'gelombang_id');
    }
}