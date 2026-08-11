<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\DosenMatakuliah;
use App\Models\ProgramStudi;
use App\Models\Rps;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RpsController extends Controller
{
    public function index(Request $request)
    {
        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        $taId = $request->input('ta_id', $activeTa?->ta_id);

        $assignments = DosenMatakuliah::query()
            ->with(['dosen', 'kurikulum.mataKuliah', 'kurikulum.programStudi'])
            ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
            ->when($taId, fn ($query) => $query
                ->whereHas('kurikulum', fn ($kurikulum) => $kurikulum->where('ta_id', $taId)))
            ->when($request->filled('prodi_id'), fn ($query) => $query
                ->whereHas('kurikulum', fn ($kurikulum) => $kurikulum
                    ->where('jurusan_id', $request->prodi_id)))
            ->when($request->filled('jenis_kelas'), fn ($query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', [strtolower($request->jenis_kelas)]))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);
                $query->where(function ($query) use ($search) {
                    $query->whereHas('kurikulum.mataKuliah', fn ($mataKuliah) => $mataKuliah
                        ->where('nama', 'like', '%'.$search.'%')
                        ->orWhere('matakuliah_id', 'like', '%'.$search.'%'))
                        ->orWhereHas('dosen', fn ($dosen) => $dosen
                            ->where('nama', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('kurikulum_id')
            ->get();

        $rpsByClass = Rps::whereIn('kurikulum_id', $assignments->pluck('kurikulum_id')->unique())
            ->get()->keyBy(fn ($rps) => $rps->kurikulum_id.'-'.strtolower((string) $rps->jenis_kelas));

        $mataKuliah = $assignments
            ->groupBy(fn ($item) => $item->kurikulum_id.'-'.strtolower((string) $item->jenis_kelas))
            ->map(function ($group, $key) use ($rpsByClass) {
                $item = $group->first();
                $item->setRelation('rpsAdmin', $rpsByClass->get($key));
                $item->dosen_pengampu = $group->pluck('dosen.nama')->filter()->unique()->values();

                return $item;
            })->values();

        return view('admin.akademik.rps.index', [
            'mataKuliah' => $mataKuliah,
            'tahunAkademik' => TahunAkademik::orderByDesc('ta_id')->get(),
            'programStudi' => ProgramStudi::orderBy('nama')->get(),
            'selectedTaId' => $taId,
        ]);
    }

    public function show(Rps $rps)
    {
        abort_unless($rps->file && Storage::disk('public')->exists($rps->file), 404);

        return response()->file(Storage::disk('public')->path($rps->file), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.($rps->nama_file ?: basename($rps->file)).'"',
        ]);
    }
}
