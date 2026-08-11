<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Krs extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Krs $krs) {
            if (! $krs->matakuliah_id && $krs->kurikulum_id) {
                $krs->matakuliah_id = Kurikulum::whereKey($krs->kurikulum_id)
                    ->value('matakuliah_id');
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'krs'; // Pastikan nama tabel sesuai

    protected $primaryKey = 'krs_id';   // Pastikan primary key sesuai

    public $timestamps = true;

    protected $fillable = [
        'kurikulum_id',
        'matakuliah_id',
        'ta_id',
        'mahasiswa_id',
        'disetujui_oleh',
        'disetujui_pada',
        'khs',
        'uts',
        'uas',

        'akhir',
        'tugas',
        'absen',
        'praktik',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [
            'krs_id' => 'integer',
            'kurikulum_id' => 'integer',
            'matakuliah_id' => 'string',
            'ta_id' => 'integer',
            'mahasiswa_id' => 'integer',
            'disetujui_oleh' => 'integer',
            'disetujui_pada' => 'datetime',
            'khs' => 'string',
            'uts' => 'string',
            'uas' => 'string',
            'akhir' => 'string',
            'tugas' => 'string',
            'absen' => 'string',
            'praktik' => 'string',
        ];
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'matakuliah_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAkademik::class, 'ta_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(
            Mahasiswa::class,
            'mahasiswa_id',
            'mahasiswa_id'
        );
    }

    public function disetujuiOleh()
    {
        return $this->belongsTo(Dosen::class, 'disetujui_oleh', 'dosen_id');
    }

    public function kurikulum()
    {
        return $this->belongsTo(
            Kurikulum::class,
            'kurikulum_id',
            'kurikulum_id'
        );
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function dosenToMatakuliah()
    {
        return $this->hasMany(DosenMatakuliah::class, 'kurikulum_id', 'kurikulum_id');
    }
}
