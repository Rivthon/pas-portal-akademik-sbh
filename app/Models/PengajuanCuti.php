<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanCuti extends Model
{
    public const MENUNGGU_DOSPEM = 'menunggu_dospem';

    public const MENUNGGU_KAPRODI = 'menunggu_kaprodi';

    public const MENUNGGU_BAAK = 'menunggu_baak';

    public const DISETUJUI = 'disetujui';

    public const DITOLAK_DOSPEM = 'ditolak_dospem';

    public const DITOLAK_KAPRODI = 'ditolak_kaprodi';

    public const DITOLAK_BAAK = 'ditolak_baak';

    public const DIBATALKAN = 'dibatalkan';

    protected $table = 'pengajuan_cuti';

    protected $fillable = [
        'mahasiswa_id', 'ta_id', 'program_studi_id', 'dospem_id', 'kaprodi_id', 'baak_id',
        'alasan', 'lampiran', 'status', 'status_mahasiswa_sebelumnya',
        'catatan_dospem', 'catatan_kaprodi', 'catatan_baak',
        'diajukan_pada', 'diproses_dospem_pada', 'diproses_kaprodi_pada', 'diproses_baak_pada',
    ];

    protected function casts(): array
    {
        return [
            'diajukan_pada' => 'datetime',
            'diproses_dospem_pada' => 'datetime',
            'diproses_kaprodi_pada' => 'datetime',
            'diproses_baak_pada' => 'datetime',
        ];
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'ta_id', 'ta_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id', 'jurusan_id');
    }

    public function dospem()
    {
        return $this->belongsTo(Dosen::class, 'dospem_id', 'dosen_id');
    }

    public function kaprodi()
    {
        return $this->belongsTo(Dosen::class, 'kaprodi_id', 'dosen_id');
    }

    public function baak()
    {
        return $this->belongsTo(User::class, 'baak_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::MENUNGGU_DOSPEM => 'Menunggu ACC Dospem',
            self::MENUNGGU_KAPRODI => 'Menunggu ACC Kaprodi',
            self::MENUNGGU_BAAK => 'Menunggu Validasi BAAK',
            self::DISETUJUI => 'Cuti Disetujui',
            self::DITOLAK_DOSPEM => 'Ditolak Dospem',
            self::DITOLAK_KAPRODI => 'Ditolak Kaprodi',
            self::DITOLAK_BAAK => 'Ditolak BAAK',
            self::DIBATALKAN => 'Dibatalkan Mahasiswa',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::DISETUJUI => 'success',
            self::DITOLAK_DOSPEM, self::DITOLAK_KAPRODI, self::DITOLAK_BAAK => 'danger',
            self::DIBATALKAN => 'secondary',
            default => 'warning',
        };
    }
}
