<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\KaprodiAbsensiVerification;
use App\Models\Krs;
use App\Models\NilaiSubmission;
use App\Models\Pertemuan;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;

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
