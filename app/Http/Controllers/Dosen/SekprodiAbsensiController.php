<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\TahunAkademik;
use App\Support\KrsClassResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SekprodiAbsensiController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'program_studi_id' => ['nullable', 'string'],
            'ta_id' => ['nullable', 'integer', 'exists:tahun_ajaran,ta_id'],
            'jenis' => ['nullable', Rule::in(['teori', 'praktik'])],
            'dosen_id' => ['nullable', 'integer', 'exists:dosen,dosen_id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $dosen = $request->user('dosen');
        $managedPrograms = $dosen->programStudiSebagaiSekprodi()->orderBy('nama')->get();
        abort_if($managedPrograms->isEmpty(), 403, 'Akun Anda belum ditetapkan sebagai Sekprodi.');

        $managedIds = $managedPrograms->pluck('jurusan_id')->map(fn ($id) => (string) $id);
        $programStudiId = (string) ($data['program_studi_id'] ?? $managedPrograms->first()->jurusan_id);
        abort_unless($managedIds->contains($programStudiId), 403, 'Anda bukan Sekprodi program studi ini.');

        $activeTaId = TahunAkademik::query()->where('status_ta', 1)->value('ta_id');
        $taId = (int) ($data['ta_id'] ?? $activeTaId);
        $jenis = $data['jenis'] ?? 'teori';
        $search = trim((string) ($data['search'] ?? ''));
        $dosenId = isset($data['dosen_id']) ? (int) $data['dosen_id'] : null;
        $table = $jenis === 'praktik' ? 'jadwal_praktik' : 'jadwal';
        $model = $jenis === 'praktik' ? JadwalPraktik::class : Jadwal::class;

        $jadwals = $model::query()
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.dosenToMatakuliah.dosen',
                'programStudi',
                'tahunAjaran',
                'ruangan',
            ])
            ->withCount('pertemuan')
            ->where("{$table}.jurusan_id", $programStudiId)
            ->when($taId, fn (Builder $query) => $query->where("{$table}.ta_id", $taId))
            ->when($dosenId, fn (Builder $query) => $query->whereHas(
                'kurikulum.dosenToMatakuliah',
                fn (Builder $assignment) => $assignment
                    ->where('dosen_id', $dosenId)
                    ->whereRaw('LOWER(jenis_dosen) = ?', [$jenis])
                    ->whereColumn('dosen_mata_kuliah.jenis_kelas', "{$table}.jenis_kelas")
            ))
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $filter) use ($search) {
                $filter->whereHas('kurikulum.mataKuliah', fn (Builder $mataKuliah) => $mataKuliah
                    ->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('matakuliah_id', 'like', '%'.$search.'%'))
                    ->orWhereHas('kurikulum.dosenToMatakuliah.dosen', fn (Builder $pengajar) => $pengajar
                        ->where('nama', 'like', '%'.$search.'%'));
            }))
            ->orderByRaw("FIELD({$table}.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy("{$table}.jam_mulai")
            ->paginate(20)
            ->withQueryString();

        $jadwals->getCollection()->each(function ($jadwal) use ($jenis) {
            $kelas = KrsClassResolver::normalize($jadwal->jenis_kelas);
            $pengajar = $jadwal->kurikulum?->dosenToMatakuliah
                ?->filter(fn ($assignment) => strtolower((string) $assignment->jenis_dosen) === $jenis
                    && KrsClassResolver::normalize($assignment->jenis_kelas) === $kelas)
                ->pluck('dosen.nama')
                ->filter()
                ->unique()
                ->values() ?? collect();

            $jadwal->setAttribute('nama_pengajar_sekprodi', $pengajar->join(', ') ?: 'Belum ditentukan');
        });

        $dosenPengajar = Dosen::query()
            ->whereHas('dosenMatakuliah', fn (Builder $assignment) => $assignment
                ->whereRaw('LOWER(jenis_dosen) = ?', [$jenis])
                ->whereHas('kurikulum', fn (Builder $kurikulum) => $kurikulum
                    ->where('jurusan_id', $programStudiId)
                    ->when($taId, fn (Builder $query) => $query->where('ta_id', $taId))))
            ->orderBy('nama')
            ->get(['dosen_id', 'nama']);

        activity_log('lihat_absensi_sekprodi', 'Sekprodi melihat rekap absensi prodi '.$programStudiId);

        return view('dosen.sekprodi.absensi', [
            'jadwals' => $jadwals,
            'managedPrograms' => $managedPrograms,
            'selectedProgramId' => $programStudiId,
            'tahunAkademik' => TahunAkademik::query()->orderByDesc('ta_id')->get(),
            'selectedTaId' => $taId,
            'selectedJenis' => $jenis,
            'selectedDosenId' => $dosenId,
            'dosenPengajar' => $dosenPengajar,
            'search' => $search,
        ]);
    }

    public function show(Request $request, string $jenis, int $id): View
    {
        abort_unless(in_array($jenis, ['teori', 'praktik'], true), 404);

        $model = $jenis === 'praktik' ? JadwalPraktik::class : Jadwal::class;
        $jadwal = $model::query()
            ->with(['kurikulum.mataKuliah', 'programStudi', 'tahunAjaran', 'ruangan'])
            ->findOrFail($id);
        $this->ensureManagedProgram($request, $jadwal->jurusan_id);

        $pertemuan = $jadwal->pertemuan()
            ->with('dosen')
            ->withCount([
                'absensi as hadir' => fn ($query) => $query->whereIn('status', ['H', 'Hadir', 'hadir']),
                'absensi as sakit' => fn ($query) => $query->whereIn('status', ['S', 'Sakit', 'sakit']),
                'absensi as izin' => fn ($query) => $query->whereIn('status', ['I', 'Izin', 'izin']),
                'absensi as alfa' => fn ($query) => $query->whereIn('status', ['A', 'T', 'Alpha', 'Alpa', 'Alfa', 'alpha', 'alpa', 'alfa']),
            ])
            ->orderBy('tanggal_pertemuan')
            ->orderBy('jam_mulai')
            ->get();

        activity_log('lihat_detail_absensi_sekprodi', 'Sekprodi melihat detail absensi '.$jenis.' ID '.$id);

        return view('dosen.sekprodi.absensi-detail', compact('jadwal', 'pertemuan', 'jenis'));
    }

    private function ensureManagedProgram(Request $request, $programStudiId): void
    {
        abort_unless(
            $request->user('dosen')->programStudiSebagaiSekprodi()
                ->where('program_studi.jurusan_id', $programStudiId)
                ->exists(),
            403,
            'Data absensi ini bukan milik program studi yang Anda kelola.'
        );
    }
}
