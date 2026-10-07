<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsprakAssignment extends Model
{
    protected $table = 'asprak_penugasan';

    protected $fillable = [
        'jadwal_praktik_id',
        'mahasiswa_id',
        'ditugaskan_oleh_dosen_id',
        'aktif',
    ];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalPraktik::class, 'jadwal_praktik_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    public function ditugaskanOleh()
    {
        return $this->belongsTo(Dosen::class, 'ditugaskan_oleh_dosen_id', 'dosen_id');
    }

    public function absensi()
    {
        return $this->hasMany(AsprakAttendance::class, 'asprak_penugasan_id');
    }
}
