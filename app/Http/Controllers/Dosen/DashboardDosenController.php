<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\CalendarAkademik;
use App\Models\DosenMatakuliah;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\LmsPengumpulanTugas;
use App\Models\LmsQuizAttempt;
use App\Models\Mahasiswa;
use App\Models\Penilaian;
use App\Models\RpsRevision;
use App\Models\Setting;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardDosenController extends Controller
{
    public function index()
    {
        $dosen = auth('dosen')->user();
        $settings = Setting::first();
        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        $kalenderAkademik = CalendarAkademik::where('jurusan_id', $dosen->jurusan_id)
            ->aktif()
            ->whereNotNull('path')
            ->get()
            ->filter(fn (CalendarAkademik $kalender) => $kalender->fileExists())
            ->values();
        $tanggalSekarang = Carbon::now()->translatedFormat('l, d F Y');

        // Hitung statistik dosen
        $totalMatakuliah = DosenMatakuliah::where('dosen_id', $dosen->dosen_id)
            ->distinct('kurikulum_id')->count();

        $totalMahasiswaBimbingan = Mahasiswa::where('dosen_id', $dosen->dosen_id)
            ->where('status_mhs', 'aktif')->count();

        $rataRataEdomRaw = $ta
            ? Penilaian::where('dosen_id', $dosen->dosen_id)
                ->whereHas('kurikulum', fn ($query) => $query->where('ta_id', $ta->ta_id))
                ->avg(DB::raw('CAST(nilai AS UNSIGNED)'))
            : null;
        $rataRataEdom = $rataRataEdomRaw ? round($rataRataEdomRaw, 2) : 0;
        $lmsAnnouncements = $this->lmsAnnouncements($dosen->dosen_id);
        $teachingReminders = $this->teachingReminders($dosen->dosen_id, $ta);
        $rpsReplacementNotifications = $this->rpsReplacementNotifications($dosen->dosen_id, $ta);

        activity_log('akses_dashboard', 'Dosen mengakses dashboard');

        return view('dosen.dashboard', compact(
            'tanggalSekarang',
            'settings',
            'ta',
            'kalenderAkademik',
            'totalMatakuliah',
            'totalMahasiswaBimbingan',
            'rataRataEdom',
            'lmsAnnouncements',
            'teachingReminders',
            'rpsReplacementNotifications'
        ));
    }

    private function rpsReplacementNotifications(int $dosenId, ?TahunAkademik $tahunAkademik)
    {
        if (! $tahunAkademik) {
            return collect();
        }

        return RpsRevision::query()
            ->with(['uploader', 'kurikulum.mataKuliah', 'kurikulum.programStudi'])
            ->where('uploaded_by_dosen_id', '!=', $dosenId)
            ->whereHas('kurikulum', fn ($query) => $query->where('ta_id', $tahunAkademik->ta_id))
            ->whereHas('kurikulum.dosenToMatakuliah', fn ($query) => $query
                ->where('dosen_id', $dosenId)
                ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
                ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(rps_revisions.jenis_kelas)'))
            ->latest()
            ->limit(8)
            ->get();
    }

    private function teachingReminders(int $dosenId, ?TahunAkademik $tahunAkademik)
    {
        if (! $tahunAkademik) {
            return collect();
        }

        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $now->copy()->endOfWeek(Carbon::SUNDAY);

        $theory = Jadwal::with(['kurikulum.mataKuliah', 'programStudi'])
            ->withCount(['pertemuan as jumlah_pertemuan_dosen' => fn ($query) => $query
                ->where('dosen_id', $dosenId)])
            ->where('ta_id', $tahunAkademik->ta_id)
            ->whereHas('kurikulum.dosenToMatakuliah', fn ($query) => $query
                ->where('dosen_id', $dosenId)
                ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
                ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(jadwal.jenis_kelas)'))
            ->whereDoesntHave('pertemuan', fn ($query) => $query
                ->where('dosen_id', $dosenId)
                ->whereBetween('tanggal_pertemuan', [
                    $weekStart->toDateString(),
                    $weekEnd->toDateString(),
                ]))
            ->get()
            ->map(fn (Jadwal $jadwal) => $this->makeTeachingReminder(
                $jadwal,
                'teori',
                $now,
                $weekStart,
                route('dosen.jadwal.index')
            ));

        $practice = JadwalPraktik::with(['kurikulum.mataKuliah', 'programStudi'])
            ->withCount(['pertemuan as jumlah_pertemuan_dosen' => fn ($query) => $query
                ->where('dosen_id', $dosenId)])
            ->where('ta_id', $tahunAkademik->ta_id)
            ->whereHas('kurikulum.dosenToMatakuliah', fn ($query) => $query
                ->where('dosen_id', $dosenId)
                ->whereRaw('LOWER(jenis_dosen) = ?', ['praktik'])
                ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(jadwal_praktik.jenis_kelas)'))
            ->whereDoesntHave('pertemuan', fn ($query) => $query
                ->where('dosen_id', $dosenId)
                ->whereBetween('tanggal_pertemuan', [
                    $weekStart->toDateString(),
                    $weekEnd->toDateString(),
                ]))
            ->get()
            ->map(fn (JadwalPraktik $jadwal) => $this->makeTeachingReminder(
                $jadwal,
                'praktik',
                $now,
                $weekStart,
                route('dosen.jadwal-praktik.index')
            ));

        return $theory
            ->concat($practice)
            ->filter()
            ->sortBy('scheduled_at')
            ->values();
    }

    private function makeTeachingReminder(
        Jadwal|JadwalPraktik $jadwal,
        string $type,
        Carbon $now,
        Carbon $weekStart,
        string $url
    ): ?array {
        if ((int) $jadwal->jumlah_pertemuan_dosen >= 14) {
            return null;
        }

        $dayOffsets = [
            'senin' => 0,
            'selasa' => 1,
            'rabu' => 2,
            'kamis' => 3,
            'jumat' => 4,
            'jum\'at' => 4,
            'sabtu' => 5,
            'minggu' => 6,
        ];
        $day = strtolower(trim((string) $jadwal->hari));
        if (! array_key_exists($day, $dayOffsets)) {
            return null;
        }

        $scheduledAt = $weekStart->copy()->addDays($dayOffsets[$day]);
        $endTime = preg_match('/^\d{2}:\d{2}/', (string) $jadwal->jam_selesai, $matches)
            ? $matches[0]
            : '23:59';
        [$hour, $minute] = array_map('intval', explode(':', $endTime));
        $scheduledAt->setTime($hour, $minute);

        if ($scheduledAt->greaterThan($now)) {
            return null;
        }

        return [
            'schedule_id' => $jadwal->getKey(),
            'type' => $type,
            'course' => $jadwal->kurikulum?->mataKuliah?->nama ?? 'Mata kuliah',
            'semester' => $jadwal->kurikulum?->mataKuliah?->smt,
            'prodi' => $jadwal->programStudi?->nama ?? $jadwal->kurikulum?->programStudi?->nama ?? '-',
            'class' => jenis_kelas_label($jadwal->jenis_kelas),
            'day' => ucfirst($day),
            'time' => substr((string) $jadwal->jam_mulai, 0, 5)
                .' - '.substr((string) $jadwal->jam_selesai, 0, 5),
            'scheduled_at' => $scheduledAt,
            'url' => $url,
        ];
    }

    private function lmsAnnouncements(int $dosenId)
    {
        $pengumpulanTugas = LmsPengumpulanTugas::query()
            ->whereNotNull('waktu_upload')
            ->whereHas('tugas', fn ($query) => $query->where('dosen_id', $dosenId))
            ->with(['mahasiswa', 'tugas.jadwal.kurikulum.mataKuliah'])
            ->latest('waktu_upload')
            ->limit(8)
            ->get()
            ->map(function ($pengumpulan) {
                $tugas = $pengumpulan->tugas;

                return [
                    'type' => 'tugas',
                    'title' => ($pengumpulan->mahasiswa?->nama ?? 'Mahasiswa')
                        .' mengumpulkan tugas',
                    'detail' => $tugas?->judul ?? 'Tugas LMS',
                    'course' => $tugas?->jadwal?->kurikulum?->mataKuliah?->nama ?? '-',
                    'event_at' => $pengumpulan->waktu_upload,
                    'url' => $tugas
                        ? route('dosen.lms.tugas.pengumpulan', $tugas->tugas_id)
                        : route('dosen.lms.index'),
                    'icon' => 'bx-upload',
                    'color' => 'primary',
                    'pending' => $pengumpulan->nilai === null,
                ];
            });

        $quizSelesai = LmsQuizAttempt::query()
            ->whereNotNull('submitted_at')
            ->whereIn('status', ['submitted', 'graded'])
            ->whereHas('quiz', fn ($query) => $query->where('dosen_id', $dosenId))
            ->with(['mahasiswa', 'quiz.jadwal.kurikulum.mataKuliah'])
            ->latest('submitted_at')
            ->limit(8)
            ->get()
            ->map(function ($attempt) {
                $quiz = $attempt->quiz;

                return [
                    'type' => 'quiz',
                    'title' => ($attempt->mahasiswa?->nama ?? 'Mahasiswa')
                        .' menyelesaikan quiz',
                    'detail' => $quiz?->judul ?? 'Quiz LMS',
                    'course' => $quiz?->jadwal?->kurikulum?->mataKuliah?->nama ?? '-',
                    'event_at' => $attempt->submitted_at,
                    'url' => $quiz
                        ? route('dosen.lms.quiz.hasil', $quiz->quiz_id)
                        : route('dosen.lms.index'),
                    'icon' => 'bx-check-circle',
                    'color' => 'success',
                    'pending' => $attempt->status === 'submitted',
                ];
            });

        return $pengumpulanTugas
            ->concat($quizSelesai)
            ->sortByDesc('event_at')
            ->take(8)
            ->values();
    }

    public function hasilEdom()
    {
        $dosen = auth('dosen')->user();
        $tahunAkademikAktif = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);

        if (! $tahunAkademikAktif) {
            return view('dosen.edom.hasil', [
                'hasilEdom' => collect(),
                'rataRataKeseluruhan' => 0,
                'tahunAkademikAktif' => null,
            ]);
        }

        $hasilEdom = DB::table('penilaian')
            ->join('kurikulum', 'penilaian.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
            ->where('penilaian.dosen_id', $dosen->dosen_id)
            ->where('kurikulum.ta_id', $tahunAkademikAktif->ta_id)
            ->select(
                'kurikulum.kurikulum_id',
                'matakuliah.matakuliah_id',
                'matakuliah.nama',
                'matakuliah.smt',
                DB::raw('AVG(CAST(penilaian.nilai AS UNSIGNED)) as rata_rata_nilai'),
                DB::raw('COUNT(DISTINCT penilaian.mahasiswa_id) as jumlah_pemberi_nilai')
            )
            ->groupBy('kurikulum.kurikulum_id', 'matakuliah.matakuliah_id', 'matakuliah.nama', 'matakuliah.smt')
            ->get();

        $komentarPerKurikulum = DB::table('saran')
            ->join('kurikulum', 'saran.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->where('saran.dosen_id', $dosen->dosen_id)
            ->where('kurikulum.ta_id', $tahunAkademikAktif->ta_id)
            ->whereNotNull('saran.saran')
            ->whereRaw("TRIM(saran.saran) != ''")
            ->select('saran.kurikulum_id', 'saran.saran')
            ->orderBy('saran.id')
            ->get()
            ->groupBy('kurikulum_id');

        $hasilEdom->each(function ($item) use ($komentarPerKurikulum) {
            $item->komentar = $komentarPerKurikulum->get($item->kurikulum_id, collect())
                ->pluck('saran')
                ->values();
        });

        $rataRataKeseluruhan = DB::table('penilaian')
            ->join('kurikulum', 'penilaian.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->where('penilaian.dosen_id', $dosen->dosen_id)
            ->where('kurikulum.ta_id', $tahunAkademikAktif->ta_id)
            ->avg(DB::raw('CAST(nilai AS UNSIGNED)'));

        $rataRataKeseluruhan = $rataRataKeseluruhan ? round($rataRataKeseluruhan, 2) : 0;

        activity_log('lihat_edom', 'Dosen melihat hasil evaluasi EDOM');

        return view('dosen.edom.hasil', compact('hasilEdom', 'rataRataKeseluruhan', 'tahunAkademikAktif'));
    }
}
