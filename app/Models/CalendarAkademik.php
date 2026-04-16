<?php

namespace App\Models;

use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CalendarAkademik extends Model
{
    use HasFactory;

    protected $table = 'calender_akademik';
    protected $fillable = [
        'jurusan_id',
        'nama_file',
        'path',
        'status',
    ];

    /**
     * Relasi ke tabel jurusan.
     */
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id');
    }

    /**
     * Scope untuk mendapatkan kalender akademik yang aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }
}
