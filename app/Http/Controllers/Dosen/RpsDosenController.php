<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\DosenMataKuliah;
use App\Models\Rps;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RpsDosenController extends Controller
{
    public function index()
    {
        $dosen = Auth::guard('dosen')->user();
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTA) {
            return back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $mataKuliah = DosenMataKuliah::with([
            'kurikulum.mataKuliah',
            'kurikulum.programStudi',
        ])
            ->where('dosen_id', $dosen->dosen_id)
            ->where('jenis_dosen', 'teori')
            ->whereHas('kurikulum', fn ($query) => $query->where('ta_id', $activeTA->ta_id))
            ->orderBy('kurikulum_id')
            ->get();
        $mataKuliah = $mataKuliah
            ->unique(function ($item) {

                return implode('_', [
                    $item->kurikulum_id,
                    $item->jenis_kelas,
                ]);

            })
            ->values();
        // Ambil RPS berdasarkan kurikulum + jenis_kelas
        foreach ($mataKuliah as $item) {

            $item->rps = Rps::where('kurikulum_id', $item->kurikulum_id)
                ->where('jenis_kelas', $item->jenis_kelas)
                ->first();
        }

        return view('dosen.rps.index', compact(
            'mataKuliah'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kurikulum_id' => 'required|exists:kurikulum,kurikulum_id',
            'jenis_kelas' => 'required|in:reguler,karyawan',
            'file' => 'required|mimes:pdf|max:10240',
        ]);

        $dosen = Auth::guard('dosen')->user();
        $activeTA = TahunAkademik::where('status_ta', 1)->firstOrFail();

        $isAssigned = DosenMataKuliah::where('dosen_id', $dosen->dosen_id)
            ->where('kurikulum_id', $request->kurikulum_id)
            ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
            ->whereRaw('LOWER(jenis_kelas) = ?', [strtolower($request->jenis_kelas)])
            ->whereHas('kurikulum', fn ($query) => $query->where('ta_id', $activeTA->ta_id))
            ->exists();

        abort_unless($isAssigned, 403, 'Mata kuliah ini tidak termasuk pengajaran Anda pada semester aktif.');

        $rps = Rps::where('kurikulum_id', $request->kurikulum_id)
            ->where('jenis_kelas', $request->jenis_kelas)
            ->first();

        $isReplacement = (bool) $rps;
        $oldPath = $rps?->file;
        $namaAsli = $request->file('file')->getClientOriginalName();
        $namaFile = 'RPS_'.
            $request->kurikulum_id.'_'.
            $request->jenis_kelas.'_'.
            Str::uuid().
            '.pdf';

        $path = $request->file('file')->storeAs(
            'rps',
            $namaFile,
            'public'
        );

        try {
            DB::transaction(function () use ($request, $dosen, $namaAsli, $path) {
                Rps::updateOrCreate(
                    [
                        'kurikulum_id' => $request->kurikulum_id,
                        'jenis_kelas' => $request->jenis_kelas,
                    ],
                    [
                        'dosen_id' => $dosen->dosen_id,
                        'nama_file' => $namaAsli,
                        'file' => $path,
                        'status' => 1,
                    ]
                );
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }

        if ($oldPath && $oldPath !== $path && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        activity_log(
            $isReplacement ? 'upload_ulang_rps' : 'upload_rps',
            ($isReplacement ? 'Upload ulang RPS : ' : 'Upload RPS : ').$request->kurikulum_id.
            ' ('.$request->jenis_kelas.')'
        );

        return back()->with('success', $isReplacement
            ? 'RPS berhasil diupload ulang dan file lama telah diganti.'
            : 'RPS berhasil diupload.');
    }

    public function show(Rps $rps)
    {
        $dosen = Auth::guard('dosen')->user();
        $isAssigned = DosenMataKuliah::where('dosen_id', $dosen->dosen_id)
            ->where('kurikulum_id', $rps->kurikulum_id)
            ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
            ->whereRaw('LOWER(jenis_kelas) = ?', [strtolower((string) $rps->jenis_kelas)])
            ->exists();

        abort_unless($isAssigned, 403, 'Anda tidak memiliki akses ke RPS ini.');
        abort_unless($rps->file && Storage::disk('public')->exists($rps->file), 404, 'File RPS tidak ditemukan.');

        $response = response()->file(Storage::disk('public')->path($rps->file), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.($rps->nama_file ?: basename($rps->file)).'"',
        ]);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
