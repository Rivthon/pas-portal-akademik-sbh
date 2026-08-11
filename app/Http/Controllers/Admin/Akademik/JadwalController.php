<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Kurikulum;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use App\Models\Ruangan;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class JadwalController extends Controller
{
    public function index()
    {
        try {
            // Ambil tahun ajaran yang statusnya aktif (cached)
            $tahunAjaran = Cache::remember('active_tahun_akademik', 3600, function () {
                return TahunAkademik::where('status_ta', 1)->first();
            });

            if (! $tahunAjaran) {
                return redirect()->back()->with('error', 'Tidak ada tahun ajaran yang aktif.');
            }

            // Ambil semua data yang dibutuhkan
            $matakuliah = Matakuliah::all();
            $programStudi = ProgramStudi::all();
            $ruangan = Ruangan::all();

            if ($programStudi->isEmpty()) {
                return redirect()->back()->with('error', 'Data program studi tidak tersedia.');
            }

            // Kirim data ke view
            return view('admin.akademik.jadwal.index', compact(
                'programStudi',
                'tahunAjaran',
                'matakuliah',
                'ruangan'
            ));
        } catch (\Exception $e) {
            \Log::error('Gagal memuat halaman jadwal: '.$e->getMessage());

            return abort(500, 'Terjadi kesalahan pada server.');
        }
    }

    public function generatejadwal(Request $request)
    {
        $request->validate([
            'jurusan_id' => 'required|exists:program_studi,jurusan_id',
            'jenis_kelas' => 'required|in:Reguler,Karyawan',
        ]);

        $kurikulums = Kurikulum::where('jurusan_id', $request->jurusan_id)->get();

        if ($kurikulums->isEmpty()) {
            return redirect()->back()->with('error', 'Data Kurikulum tidak ditemukan untuk Prodi ini.');
        }

        $importedCount = 0;

        foreach ($kurikulums as $kurikulum) {
            $exists = Jadwal::where([
                'ta_id' => $kurikulum->ta_id,
                'jurusan_id' => $kurikulum->jurusan_id,
                'kurikulum_id' => $kurikulum->kurikulum_id,
                'jenis_kelas' => $request->jenis_kelas,
            ])->exists();

            if (! $exists) {
                Jadwal::create([
                    'ta_id' => $kurikulum->ta_id,
                    'jurusan_id' => $kurikulum->jurusan_id,
                    'kurikulum_id' => $kurikulum->kurikulum_id,
                    'ruangan_id' => null, // Bisa diatur jika diperlukan
                    'jam_mulai' => null,
                    'jam_selesai' => null,
                    'hari' => null, // Jadwal UTS seminggu dari hari ini
                    'jenis_kelas' => $request->jenis_kelas,
                ]);
                $importedCount++;
            }
        }

        if ($importedCount > 0) {
            activity_log('generate_jadwal', 'Admin generate '.$importedCount.' jadwal kuliah untuk prodi '.$request->jurusan_id);
            Alert::toast("$importedCount Jadwal Kuliah berhasil di-import.", 'success')
                ->position('center')
                ->autoClose(3000);
        } else {
            Alert::toast('Tidak ada data baru yang di-import.', 'warning')
                ->position('center')
                ->autoClose(3000);
        }

        return redirect()->back();
    }

    public function filter(Request $request)
    {
        try {
            $programStudi = $request->query('programStudi');
            $semester = $request->query('semester');
            $jenisKelas = $request->query('jenis_kelas');

            if (! $programStudi || ! $semester) {
                return response()->json(['message' => 'Program studi dan semester diperlukan.'], 400);
            }

            if (! $jenisKelas) {
                return response()->json(['message' => 'Jenis kelas diperlukan.', 'error' => 'Jenis kelas tidak ditemukan dalam permintaan.'], 400);
            }

            // Ambil tahun ajaran yang statusnya aktif
            // Ambil tahun ajaran yang statusnya aktif (cached)
            $tahunAjaran = Cache::remember('active_tahun_akademik', 3600, function () {
                return TahunAkademik::where('status_ta', 1)->first();
            });

            if (! $tahunAjaran) {
                return response()->json(['message' => 'Tidak ada tahun ajaran yang aktif.'], 404);
            }

            // Ambil data jadwal UTS dengan filter jurusan_id dan semester dari matakuliah
            $jadwal = Jadwal::select(
                'jadwal.id',
                'jadwal.ta_id',
                'jadwal.jurusan_id',
                'matakuliah.nama as nama_matakuliah',
                'matakuliah.smt as semester',
                'jadwal.jam_mulai',
                'jadwal.jam_selesai',
                'jadwal.hari',
                'ruangan.nama as nama_ruangan',
                'jadwal.jenis_kelas',
                'jadwal.ruangan_id'
            )
                ->join('kurikulum', 'jadwal.kurikulum_id', '=', 'kurikulum.kurikulum_id') // Menghubungkan dengan kurikulum
                ->join('matakuliah', function ($join) use ($semester) {
                    $join->on('kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                        ->where('matakuliah.smt', '=', $semester);
                })
                ->leftJoin('ruangan', 'jadwal.ruangan_id', '=', 'ruangan.ruangan_id')
                ->where('jadwal.jurusan_id', $programStudi)
                ->where('jadwal.jenis_kelas', $jenisKelas)
                ->where('jadwal.ta_id', $tahunAjaran->ta_id) // Sesuaikan dengan tahun ajaran aktif
                ->orderBy('jadwal.jam_mulai', 'asc')
                ->get();

            if ($jadwal->isEmpty()) {
                return response()->json(['message' => 'Tidak ada jadwal UTS yang ditemukan untuk program studi dan semester ini.'], 404);
            }

            return response()->json($jadwal);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan pada server.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::find($id);

        if (! $jadwal) {
            return response()->json(['success' => false, 'message' => 'Jadwal tidak ditemukan.']);
        }

        // Validasi input berdasarkan field
        if ($request->field === 'jam_mulai') {
            $request->validate(['value' => 'required']);
        } elseif ($request->field === 'jam_selesai') {
            $request->validate(['value' => 'required']);
        } elseif ($request->field === 'hari') {
            $request->validate(['value' => 'required|string']);
        } elseif ($request->field === 'ruangan_id') {
            $request->validate(['value' => 'required|exists:ruangan,ruangan_id']);
        }

        // Update field yang diedit
        $jadwal->update([$request->field => $request->value]);

        activity_log('update_jadwal', 'Admin memperbarui jadwal ID: '.$id.' ('.$request->field.')');

        return response()->json(['success' => true, 'message' => 'Jadwal berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        try {
            $jadwal = Jadwal::find($id);

            if ($jadwal) {
                activity_log('hapus_jadwal', 'Admin menghapus jadwal ID: '.$id);
                $jadwal->delete();
                Alert::toast('Data Jadwal berhasil dihapus.', 'info')
                    ->position('bottom-end')
                    ->autoClose(3000);

                return redirect()->back(); // Sesuaikan dengan kebutuhan
            } else {
                Alert::toast('Data tidak ditemukan.', 'error')
                    ->position('bottom-end')
                    ->autoClose(3000);

                return redirect()->back();
            }
        } catch (\Exception $e) {
            Alert::toast('Gagal menghapus data: '.$e->getMessage(), 'error')
                ->position('bottom-end')
                ->autoClose(3000);

            return redirect()->back();
        }
    }
}
