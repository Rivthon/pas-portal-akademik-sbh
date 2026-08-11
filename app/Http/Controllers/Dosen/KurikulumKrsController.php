<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class KurikulumKrsController extends Controller
{
    public function index(Request $request)
    {
        $dosen = auth('dosen')->user();
        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        $relevantProdiIds = collect([$dosen->jurusan_id])
            ->merge(Mahasiswa::where('dosen_id', $dosen->dosen_id)
                ->where('status_mhs', 'aktif')
                ->pluck('jurusan_id'));

        if ($activeTa) {
            $relevantProdiIds = $relevantProdiIds->merge(
                Kurikulum::where('ta_id', $activeTa->ta_id)
                    ->whereHas('dosenToMatakuliah', fn (Builder $query) => $query
                        ->where('dosen_id', $dosen->dosen_id))
                    ->pluck('jurusan_id')
            );
        }

        $relevantProdiIds = $relevantProdiIds
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $programStudiList = ProgramStudi::query()
            ->when($relevantProdiIds->isNotEmpty(), fn (Builder $query) => $query
                ->whereIn('jurusan_id', $relevantProdiIds))
            ->orderBy('nama')
            ->get();

        $selectedProdi = (string) $request->input('prodi', $programStudiList->first()?->jurusan_id ?? '');
        if (! $programStudiList->contains(fn (ProgramStudi $prodi) => (string) $prodi->jurusan_id === $selectedProdi)) {
            $selectedProdi = (string) ($programStudiList->first()?->jurusan_id ?? '');
        }

        $jenisKelas = strtolower((string) $request->input('jenis_kelas', 'semua'));
        if (! in_array($jenisKelas, ['semua', 'reguler', 'karyawan'], true)) {
            $jenisKelas = 'semua';
        }

        $semester = $request->filled('semester') ? (int) $request->input('semester') : null;
        if ($semester !== null && ($semester < 1 || $semester > 14)) {
            $semester = null;
        }
        $search = trim((string) $request->input('search', ''));
        $kurikulumPerSemester = collect();
        $stats = ['mata_kuliah' => 0, 'sks' => 0, 'wajib' => 0, 'pilihan' => 0];

        if ($activeTa && $selectedProdi !== '') {
            $kurikulum = Kurikulum::with([
                'mataKuliah',
                'programStudi',
                'dosenToMatakuliah' => fn ($query) => $query
                    ->select('id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas'),
            ])
                ->where('ta_id', $activeTa->ta_id)
                ->where('jurusan_id', $selectedProdi)
                ->when($semester !== null, fn (Builder $query) => $query
                    ->whereHas('mataKuliah', fn (Builder $mataKuliah) => $mataKuliah
                        ->where('smt', $semester)))
                ->when($jenisKelas !== 'semua', fn (Builder $query) => $query
                    ->whereHas('dosenToMatakuliah', fn (Builder $penugasan) => $penugasan
                        ->whereRaw('LOWER(jenis_kelas) = ?', [$jenisKelas])))
                ->when($search !== '', fn (Builder $query) => $query
                    ->whereHas('mataKuliah', fn (Builder $mataKuliah) => $mataKuliah
                        ->where(function (Builder $filter) use ($search) {
                            $filter->where('nama', 'like', '%'.$search.'%')
                                ->orWhere('matakuliah_id', 'like', '%'.$search.'%');
                        })))
                ->get()
                ->map(function (Kurikulum $item) {
                    $kelas = $item->dosenToMatakuliah
                        ->pluck('jenis_kelas')
                        ->map(fn ($kelas) => strtolower(trim((string) $kelas)))
                        ->filter(fn ($kelas) => in_array($kelas, ['reguler', 'karyawan'], true))
                        ->unique()
                        ->sort()
                        ->values();
                    $jenisPbm = $item->dosenToMatakuliah
                        ->pluck('jenis_dosen')
                        ->map(fn ($jenis) => strtolower(trim((string) $jenis)))
                        ->filter(fn ($jenis) => in_array($jenis, ['teori', 'praktik'], true))
                        ->unique()
                        ->sort()
                        ->values();

                    $item->setAttribute('kelas_tersedia', $kelas);
                    $item->setAttribute('jenis_pbm', $jenisPbm);

                    return $item;
                })
                ->sortBy(fn (Kurikulum $item) => sprintf(
                    '%02d-%s',
                    (int) ($item->mataKuliah?->smt ?? 0),
                    strtolower((string) ($item->mataKuliah?->nama ?? ''))
                ))
                ->values();

            $kurikulumPerSemester = $kurikulum
                ->groupBy(fn (Kurikulum $item) => (int) ($item->mataKuliah?->smt ?? 0))
                ->sortKeys();
            $stats = [
                'mata_kuliah' => $kurikulum->count(),
                'sks' => $kurikulum->sum(fn (Kurikulum $item) => (int) ($item->mataKuliah?->sks ?? 0)),
                'wajib' => $kurikulum->filter(fn (Kurikulum $item) => (int) ($item->mataKuliah?->kategori_mk ?? -1) === 0)->count(),
                'pilihan' => $kurikulum->filter(fn (Kurikulum $item) => (int) ($item->mataKuliah?->kategori_mk ?? -1) === 1)->count(),
            ];
        }

        activity_log('lihat_kurikulum_krs', 'Dosen melihat referensi Kurikulum KRS');

        return view('dosen.kurikulum-krs.index', compact(
            'activeTa',
            'programStudiList',
            'selectedProdi',
            'jenisKelas',
            'semester',
            'search',
            'kurikulumPerSemester',
            'stats'
        ));
    }
}
