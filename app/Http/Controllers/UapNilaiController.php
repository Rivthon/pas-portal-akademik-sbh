<?php

namespace App\Http\Controllers;

use App\Models\UapNilai;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // ✅ yang benar
use App\Http\Requests\StoreUapNilaiRequest;
use App\Http\Requests\UpdateUapNilaiRequest;

class UapNilaiController extends Controller
{
    public function index()
    {
        // Menampilkan form filter (tahun ajaran, semester)
        $tahunAjaran = TahunAkademik::all();
        return view('nilai-uap.index', compact('tahunAjaran'));
    }

    public function getMahasiswa(Request $request)
{
    $request->validate([
        'tahun_ajaran_id' => 'required|exists:tahun_ajaran,ta_id',
        'semester' => 'required|numeric',
    ]);

    // Ambil mahasiswa sesuai filter
    $mahasiswaList = \App\Models\Mahasiswa::where('jurusan_id', 15401)
        ->where('semester', $request->semester)
        ->get();

    // Ambil semua nilai uap sekaligus
    $nilaiUap = \App\Models\UapNilai::where('tahun_ajaran_id', $request->tahun_ajaran_id)
        ->pluck('uap_tulis', 'mahasiswa_id')->toArray();

    $nilaiUapPraktik = \App\Models\UapNilai::where('tahun_ajaran_id', $request->tahun_ajaran_id)
        ->pluck('uap_praktik', 'mahasiswa_id')->toArray();

    // Gabungkan ke array
    $data = $mahasiswaList
        ->where('status_mhs', 'aktif')
        ->sortBy('nim')
        ->values()
        ->map(function ($mhs) use ($nilaiUap, $nilaiUapPraktik) {
            return [
                'mahasiswa_id' => $mhs->mahasiswa_id,
                'nim' => $mhs->nim,
                'nama' => $mhs->nama,
                'uap_tulis' => $nilaiUap[$mhs->mahasiswa_id] ?? null,
                'uap_praktik' => $nilaiUapPraktik[$mhs->mahasiswa_id] ?? null,
            ];
        });

    return response()->json(['data' => $data]);
}


    public function simpanNilai(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,mahasiswa_id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,ta_id',
            'uap_tulis' => 'nullable|numeric|min:0|max:100',
            'uap_praktik' => 'nullable|numeric|min:0|max:100',
        ]);

        UapNilai::updateOrCreate(
            [
                'mahasiswa_id' => $request->mahasiswa_id,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
            ],
            [
                'program_studi_id' => 15401,
                'uap_tulis' => $request->uap_tulis,
                'uap_praktik' => $request->uap_praktik,
                'tanggal_input' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }
}
