<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\DosenMatakuliah;
use App\Models\JadwalPraktik;
use App\Models\TahunAkademik;

class JadwalPraktikAssignmentService
{
    public function ensureForDosen(Dosen $dosen, TahunAkademik $tahunAkademik): void
    {
        $dosen->dosenMatakuliah()
            ->whereRaw('LOWER(jenis_dosen) = ?', ['praktik'])
            ->whereHas('kurikulum', fn ($query) => $query->where('ta_id', $tahunAkademik->ta_id))
            ->with('kurikulum')
            ->get()
            ->each(fn (DosenMatakuliah $assignment) => $this->ensureForAssignment($assignment));
    }

    public function ensureForAssignment(DosenMatakuliah $assignment): ?JadwalPraktik
    {
        if (strtolower(trim((string) $assignment->jenis_dosen)) !== 'praktik') {
            return null;
        }

        $assignment->loadMissing('kurikulum');
        $kurikulum = $assignment->kurikulum;
        $jenisKelas = strtolower(trim((string) $assignment->jenis_kelas));

        if (! $kurikulum || $jenisKelas === '') {
            return null;
        }

        $jadwal = JadwalPraktik::query()
            ->where('ta_id', $kurikulum->ta_id)
            ->where('jurusan_id', $kurikulum->jurusan_id)
            ->where('kurikulum_id', $kurikulum->kurikulum_id)
            ->whereRaw('LOWER(jenis_kelas) = ?', [$jenisKelas])
            ->first();

        return $jadwal ?: JadwalPraktik::create([
            'ta_id' => $kurikulum->ta_id,
            'jurusan_id' => $kurikulum->jurusan_id,
            'kurikulum_id' => $kurikulum->kurikulum_id,
            'ruangan_id' => null,
            'jam_mulai' => null,
            'jam_selesai' => null,
            'hari' => null,
            'jenis_kelas' => $jenisKelas,
        ]);
    }
}
