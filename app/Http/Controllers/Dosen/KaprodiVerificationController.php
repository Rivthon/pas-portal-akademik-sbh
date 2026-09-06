<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\DosenMatakuliah;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\KaprodiAbsensiVerification;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\NilaiSubmission;
use App\Models\Pertemuan;
use App\Models\Rps;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KaprodiVerificationController extends Controller
{
    private function prodiIds()
    {
        return auth('dosen')->user()->programStudiDipimpin()->pluck('jurusan_id');
    }

    private function ensureKaprodi($programStudiId): void
    {
        abort_unless($this->prodiIds()->contains((string) $programStudiId) || $this->prodiIds()->contains((int) $programStudiId), 403, 'Anda bukan Kaprodi program studi ini.');
    }

    public function monitoring(Request $request)
    {
        $programStudiDipimpin = auth('dosen')->user()->programStudiDipimpin()->orderBy('nama')->get();
        abort_if($programStudiDipimpin->isEmpty(), 403, 'Akun Anda belum ditetapkan sebagai Kaprodi.');

        $programStudiId = $request->integer('program_studi_id') ?: (int) $programStudiDipimpin->first()->jurusan_id;
        $this->ensureKaprodi($programStudiId);

        $taId = $request->integer('ta_id') ?: TahunAkademik::where('status_ta', 1)->value('ta_id');
        $tahunAjaran = TahunAkademik::orderByDesc('ta_id')->get();
        $tahunAkademikTerpilih = $tahunAjaran->firstWhere('ta_id', $taId);
        $search = trim((string) $request->input('search', ''));
        $searchLower = mb_strtolower($search);
        $today = now()->toDateString();

        $assignments = DosenMatakuliah::with(['dosen', 'kurikulum.mataKuliah'])
            ->whereHas('kurikulum', fn ($query) => $query
                ->where('jurusan_id', $programStudiId)
                ->when($taId, fn ($q) => $q->where('ta_id', $taId)))
            ->get()
            ->filter(fn ($item) => $item->dosen && $item->kurikulum && $item->kurikulum->mataKuliah)
            ->unique(fn ($item) => implode('|', [
                $item->dosen_id,
                $item->kurikulum_id,
                strtolower((string) $item->jenis_kelas),
                strtolower((string) $item->jenis_dosen),
            ]));

        $jadwalTeori = Jadwal::with(['pertemuan' => fn ($query) => $query->whereDate('tanggal_pertemuan', '<=', $today)])
            ->where('jurusan_id', $programStudiId)
            ->when($taId, fn ($query) => $query->where('ta_id', $taId))
            ->get()
            ->groupBy(fn ($item) => $item->kurikulum_id.'|'.strtolower((string) $item->jenis_kelas));

        $jadwalPraktik = JadwalPraktik::with(['pertemuan' => fn ($query) => $query->whereDate('tanggal_pertemuan', '<=', $today)])
            ->where('jurusan_id', $programStudiId)
            ->when($taId, fn ($query) => $query->where('ta_id', $taId))
            ->get()
            ->groupBy(fn ($item) => $item->kurikulum_id.'|'.strtolower((string) $item->jenis_kelas));

        $progressRows = $assignments->map(function ($assignment) use ($jadwalTeori, $jadwalPraktik) {
            $jenis = strtolower((string) $assignment->jenis_dosen) === 'praktik' ? 'praktik' : 'teori';
            $key = $assignment->kurikulum_id.'|'.strtolower((string) $assignment->jenis_kelas);
            $schedules = ($jenis === 'praktik' ? $jadwalPraktik : $jadwalTeori)->get($key, collect());
            $pertemuan = $schedules->flatMap->pertemuan
                ->filter(fn ($item) => (string) $item->dosen_id === (string) $assignment->dosen_id);
            $jumlah = $pertemuan->count();

            return (object) [
                'dosen_id' => $assignment->dosen_id,
                'dosen' => $assignment->dosen->nama,
                'nidn' => $assignment->dosen->nidn ?: $assignment->dosen->kd_dosen,
                'mata_kuliah' => $assignment->kurikulum->mataKuliah->nama,
                'kode' => $assignment->kurikulum->mataKuliah->matakuliah_id,
                'semester' => $assignment->kurikulum->mataKuliah->smt,
                'jenis_kelas' => $assignment->jenis_kelas,
                'jenis' => $jenis,
                'jumlah' => $jumlah,
                'persen' => min(100, round(($jumlah / 14) * 100)),
                'terakhir' => $pertemuan->max('tanggal_pertemuan'),
                'online' => $pertemuan->filter(fn ($item) => strtolower((string) $item->metode_pbm) === 'online')->count(),
                'offline' => $pertemuan->filter(fn ($item) => strtolower((string) $item->metode_pbm) === 'offline')->count(),
            ];
        })->filter(fn ($row) => $search === '' || str_contains(mb_strtolower($row->dosen.' '.$row->nidn.' '.$row->mata_kuliah.' '.$row->kode), $searchLower))->values();

        $rpsMap = Rps::with('dosen')
            ->whereIn('kurikulum_id', $assignments->pluck('kurikulum_id')->unique())
            ->get()
            ->keyBy(fn ($item) => $item->kurikulum_id.'|'.strtolower((string) $item->jenis_kelas));

        $rpsRows = $assignments->groupBy(fn ($item) => $item->kurikulum_id.'|'.strtolower((string) $item->jenis_kelas))
            ->map(function ($group, $key) use ($rpsMap) {
                $assignment = $group->first();
                $rps = $rpsMap->get($key);

                return (object) [
                    'mata_kuliah' => $assignment->kurikulum->mataKuliah->nama,
                    'kode' => $assignment->kurikulum->mataKuliah->matakuliah_id,
                    'semester' => $assignment->kurikulum->mataKuliah->smt,
                    'jenis_kelas' => $assignment->jenis_kelas,
                    'dosen' => $group->pluck('dosen.nama')->filter()->unique()->join(', '),
                    'tersedia' => (bool) $rps,
                    'pengunggah' => $rps?->dosen?->nama,
                    'diperbarui' => $rps?->updated_at,
                ];
            })->filter(fn ($row) => $search === '' || str_contains(mb_strtolower($row->dosen.' '.$row->mata_kuliah.' '.$row->kode), $searchLower))->values();

        $mahasiswa = Mahasiswa::with(['dosen', 'krs' => fn ($query) => $query
            ->when($taId, fn ($q) => $q->where('ta_id', $taId))
            ->whereHas('kurikulum', fn ($q) => $q->where('jurusan_id', $programStudiId))])
            ->where('jurusan_id', $programStudiId)
            ->where('status_mhs', 'aktif')
            ->get();

        $krsRows = $mahasiswa->groupBy(fn ($item) => $item->dosen_id ?: 'belum-ditentukan')
            ->map(function ($group) {
                $dosen = $group->first()->dosen;
                $sudah = $group->filter(fn ($item) => $item->krs->isNotEmpty());
                $disetujui = $sudah->filter(fn ($item) => $item->krs->every(fn ($krs) => filled($krs->disetujui_pada)));

                return (object) [
                    'dosen' => $dosen?->nama ?: 'Belum ditentukan',
                    'total' => $group->count(),
                    'sudah' => $sudah->count(),
                    'belum' => $group->count() - $sudah->count(),
                    'menunggu' => $sudah->count() - $disetujui->count(),
                    'disetujui' => $disetujui->count(),
                ];
            })->filter(fn ($row) => $search === '' || str_contains(mb_strtolower($row->dosen), $searchLower))->sortBy('dosen')->values();

        $edomRows = DB::table('penilaian')
            ->join('kurikulum', 'penilaian.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
            ->join('dosen', 'penilaian.dosen_id', '=', 'dosen.dosen_id')
            ->where('kurikulum.jurusan_id', $programStudiId)
            ->when($taId, fn ($query) => $query->where('kurikulum.ta_id', $taId))
            ->select('penilaian.dosen_id', 'penilaian.kurikulum_id', 'dosen.nama as dosen', 'matakuliah.nama as mata_kuliah', 'matakuliah.matakuliah_id as kode',
                DB::raw('AVG(CAST(penilaian.nilai AS UNSIGNED)) as rata_rata'),
                DB::raw('COUNT(DISTINCT penilaian.mahasiswa_id) as responden'))
            ->groupBy('penilaian.dosen_id', 'penilaian.kurikulum_id', 'dosen.nama', 'matakuliah.nama', 'matakuliah.matakuliah_id')
            ->get();

        $komentar = DB::table('saran')
            ->join('kurikulum', 'saran.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->where('kurikulum.jurusan_id', $programStudiId)
            ->when($taId, fn ($query) => $query->where('kurikulum.ta_id', $taId))
            ->whereNotNull('saran.saran')->whereRaw("TRIM(saran.saran) != ''")
            ->select('saran.dosen_id', 'saran.kurikulum_id', 'saran.saran')
            ->orderByDesc('saran.id')->get()
            ->groupBy(fn ($item) => $item->dosen_id.'|'.$item->kurikulum_id);

        $edomRows->each(function ($row) use ($komentar) {
            $row->rata_rata = round((float) $row->rata_rata, 2);
            $row->komentar = $komentar->get($row->dosen_id.'|'.$row->kurikulum_id, collect())->pluck('saran')->take(10)->values();
        });
        $edomRows = $edomRows->filter(fn ($row) => $search === '' || str_contains(mb_strtolower($row->dosen.' '.$row->mata_kuliah.' '.$row->kode), $searchLower))->values();

        $summary = (object) [
            'dosen' => $assignments->pluck('dosen_id')->merge($mahasiswa->pluck('dosen_id'))->filter()->unique()->count(),
            'pertemuan' => $progressRows->sum('jumlah'),
            'rps_total' => $rpsRows->count(),
            'rps_tersedia' => $rpsRows->where('tersedia', true)->count(),
            'mahasiswa' => $mahasiswa->count(),
            'krs_disetujui' => $krsRows->sum('disetujui'),
            'edom' => $edomRows->count() ? round($edomRows->avg('rata_rata'), 2) : 0,
        ];

        activity_log('monitoring_kaprodi', 'Kaprodi membuka monitoring dosen program studi '.$programStudiId);

        return view('dosen.kaprodi.monitoring', compact(
            'programStudiDipimpin', 'programStudiId', 'tahunAjaran', 'tahunAkademikTerpilih', 'taId', 'search',
            'progressRows', 'rpsRows', 'krsRows', 'edomRows', 'summary'
        ));
    }

    public function absensi(Request $request)
    {
        $prodiIds = $this->prodiIds();
        abort_if($prodiIds->isEmpty(), 403, 'Akun Anda belum ditetapkan sebagai Kaprodi.');
        $taId = $request->integer('ta_id') ?: TahunAkademik::where('status_ta', 1)->value('ta_id');
        $search = trim((string) $request->input('search', ''));
        $jadwals = Jadwal::with(['kurikulum.mataKuliah', 'programStudi', 'tahunAjaran', 'kurikulum.dosenToMatakuliah.dosen'])
            ->withCount('pertemuan')->whereIn('jurusan_id', $prodiIds)->when($taId, fn ($q) => $q->where('ta_id', $taId))
            ->when($search !== '', fn ($query) => $query->whereHas('kurikulum.mataKuliah', function ($mataKuliah) use ($search) {
                $mataKuliah->where('nama', 'like', '%'.$search.'%')->orWhere('matakuliah_id', 'like', '%'.$search.'%');
            }))
            ->orderBy('jurusan_id')->orderBy('hari')->paginate(15)->withQueryString();
        $verifications = KaprodiAbsensiVerification::whereIn('jadwal_id', $jadwals->pluck('id'))->get()->keyBy('jadwal_id');
        $latestUpdates = Absensi::whereIn('jadwal_id', $jadwals->pluck('id'))->selectRaw('jadwal_id, MAX(updated_at) latest')->groupBy('jadwal_id')->pluck('latest', 'jadwal_id');
        $tahunAjaran = TahunAkademik::orderByDesc('ta_id')->get();

        return view('dosen.kaprodi.absensi', compact('jadwals', 'verifications', 'latestUpdates', 'tahunAjaran', 'taId', 'search'));
    }

    public function absensiDetail(Jadwal $jadwal)
    {
        $this->ensureKaprodi($jadwal->jurusan_id);
        $jadwal->load(['kurikulum.mataKuliah', 'programStudi', 'tahunAjaran', 'pertemuan.dosen']);
        $pertemuan = $jadwal->pertemuan()->withCount([
            'absensi as hadir' => fn ($q) => $q->where('status', 'H'), 'absensi as sakit' => fn ($q) => $q->where('status', 'S'),
            'absensi as izin' => fn ($q) => $q->where('status', 'I'), 'absensi as alfa' => fn ($q) => $q->whereIn('status', ['A', 'T']),
        ])->orderBy('tanggal_pertemuan')->get();
        $verification = KaprodiAbsensiVerification::where('jadwal_id', $jadwal->id)->first();

        return view('dosen.kaprodi.absensi-detail', compact('jadwal', 'pertemuan', 'verification'));
    }

    public function verifyAbsensi(Jadwal $jadwal)
    {
        $this->ensureKaprodi($jadwal->jurusan_id);
        abort_unless(Pertemuan::where('jadwal_id', $jadwal->id)->exists(), 422, 'Belum ada pertemuan yang dapat diverifikasi.');
        $sourceUpdatedAt = Absensi::where('jadwal_id', $jadwal->id)->max('updated_at') ?: Pertemuan::where('jadwal_id', $jadwal->id)->max('updated_at');
        KaprodiAbsensiVerification::updateOrCreate(['jadwal_id' => $jadwal->id], [
            'program_studi_id' => $jadwal->jurusan_id, 'verified_by_dosen_id' => auth('dosen')->id(), 'verified_at' => now(), 'source_updated_at' => $sourceUpdatedAt,
        ]);
        activity_log('verifikasi_absensi_kaprodi', 'Kaprodi memverifikasi rekap absensi jadwal '.$jadwal->id);

        return back()->with('success', 'Rekap absensi berhasil diverifikasi.');
    }

    public function bulkVerifyAbsensi(Request $request)
    {
        $data = $request->validate([
            'jadwal_ids' => ['required', 'array', 'min:1', 'max:100'],
            'jadwal_ids.*' => ['required', 'integer', 'distinct', 'exists:jadwal,id'],
        ], [
            'jadwal_ids.required' => 'Pilih minimal satu rekap absensi.',
            'jadwal_ids.max' => 'Maksimal 100 rekap dapat diverifikasi sekaligus.',
        ]);

        $jadwalIds = collect($data['jadwal_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $prodiIds = $this->prodiIds()->map(fn ($id) => (string) $id);

        $verifiedCount = DB::transaction(function () use ($jadwalIds, $prodiIds) {
            $jadwals = Jadwal::withCount('pertemuan')
                ->whereIn('id', $jadwalIds)
                ->lockForUpdate()
                ->get();

            abort_unless(
                $jadwals->count() === $jadwalIds->count()
                && $jadwals->every(fn ($jadwal) => $prodiIds->contains((string) $jadwal->jurusan_id)),
                403,
                'Terdapat rekap absensi di luar program studi yang Anda pimpin.'
            );

            if ($jadwals->contains(fn ($jadwal) => $jadwal->pertemuan_count < 1)) {
                abort(422, 'Semua rekap yang dipilih harus memiliki minimal satu pertemuan.');
            }

            $latestAbsensi = Absensi::whereIn('jadwal_id', $jadwalIds)
                ->selectRaw('jadwal_id, MAX(updated_at) as latest')
                ->groupBy('jadwal_id')
                ->pluck('latest', 'jadwal_id');
            $latestPertemuan = Pertemuan::whereIn('jadwal_id', $jadwalIds)
                ->selectRaw('jadwal_id, MAX(updated_at) as latest')
                ->groupBy('jadwal_id')
                ->pluck('latest', 'jadwal_id');
            $verifiedAt = now();

            foreach ($jadwals as $jadwal) {
                KaprodiAbsensiVerification::updateOrCreate(
                    ['jadwal_id' => $jadwal->id],
                    [
                        'program_studi_id' => $jadwal->jurusan_id,
                        'verified_by_dosen_id' => auth('dosen')->id(),
                        'verified_at' => $verifiedAt,
                        'source_updated_at' => $latestAbsensi->get($jadwal->id) ?: $latestPertemuan->get($jadwal->id),
                    ]
                );
            }

            return $jadwals->count();
        });

        activity_log(
            'verifikasi_absensi_kaprodi_massal',
            'Kaprodi memverifikasi '.$verifiedCount.' rekap absensi sekaligus. Jadwal: '.$jadwalIds->join(', ')
        );

        return back()->with('success', $verifiedCount.' rekap absensi berhasil diverifikasi.');
    }

    public function nilai(Request $request)
    {
        $prodiIds = $this->prodiIds();
        abort_if($prodiIds->isEmpty(), 403, 'Akun Anda belum ditetapkan sebagai Kaprodi.');
        $status = $request->input('status');
        $programStudiDipimpin = auth('dosen')->user()->programStudiDipimpin()->orderBy('nama')->get();
        $submissions = NilaiSubmission::with(['jadwal.kurikulum.mataKuliah', 'jadwal.tahunAjaran', 'submitter', 'reviewer'])
            ->whereIn('program_studi_id', $prodiIds)->when($status, fn ($q) => $q->where('status', $status))->latest('submitted_at')->paginate(15)->withQueryString();

        return view('dosen.kaprodi.nilai', compact('submissions', 'status', 'programStudiDipimpin'));
    }

    public function approveNilai(NilaiSubmission $submission)
    {
        $this->ensureKaprodi($submission->program_studi_id);
        abort_unless($submission->status === 'submitted', 422, 'Nilai ini sudah diproses.');
        $submission->update(['status' => 'approved', 'reviewed_by_dosen_id' => auth('dosen')->id(), 'reviewed_at' => now(), 'review_note' => null]);
        activity_log('verifikasi_nilai_kaprodi', 'Kaprodi menyetujui pengajuan nilai jadwal '.$submission->jadwal_id);

        return back()->with('success', 'Nilai berhasil di-ACC dan siap diterbitkan BAAK.');
    }

    public function bulkApproveNilai(Request $request)
    {
        $data = $request->validate([
            'submission_ids' => ['required', 'array', 'min:1', 'max:100'],
            'submission_ids.*' => ['required', 'integer', 'distinct', 'exists:nilai_submissions,id'],
        ], [
            'submission_ids.required' => 'Pilih minimal satu pengajuan nilai.',
            'submission_ids.max' => 'Maksimal 100 pengajuan dapat disetujui sekaligus.',
        ]);

        $submissionIds = collect($data['submission_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $prodiIds = $this->prodiIds()->map(fn ($id) => (string) $id);

        $approvedCount = DB::transaction(function () use ($submissionIds, $prodiIds) {
            $submissions = NilaiSubmission::whereIn('id', $submissionIds)
                ->lockForUpdate()
                ->get();

            abort_unless(
                $submissions->count() === $submissionIds->count()
                && $submissions->every(fn ($submission) => $prodiIds->contains((string) $submission->program_studi_id)),
                403,
                'Terdapat pengajuan nilai di luar program studi yang Anda pimpin.'
            );

            $eligible = $submissions->where('status', 'submitted');
            $reviewedAt = now();

            foreach ($eligible as $submission) {
                $submission->update([
                    'status' => 'approved',
                    'reviewed_by_dosen_id' => auth('dosen')->id(),
                    'reviewed_at' => $reviewedAt,
                    'review_note' => null,
                ]);
            }

            return $eligible->count();
        });

        if ($approvedCount < 1) {
            return back()->with('warning', 'Pengajuan yang dipilih sudah diproses sebelumnya.');
        }

        activity_log(
            'verifikasi_nilai_kaprodi_massal',
            'Kaprodi menyetujui '.$approvedCount.' pengajuan nilai sekaligus. Pengajuan: '.$submissionIds->join(', ')
        );

        return back()->with('success', $approvedCount.' pengajuan nilai berhasil di-ACC dan siap diterbitkan BAAK.');
    }

    public function nilaiDetail(NilaiSubmission $submission)
    {
        $this->ensureKaprodi($submission->program_studi_id);
        $submission->load(['jadwal.kurikulum.mataKuliah', 'jadwal.programStudi', 'jadwal.tahunAjaran', 'submitter', 'reviewer']);
        $kelas = strtolower((string) $submission->jadwal->jenis_kelas);
        $nilai = Krs::with('mahasiswa')->where('kurikulum_id', $submission->jadwal->kurikulum_id)
            ->where('ta_id', $submission->jadwal->ta_id)->whereHas('mahasiswa', function ($query) use ($kelas) {
                if ($kelas === 'karyawan') {
                    $query->whereRaw('LOWER(kelas) = ?', ['karyawan']);
                } else {
                    $query->where(fn ($q) => $q->whereNull('kelas')->orWhereRaw('LOWER(kelas) != ?', ['karyawan']));
                }
            })->get()->sortBy(fn ($item) => $item->mahasiswa?->nim);

        return view('dosen.kaprodi.nilai-detail', compact('submission', 'nilai'));
    }

    public function revisionNilai(Request $request, NilaiSubmission $submission)
    {
        $this->ensureKaprodi($submission->program_studi_id);
        $data = $request->validate(['review_note' => 'required|string|max:1000']);
        $submission->update(['status' => 'revision', 'reviewed_by_dosen_id' => auth('dosen')->id(), 'reviewed_at' => now(), 'review_note' => $data['review_note']]);

        return back()->with('success', 'Nilai dikembalikan kepada dosen untuk diperbaiki.');
    }
}
