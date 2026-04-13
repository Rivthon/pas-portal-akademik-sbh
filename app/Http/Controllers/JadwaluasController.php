<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use App\Models\Jadwaluas;
use App\Models\Kurikulum;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;

class JadwaluasController extends Controller
{
     public function index()

        {
            try {
                $tahunAjaran = Cache::remember('active_tahun_akademik', 3600, function () {
                    return TahunAkademik::where('status_ta', 1)->first();
                });
                $matakuliah = Matakuliah::all();
                $ruangan = Ruangan::all();
                if (!$tahunAjaran) {
                    return redirect()->back()->with('error', 'Tidak ada tahun ajaran yang aktif.');
                }

                // Ambil semua program studi
                $programStudi = ProgramStudi::all();

                if ($programStudi->isEmpty()) {
                    return redirect()->back()->with('error', 'Data program studi tidak tersedia.');
                }

                return view('jadwal-uas.index', compact('programStudi', 'tahunAjaran','matakuliah','ruangan'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Terjadi kesalahan pada server.');
            }
        }
     public function filter(Request $request)
        {
                try {
                $programStudi = $request->query('programStudi');
                $semester = $request->query('semester');
                $jenisKelas = $request->query('jenis_kelas');

                if (!$programStudi || !$semester) {
                    return response()->json(['message' => 'Program studi dan semester diperlukan.'], 400);
                }

                if (!$jenisKelas) {
                    return response()->json(['message' => 'Jenis kelas diperlukan.', 'error' => 'Jenis kelas tidak ditemukan dalam permintaan.'], 400);
                }

                // Ambil tahun ajaran yang statusnya aktif
                $tahunAjaran = Cache::remember('active_tahun_akademik', 3600, function () {
                    return TahunAkademik::where('status_ta', 1)->first();
                });

                if (!$tahunAjaran) {
                    return response()->json(['message' => 'Tidak ada tahun ajaran yang aktif.'], 404);
                }
                // Ambil data jadwal UTS dengan filter jurusan_id dan semester dari matakuliah
                $jadwal = Jadwaluas::select(
                    'jadwal_uas.id',
                        'jadwal_uas.ta_id',
                        'jadwal_uas.jurusan_id',
                        'matakuliah.nama as nama_matakuliah',
                        'matakuliah.smt as semester',
                        'jadwal_uas.jam_mulai',
                        'jadwal_uas.jam_selesai',
                        'jadwal_uas.tanggal',
                        'ruangan.nama as nama_ruangan',
                        'jadwal_uas.jenis_kelas',
                        'jadwal_uas.ruangan_id',
                        'jadwal_uas.jenis_kelas'
                    )
                    ->join('matakuliah', function ($join) use ($semester) {
                        $join->on('jadwal_uas.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                            ->where('matakuliah.smt', '=', $semester);
                    })
                    ->leftJoin('ruangan', 'jadwal_uas.ruangan_id', '=', 'ruangan.ruangan_id')
                    ->where('jadwal_uas.jurusan_id', $programStudi)
                    ->where('jadwal_uas.jenis_kelas', $jenisKelas)
                    ->where('jadwal_uas.ta_id', $tahunAjaran->ta_id) // Sesuaikan dengan tahun ajaran aktif
                    ->orderBy('jadwal_uas.tanggal', 'asc')
                    ->orderBy('jadwal_uas.jam_mulai', 'asc')
                    ->get();

                if ($jadwal->isEmpty()) {
                    return response()->json(['message' => 'Tidak ada jadwal UTS yang ditemukan untuk program studi dan semester ini.'], 404);
                }

                return response()->json($jadwal);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Terjadi kesalahan pada server.', 'error' => $e->getMessage()], 500);
            }
        }
        public function generateJadwalUAS(Request $request)
            {
                $request->validate([
                    'jurusan_id'  => 'required|exists:program_studi,jurusan_id',
                    'jenis_kelas' => 'required|in:Reguler,Karyawan',
                ]);

                $kurikulums = Kurikulum::where('jurusan_id', $request->jurusan_id)->get();

                if ($kurikulums->isEmpty()) {
                    return redirect()->back()->with('error', 'Data Kurikulum tidak ditemukan untuk Prodi ini.');
                }

                $importedCount = 0;

                foreach ($kurikulums as $kurikulum) {
                    $exists = Jadwaluas::where([
                        'ta_id'         => $kurikulum->ta_id,
                        'jurusan_id'    => $kurikulum->jurusan_id,
                        'matakuliah_id' => $kurikulum->matakuliah_id,
                        'jenis_kelas'   => $request->jenis_kelas,
                    ])->exists();

                    if (!$exists) {
                        Jadwaluas::create([
                            'ta_id'         => $kurikulum->ta_id,
                            'jurusan_id'    => $kurikulum->jurusan_id,
                            'matakuliah_id' => $kurikulum->matakuliah_id,
                            'ruangan_id'    => null, // Bisa diatur jika diperlukan
                            'jam_mulai'           => null,
                            'jam_selesai'         => null,
                            'tanggal'       => now()->addDays(7), // Jadwal UTS seminggu dari hari ini
                            'jenis_kelas'   => $request->jenis_kelas,
                        ]);
                        $importedCount++;
                    }
                }

                if ($importedCount > 0) {
                    Alert::toast("$importedCount Jadwal UTS berhasil di-import.", 'success')
                        ->position('center')
                        ->autoClose(3000);
                } else {
                    Alert::toast("Tidak ada data baru yang di-import.", 'warning')
                        ->position('center')
                        ->autoClose(3000);
                }

                return redirect()->back();
            }


          public function update(Request $request, $id)
            {
                $jadwal = Jadwaluas::find($id);

                if (!$jadwal) {
                    return response()->json(['success' => false, 'message' => 'Jadwal tidak ditemukan.']);
                }

                // Validasi input berdasarkan field
                if ($request->field === 'jam_mulai') {
                    $request->validate(['value' => 'required|string']);
                } elseif ($request->field === 'jam_selesai') {
                    $request->validate(['value' => 'required|string']);
                } elseif ($request->field === 'tanggal') {
                    $request->validate(['value' => 'required|date']);
                } elseif ($request->field === 'ruangan_id') {
                    $request->validate(['value' => 'required|exists:ruangan,ruangan_id']);
                }

                // Update field yang diedit
                $jadwal->update([$request->field => $request->value]);

                return response()->json(['success' => true, 'message' => 'Jadwal berhasil diperbarui.']);
            }
        public function destroy($id)
        {
            $jadwal = Jadwaluas::find($id);

            if (!$jadwal) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $jadwal->delete();

            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        }
}