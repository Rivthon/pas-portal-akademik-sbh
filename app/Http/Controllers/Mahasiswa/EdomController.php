<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Models\Krs;
use App\Models\Dosen;
use App\Models\Evaluasi;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;


class EdomController extends Controller
{

      public function index()
    {
        // Ambil data mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $semester = $mahasiswa->semester;

        $activeTA = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        if (!$activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $mahasiswa = Auth::guard('mahasiswa')->user();
        $mahasiswaId = $mahasiswa->mahasiswa_id;
        $jenisKelas = $mahasiswa->kelas;
        $semester = $mahasiswa->semester;

        $krsList = Krs::with([
                'kurikulum.mataKuliah',
                'kurikulum.dosenToMatakuliah.dosen'
            ])
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereHas('kurikulum', function ($query) use ($semester) {
                $query->whereHas('mataKuliah', function ($q) use ($semester) {
                    $q->where('smt', $semester);
                });
            })
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($jenisKelas) {
                $query->where(function ($q) use ($jenisKelas) {
                    if ($jenisKelas === 'reguler') {
                        $q->where('jenis_kelas', 'reguler');
                    } elseif ($jenisKelas === 'karyawan') {
                        $q->where('jenis_kelas', 'karyawan');
                    }
                });
            })
            ->get()
            ->map(function ($krs) use ($mahasiswa) {
                return [
                    'krs_id' => $krs->krs_id,
                    'kode_matakuliah' => $krs->kurikulum->mataKuliah->matakuliah_id ?? null,
                    'nama_matakuliah' => $krs->kurikulum->mataKuliah->nama ?? 'Tidak ada data',
                    'dosen' => $krs->kurikulum->dosenToMatakuliah
                        ->where('jenis_kelas', $mahasiswa->kelas) // Filter jenis_kelas di sini
                        ->map(function ($dosenToMatakuliah) use ($mahasiswa, $krs) {
                            $isAlreadyRated = \DB::table('penilaian')
                                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                                ->where('dosen_id', $dosenToMatakuliah->dosen->dosen_id ?? null)
                                ->where('kurikulum_id', $krs->kurikulum_id)
                                ->exists();

                            return [
                                'id' => $dosenToMatakuliah->dosen->dosen_id ?? null,
                                'nama' => $dosenToMatakuliah->dosen->nama ?? 'Tidak ada data',
                                'jenis_dosen' => $dosenToMatakuliah->jenis_dosen ?? 'tidak diketahui',
                                'is_rated' => $isAlreadyRated,
                            ];
                        })->unique('id')->values(),
                ];
            });

            // ✅ Perbaikan $allFilled
            $allFilled = $krsList->every(function ($kurikulum) {
                return collect($kurikulum['dosen'])->every(fn($dosen) => $dosen['is_rated']);
            });

        // Return view dengan data yang diperlukan
        return view('students.edom.index', compact('mahasiswa', 'activeTA', 'krsList', 'allFilled'));
    }

    public function form($krs_id)
    {
        try {
            $dosen_id = request('dosen_id');  // Ambil dosen_id dari request

            // Ambil data KRS berdasarkan id dengan relasi terkait
            $krs = Krs::with(['kurikulum.mataKuliah', 'kurikulum.programStudi'])
                ->findOrFail($krs_id);

            // Ambil data dosen berdasarkan id
            $dosen = Dosen::findOrFail($dosen_id);

            // Ambil kurikulum_id dari KRS
            $kurikulum_id = $krs->kurikulum_id;

            // Periksa apakah mahasiswa sudah mengisi penilaian
            $existingPenilaian = \DB::table('penilaian')
                ->where('mahasiswa_id', auth('mahasiswa')->id())
                ->where('dosen_id', $dosen_id)
                ->where('kurikulum_id', $kurikulum_id) // Menggunakan kurikulum dari KRS
                ->exists();

            // Ambil data jenis_dosen dari tabel dosen_mata_kuliah
            $dosenData = \DB::table('dosen_mata_kuliah')
                ->where('kurikulum_id', $kurikulum_id)
                ->where('dosen_id', $dosen_id)
                ->select('dosen_id', 'jenis_dosen')
                ->first();

            if (!$dosenData) {
                return redirect()
                    ->route('mahasiswa.edom.index')
                    ->with('error', 'Jenis dosen tidak ditemukan.');
            }

            // Masukkan jenis_dosen ke dalam $dosen
            $dosen->jenis_dosen = $dosenData->jenis_dosen;

            // Cek apakah mahasiswa sudah mengisi saran
            $existingSaran = \DB::table('saran')
                ->where('mahasiswa_id', auth('mahasiswa')->id())
                ->where('dosen_id', $dosen_id)
                ->where('kurikulum_id', $kurikulum_id) // Menggunakan kurikulum dari KRS
                ->exists();

            if ($existingPenilaian || $existingSaran) {
                Alert::error('Anda sudah mengisi evaluasi untuk dosen ' . $dosen->nama . '. Data Anda tidak dapat diubah.');
                return redirect()
                    ->route('mahasiswa.edom.index');
            }

            // Ambil semua pertanyaan dari tabel evaluasi
            $evaluasis = Evaluasi::all();

            // Return view dengan data lengkap
            return view('students.edom.form', [
                'krs' => $krs,
                'dosen' => $dosen,
                'evaluasis' => $evaluasis,
                'jenis_dosen' => $dosenData->jenis_dosen,
                'title' => 'Formulir EDOM', // Judul halaman
            ]);

        } catch (ModelNotFoundException $e) {
            // Redirect kembali jika data tidak ditemukan
            return redirect()
                ->route('mahasiswa.edom.index')
                ->with('error', 'Data tidak ditemukan atau tidak valid.');
        }
    }

    public function submit(Request $request, $krs_id, $dosen_id)
    {
        // Cari data KRS berdasarkan ID
        $krs = Krs::with('kurikulum')->find($krs_id);
        if (!$krs || !Dosen::find($dosen_id)) {
            return redirect()
                ->route('mahasiswa.edom.index')
                ->with('error', 'Data tidak valid.');
        }

        // Ambil `kurikulum_id` dari `KRS`
        $kurikulum_id = $krs->kurikulum_id;

        // Cari jenis_dosen dari tabel pivot `dosen_mata_kuliah`
        $jenisDosen = \DB::table('dosen_mata_kuliah')
            ->where('kurikulum_id', $kurikulum_id)
            ->where('dosen_id', $dosen_id)
            ->value('jenis_dosen');

        if (!$jenisDosen) {
            return redirect()
                ->route('mahasiswa.edom.index')
                ->with('error', 'Jenis dosen tidak ditemukan.');
        }

        // Validasi input form
        $validatedData = $request->validate([
            'responses' => 'required|array',
            'responses.*' => 'required|in:1,2,3,4,5',
            'suggestion' => 'required|string|max:255',
        ]);

        try {
            // Simpan hasil penilaian dengan jenis_dosen
            foreach ($validatedData['responses'] as $evaluasi_id => $nilai) {
                \DB::table('penilaian')->insert([
                    'mahasiswa_id' => auth()->id(),
                    'dosen_id' => $dosen_id,
                    'krs_id' => $krs_id,  // Simpan krs_id
                    'kurikulum_id' => $kurikulum_id,
                    'evaluasi_id' => $evaluasi_id,
                    'nilai' => $nilai,
                    'jenis_dosen' => $jenisDosen, // Simpan jenis_dosen
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Simpan saran
            \DB::table('saran')->insert([
                'mahasiswa_id' => auth()->id(),
                'dosen_id' => $dosen_id,
                'krs_id' => $krs_id,  // Simpan krs_id
                'kurikulum_id' => $kurikulum_id,
                'saran' => $validatedData['suggestion'],
                'jenis_dosen' => $jenisDosen, // Simpan jenis_dosen
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Redirect ke halaman sukses
            Alert::toast('Berhasil mengisi EDOM.', 'success')
                ->position('center')
                ->autoClose(3000);

            return redirect()->route('mahasiswa.edom.index');

        } catch (\Exception $e) {
            // Jika ada error saat menyimpan
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.')
                ->withInput();
        }
    }

   public function konfirmasiEdom(Request $request)
    {
        // Ambil data mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();

        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.edom.index')->with('error', 'Mahasiswa tidak ditemukan.');
        }

        // Ambil semua `krs_id` yang diambil mahasiswa
        $krsList = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)->pluck('krs_id');

        if ($krsList->isEmpty()) {
            Alert::error('Error', 'Anda belum mengambil KRS.')->persistent('Close');
            return redirect()->route('mahasiswa.edom.index');
        }

        // Cek apakah semua mata kuliah dalam KRS sudah diisi di tabel `penilaian`
        $totalMatkul = DB::table('dosen_mata_kuliah')
            ->whereIn('kurikulum_id', function ($query) use ($krsList) {
                $query->select('kurikulum_id')->from('krs')->whereIn('id', $krsList);
            })
            ->count();

        $totalEvaluasi = DB::table('penilaian')
            ->whereIn('krs_id', $krsList)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->count();

        // Debugging log
        Log::info('Cek apakah semua EDOM sudah diisi:', [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'totalMatkul' => $totalMatkul,
            'totalEvaluasi' => $totalEvaluasi
        ]);

        if ($totalEvaluasi < $totalMatkul) {
            Alert::error('Error', 'Anda belum mengisi semua EDOM.')->persistent('Close');
            return redirect()->route('mahasiswa.edom.index');
        }

        // Update status EDOM menggunakan transaction
        DB::beginTransaction();
        try {
            $mahasiswa->update(['status_edom' => 1]);

            DB::commit();
            Alert::success('Berhasil', 'Konfirmasi EDOM berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saat mengupdate status EDOM:', ['error' => $e->getMessage()]);
            Alert::error('Gagal', 'Terjadi kesalahan saat memperbarui status.');
        }

        return redirect()->route('mahasiswa.edom.index');
    }


}