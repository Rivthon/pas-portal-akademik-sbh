<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UjianController extends Controller
{
    public function riwayatNilaiUjian(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        abort_unless($mahasiswa, 401);

        $activeTaId = TahunAkademik::where('status_ta', 1)->value('ta_id');
        $tahunAjaranOptions = TahunAkademik::query()
            ->when($activeTaId, fn ($query) => $query->where('ta_id', '!=', $activeTaId))
            ->whereExists(function ($query) use ($mahasiswa) {
                $query->selectRaw('1')
                    ->from('krs')
                    ->whereColumn('krs.ta_id', 'tahun_ajaran.ta_id')
                    ->where('krs.mahasiswa_id', $mahasiswa->mahasiswa_id)
                    ->where(function ($nilai) {
                        $nilai->where(function ($uts) {
                            $uts->whereNotNull('krs.uts')->whereRaw("TRIM(krs.uts) != ''");
                        })->orWhere(function ($uas) {
                            $uas->whereNotNull('krs.uas')->whereRaw("TRIM(krs.uas) != ''");
                        });
                    });
            })
            ->orderByDesc('ta_id')
            ->get(['ta_id', 'nama', 'semester']);

        $requestedTaId = $request->integer('ta_id');
        $selectedTaId = $tahunAjaranOptions->contains('ta_id', $requestedTaId)
            ? $requestedTaId
            : $tahunAjaranOptions->first()?->ta_id;
        $selectedTahunAjaran = $tahunAjaranOptions->firstWhere('ta_id', $selectedTaId);

        $nilai = collect();
        if ($selectedTaId) {
            $nilai = Krs::with('kurikulum.mataKuliah')
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $selectedTaId)
                ->whereHas('kurikulum.mataKuliah')
                ->where(function ($query) {
                    $query->where(function ($uts) {
                        $uts->whereNotNull('uts')->whereRaw("TRIM(uts) != ''");
                    })->orWhere(function ($uas) {
                        $uas->whereNotNull('uas')->whereRaw("TRIM(uas) != ''");
                    });
                })
                ->get()
                ->unique('kurikulum_id')
                ->sortBy(fn (Krs $item) => $item->kurikulum?->mataKuliah?->nama)
                ->values();
        }

        activity_log('lihat_riwayat_nilai_ujian', 'Mahasiswa melihat riwayat nilai UTS dan UAS');

        return view('mahasiswa.nilai-ujian-riwayat.index', compact(
            'mahasiswa',
            'tahunAjaranOptions',
            'selectedTaId',
            'selectedTahunAjaran',
            'nilai'
        ));
    }

    public function tampilkanNilaiUts()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (! $mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $mahasiswaId = $mahasiswa->mahasiswa_id;

        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama']); // Ambil ID dan Nama Tahun Akademik Aktif
        $taId = $ta->ta_id;
        try {

            $nilai = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get();

            activity_log('lihat_nilai', 'Mahasiswa melihat nilai UTS');

            return view('mahasiswa.nilai-uts.index', compact('nilai', 'ta', 'mahasiswa'));
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal memuat data KRS: '.$e->getMessage());
        }
    }

    public function tampilkanNilaiUas()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (! $mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $mahasiswaId = $mahasiswa->mahasiswa_id;

        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama']); // Ambil ID dan Nama Tahun Akademik Aktif
        $taId = $ta->ta_id;
        try {

            $nilai = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get();

            activity_log('lihat_nilai', 'Mahasiswa melihat nilai UAS');

            return view('mahasiswa.nilai-uas.index', compact('nilai', 'ta', 'mahasiswa'));
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal memuat data KRS: '.$e->getMessage());
        }
    }

    public function tampilkanNilaiAkhir()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (! $mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $mahasiswaId = $mahasiswa->mahasiswa_id;

        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama']); // Ambil ID dan Nama Tahun Akademik Aktif
        $taId = $ta->ta_id;
        try {

            $nilai = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get();

            activity_log('lihat_nilai', 'Mahasiswa melihat nilai Akhir');

            return view('mahasiswa.nilai-akhir.index', compact('nilai', 'ta', 'mahasiswa'));
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal memuat data KRS: '.$e->getMessage());
        }
    }
}
