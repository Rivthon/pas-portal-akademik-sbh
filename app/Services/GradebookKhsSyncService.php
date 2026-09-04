<?php

namespace App\Services;

use App\Models\BobotNilai;
use App\Models\Jadwal;
use App\Models\KhsPublication;
use App\Models\Krs;
use App\Models\LmsQuiz;
use App\Models\LmsTugas;
use App\Models\NilaiSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GradebookKhsSyncService
{
    /**
     * Sinkronkan persentase Gradebook (0-100) ke komponen tugas pada KHS.
     * Bobot tugas tidak diterapkan di sini agar tidak dihitung dua kali.
     */
    public function sync(Jadwal $jadwal): array
    {
        $jadwal->loadMissing(['kurikulum.mataKuliah', 'kurikulum.programStudi']);
        if (KhsPublication::coversJadwal($jadwal)) {
            return ['synced' => 0, 'total_maksimal' => 0, 'bobot_tugas' => 0, 'reason' => 'KHS sudah diterbitkan BAAK. Batalkan penerbitan sebelum menyinkronkan ulang nilai.'];
        }

        $tugasList = LmsTugas::where('jadwal_id', $jadwal->id)
            ->with('pengumpulan')
            ->get();
        $quizList = LmsQuiz::where('jadwal_id', $jadwal->id)
            ->with(['soal', 'attempts'])
            ->get();

        $totalMaksimal = (float) $tugasList->sum('nilai_maksimal')
            + (float) $quizList->sum(fn ($quiz) => $quiz->soal->sum('bobot'));
        $jumlahDinilai = $tugasList->sum(fn ($tugas) => $tugas->pengumpulan->whereNotNull('nilai')->count())
            + $quizList->sum(fn ($quiz) => $quiz->attempts
                ->where('status', 'graded')->whereNotNull('nilai_total')->count());

        if ($totalMaksimal <= 0 || $jumlahDinilai <= 0) {
            return [
                'synced' => 0,
                'total_maksimal' => $totalMaksimal,
                'bobot_tugas' => $this->weights($jadwal)['tugas'],
                'reason' => 'Belum ada nilai tugas atau quiz yang dapat disinkronkan.',
            ];
        }

        $kelasJadwal = strtolower((string) $jadwal->jenis_kelas);
        $pesertaKrs = Krs::with('mahasiswa')
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->where('ta_id', $jadwal->ta_id)
            ->whereHas('mahasiswa', function ($query) use ($kelasJadwal) {
                if ($kelasJadwal === 'karyawan') {
                    $query->whereRaw('LOWER(kelas) = ?', ['karyawan']);
                } else {
                    $query->where(function ($kelas) {
                        $kelas->whereNull('kelas')
                            ->orWhereRaw('LOWER(kelas) != ?', ['karyawan']);
                    });
                }
            })
            ->get();

        $pengumpulan = $tugasList->flatMap->pengumpulan
            ->keyBy(fn ($item) => $item->mahasiswa_id.'-'.$item->tugas_id);
        $attemptQuiz = $quizList->flatMap->attempts
            ->keyBy(fn ($item) => $item->mahasiswa_id.'-'.$item->quiz_id);
        $weights = $this->weights($jadwal);

        DB::transaction(function () use (
            $pesertaKrs, $tugasList, $quizList, $pengumpulan, $attemptQuiz, $totalMaksimal, $weights
        ) {
            foreach ($pesertaKrs as $krs) {
                $nilaiDiperoleh = 0;

                foreach ($tugasList as $tugas) {
                    $item = $pengumpulan->get($krs->mahasiswa_id.'-'.$tugas->tugas_id);
                    if ($item && $item->nilai !== null) {
                        $nilaiDiperoleh += (float) $item->nilai;
                    }
                }

                foreach ($quizList as $quiz) {
                    $item = $attemptQuiz->get($krs->mahasiswa_id.'-'.$quiz->quiz_id);
                    if ($item && $item->status === 'graded' && $item->nilai_total !== null) {
                        $nilaiDiperoleh += (float) $item->nilai_total;
                    }
                }

                $nilaiTugas = $this->normalizedScore($nilaiDiperoleh, $totalMaksimal);
                $nilaiAkhir = $this->finalScore([
                    'uts' => $krs->uts,
                    'uas' => $krs->uas,
                    'tugas' => $nilaiTugas,
                    'absensi' => $krs->absen,
                    'praktik' => $krs->praktik,
                ], $weights);

                $krs->update([
                    'tugas' => $nilaiTugas,
                    'akhir' => $nilaiAkhir,
                    'khs' => $this->letterGrade($nilaiAkhir),
                ]);
            }
        });

        $submission = NilaiSubmission::where('jadwal_id', $jadwal->id)->first();
        if ($submission) {
            $submission->update(['status' => 'submitted', 'submitted_at' => now(), 'reviewed_by_dosen_id' => null, 'reviewed_at' => null, 'review_note' => null]);
        }

        return [
            'synced' => $pesertaKrs->count(),
            'total_maksimal' => $totalMaksimal,
            'bobot_tugas' => $weights['tugas'],
            'reason' => null,
        ];
    }

    public function normalizedScore(float $nilaiDiperoleh, float $totalMaksimal): float
    {
        if ($totalMaksimal <= 0) {
            return 0;
        }

        return round(max(0, min(100, ($nilaiDiperoleh / $totalMaksimal) * 100)), 2);
    }

    public function finalScore(array $scores, array $weights): float
    {
        return round(
            ((float) ($scores['uts'] ?? 0) * (float) ($weights['uts'] ?? 0) / 100)
            + ((float) ($scores['uas'] ?? 0) * (float) ($weights['uas'] ?? 0) / 100)
            + ((float) ($scores['tugas'] ?? 0) * (float) ($weights['tugas'] ?? 0) / 100)
            + ((float) ($scores['praktik'] ?? 0) * (float) ($weights['praktik'] ?? 0) / 100)
            + ((float) ($scores['absensi'] ?? 0) * (float) ($weights['absensi'] ?? 0) / 100),
            2
        );
    }

    public function letterGrade(float $nilai): string
    {
        return match (true) {
            $nilai >= 85.5 => 'A',
            $nilai >= 78.5 => 'AB',
            $nilai >= 74.5 => 'BA',
            $nilai >= 70.5 => 'B',
            $nilai >= 66.5 => 'BC',
            $nilai >= 59.5 => 'C',
            $nilai >= 45.5 => 'D',
            default => 'E',
        };
    }

    private function weights(Jadwal $jadwal): array
    {
        $programStudi = $jadwal->kurikulum?->programStudi;
        $mataKuliah = $jadwal->kurikulum?->mataKuliah;
        $bobotCustom = null;

        if ($programStudi && $mataKuliah && Schema::hasTable('bobot_nilai')) {
            $bobotCustom = BobotNilai::where('program_studi_id', $programStudi->jurusan_id)
                ->where('matakuliah_id', $mataKuliah->matakuliah_id)
                ->first();
        }

        return [
            'uts' => $bobotCustom?->persen_uts ?? $programStudi?->persen_uts ?? 0,
            'uas' => $bobotCustom?->persen_uas ?? $programStudi?->persen_uas ?? 0,
            'tugas' => $bobotCustom?->persen_tugas ?? $programStudi?->persen_tugas ?? 0,
            'absensi' => $bobotCustom?->persen_absen ?? $programStudi?->persen_absen ?? 0,
            'praktik' => $bobotCustom?->persen_praktik ?? $programStudi?->persen_praktik ?? 0,
        ];
    }
}
