<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\Kurikulum;
use App\Models\Rps;
use App\Models\TahunAkademik;
use App\Support\KrsClassResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RpsMhsController extends Controller
{
    private function getActiveTA()
    {
        return TahunAkademik::where('status_ta', 1)->first();
    }

    public function index()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        /*
        |---------------------------------------
        | Konversi kelas mahasiswa
        | pagi -> reguler
        |---------------------------------------
        */

        /*
        |---------------------------------------
        | Ambil seluruh KRS mahasiswa
        |---------------------------------------
        */

        // Ambil Tahun Ajaran aktif
        $activeTA = $this->getActiveTA();
        if (! $activeTA) {
            return back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $krsRecords = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $activeTA->ta_id)
            ->get()
            ->keyBy('kurikulum_id');

        $krs = Kurikulum::with([
            'programStudi',
            'mataKuliah',
        ])
            ->where('ta_id', $activeTA->ta_id)
            ->whereIn('kurikulum_id', $krsRecords->keys())
            ->orderBy('kurikulum_id')
            ->get();

        /*
        |---------------------------------------
        | Cari RPS sesuai kelas mahasiswa
        |---------------------------------------
        */

        foreach ($krs as $item) {
            $krsItem = $krsRecords->get($item->kurikulum_id);
            $item->jenis_kelas_krs = KrsClassResolver::forKrs($krsItem, $mahasiswa);

            $item->rps = Rps::where('kurikulum_id', $item->kurikulum_id)
                ->where('jenis_kelas', $item->jenis_kelas_krs)
                ->first();

        }

        return view(
            'mahasiswa.rps.index',
            compact(
                'mahasiswa',
                'krs',
            )
        );
    }

    public function show(Rps $rps)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $activeTA = $this->getActiveTA();
        $krs = $activeTA ? Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $activeTA->ta_id)
            ->where('kurikulum_id', $rps->kurikulum_id)
            ->first() : null;

        $isEnrolled = $activeTA
            && $krs
            && KrsClassResolver::normalize($rps->jenis_kelas) === KrsClassResolver::forKrs($krs, $mahasiswa);

        abort_unless($isEnrolled, 403, 'Anda tidak terdaftar pada mata kuliah RPS ini.');
        abort_unless($rps->file && Storage::disk('public')->exists($rps->file), 404, 'File RPS tidak ditemukan.');

        return response()->file(Storage::disk('public')->path($rps->file), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.($rps->nama_file ?: basename($rps->file)).'"',
        ]);
    }
}
