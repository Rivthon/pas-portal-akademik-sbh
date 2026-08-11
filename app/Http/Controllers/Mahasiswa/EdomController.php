<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Evaluasi;
use App\Models\Krs;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RealRashid\SweetAlert\Facades\Alert;

class EdomController extends Controller
{
    public function index()
    {
        // 1️⃣ Ambil mahasiswa login
        $mahasiswa = Auth::guard('mahasiswa')->user();

        if (! $mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        // 2️⃣ Ambil Tahun Akademik aktif
        $activeTA = TahunAkademik::where('status_ta', 1)
            ->first(['ta_id', 'nama', 'semester']);

        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $mahasiswaId = $mahasiswa->mahasiswa_id;
        $semester = $mahasiswa->semester;

        // 3️⃣ Mapping kelas mahasiswa
        $kelasMap = [
            'pagi' => 'reguler',
            'reguler' => 'reguler',
            'karyawan' => 'karyawan',
        ];

        $searchKelas = $kelasMap[strtolower($mahasiswa->kelas)] ?? strtolower($mahasiswa->kelas);

        // 4️⃣ Ambil data KRS + relasi
        $krsData = Krs::with([
            'kurikulum.mataKuliah',
            'kurikulum.dosenToMatakuliah.dosen',
        ])
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereHas('kurikulum.mataKuliah', function ($q) use ($semester) {
                $q->where('smt', $semester);
            })
            ->get();

        // 5️⃣ Ambil semua penilaian mahasiswa untuk semester ini dalam 1 query (eliminasi N+1)
        $kurikulumIds = $krsData->pluck('kurikulum_id')->unique()->values();

        $existingRatings = DB::table('penilaian')
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->select('dosen_id', 'kurikulum_id')
            ->distinct()
            ->get()
            ->groupBy('kurikulum_id')
            ->map(function ($items) {
                return $items->pluck('dosen_id')->toArray();
            });

        // 6️⃣ Map KRS data dengan cek rating dari memory (bukan query per dosen)
        $krsList = $krsData->map(function ($krs) use ($existingRatings, $searchKelas) {

            return [
                'krs_id' => $krs->krs_id,
                'kurikulum_id' => $krs->kurikulum_id,
                'kode_matakuliah' => $krs->kurikulum->mataKuliah->matakuliah_id ?? null,
                'nama_matakuliah' => $krs->kurikulum->mataKuliah->nama ?? 'Tidak ada data',

                'dosen' => $krs->kurikulum->dosenToMatakuliah
                    ->filter(function ($dtm) use ($searchKelas) {
                        $jenisKelas = strtolower($dtm->jenis_kelas ?? '');
                        $jenisDosen = strtolower($dtm->jenis_dosen ?? '');

                        // Untuk dosen teori, filter berdasarkan jenis_kelas mahasiswa
                        if ($jenisDosen === 'teori') {
                            return $jenisKelas === $searchKelas;
                        }

                        // Untuk dosen praktik, terima jika jenis_kelas cocok ATAU jika jenis_kelas kosong
                        if ($jenisDosen === 'praktik') {
                            return $jenisKelas === $searchKelas || $jenisKelas === '';
                        }

                        // Default: filter berdasarkan jenis_kelas
                        return $jenisKelas === $searchKelas;
                    })
                    ->map(function ($dtm) use ($existingRatings, $krs) {
                        // Cek rating dari memory, bukan query ke DB
                        $ratedDosens = $existingRatings->get($krs->kurikulum_id, []);
                        $isAlreadyRated = in_array($dtm->dosen->dosen_id ?? null, $ratedDosens);

                        return [
                            'id' => $dtm->dosen->dosen_id ?? null,
                            'nama' => $dtm->dosen->nama ?? 'Tidak ada data',
                            'jenis_dosen' => strtolower($dtm->jenis_dosen ?? ''),
                            'is_rated' => $isAlreadyRated,
                        ];
                    })
                    // Unique berdasarkan kombinasi id + jenis_dosen agar dosen yang sama
                    // bisa muncul sebagai teori DAN praktik
                    ->unique(function ($item) {
                        return $item['id'].'_'.$item['jenis_dosen'];
                    })
                    ->values(),
            ];
        });

        // 7️⃣ Cek apakah semua dosen sudah dinilai
        // Pastikan krsList tidak kosong DAN setiap dosen memiliki dosen yang sudah dinilai
        $allFilled = $krsList->isNotEmpty() && $krsList->every(function ($item) {
            // Pastikan ada dosen yang terkait sebelum mengecek status penilaian
            return collect($item['dosen'])->isNotEmpty() &&
                collect($item['dosen'])->every(fn ($dosen) => $dosen['is_rated']);
        });

        // 8️⃣ Return ke view
        activity_log('akses_edom', 'Mahasiswa mengakses halaman EDOM');

        return view('mahasiswa.edom.index', compact(
            'mahasiswa',
            'activeTA',
            'krsList',
            'allFilled'
        ));
    }

    public function form($krs_id)
    {
        try {
            $dosen_id = request('dosen_id');  // Ambil dosen_id dari request

            // Ambil data KRS berdasarkan id dengan relasi terkait
            $krs = Krs::with(['kurikulum.mataKuliah', 'kurikulum.programStudi'])
                ->where('mahasiswa_id', auth('mahasiswa')->id())
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

            if (! $dosenData) {
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
                Alert::error('Anda sudah mengisi evaluasi untuk dosen '.$dosen->nama.'. Data Anda tidak dapat diubah.');

                return redirect()
                    ->route('mahasiswa.edom.index');
            }

            // Ambil semua pertanyaan dari tabel evaluasi
            $evaluasis = Evaluasi::all();

            // Return view dengan data lengkap
            return view('mahasiswa.edom.form', [
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
        $mahasiswaId = auth('mahasiswa')->id();

        // Cari data KRS berdasarkan ID
        $krs = Krs::with('kurikulum')
            ->where('mahasiswa_id', $mahasiswaId)
            ->find($krs_id);
        if (! $krs || ! Dosen::find($dosen_id)) {
            return redirect()
                ->route('mahasiswa.edom.index')
                ->with('error', 'Data tidak valid.');
        }

        // Ambil `kurikulum_id` dari `KRS`
        $kurikulum_id = $krs->kurikulum_id;

        // 🛡️ Proteksi duplikasi: cek apakah sudah pernah submit untuk kombinasi ini
        $alreadySubmitted = DB::table('penilaian')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('dosen_id', $dosen_id)
            ->where('kurikulum_id', $kurikulum_id)
            ->exists();

        if ($alreadySubmitted) {
            Alert::error('Error', 'Anda sudah mengisi evaluasi untuk dosen ini. Data tidak dapat diubah.');

            return redirect()->route('mahasiswa.edom.index');
        }

        // Cari jenis_dosen dari tabel pivot `dosen_mata_kuliah`
        $jenisDosen = DB::table('dosen_mata_kuliah')
            ->where('kurikulum_id', $kurikulum_id)
            ->where('dosen_id', $dosen_id)
            ->value('jenis_dosen');

        if (! $jenisDosen) {
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

        $expectedEvaluationIds = Evaluasi::query()
            ->pluck('eval_id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values();
        $submittedEvaluationIds = collect(array_keys($validatedData['responses']))
            ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values();

        if ($expectedEvaluationIds->isEmpty() || $submittedEvaluationIds->all() !== $expectedEvaluationIds->all()) {
            throw ValidationException::withMessages([
                'responses' => 'Seluruh pertanyaan EDOM yang valid wajib dijawab.',
            ]);
        }

        // 🔒 Gunakan DB Transaction untuk konsistensi data
        DB::beginTransaction();
        try {
            // Batch insert semua penilaian sekaligus (1 query, bukan N query)
            $penilaianData = [];
            $now = now();

            foreach ($validatedData['responses'] as $evaluasi_id => $nilai) {
                $penilaianData[] = [
                    'mahasiswa_id' => $mahasiswaId,
                    'dosen_id' => $dosen_id,
                    'krs_id' => $krs_id,
                    'kurikulum_id' => $kurikulum_id,
                    'evaluasi_id' => $evaluasi_id,
                    'nilai' => $nilai,
                    'jenis_dosen' => $jenisDosen,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('penilaian')->insert($penilaianData);

            // Simpan saran
            DB::table('saran')->insert([
                'mahasiswa_id' => $mahasiswaId,
                'dosen_id' => $dosen_id,
                'krs_id' => $krs_id,
                'kurikulum_id' => $kurikulum_id,
                'saran' => $validatedData['suggestion'],
                'jenis_dosen' => $jenisDosen,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::commit();

            // Redirect ke halaman sukses
            activity_log('submit_edom', 'Mahasiswa mengisi EDOM untuk dosen ID: '.$dosen_id);
            Alert::toast('Berhasil mengisi EDOM.', 'success')
                ->position('center')
                ->autoClose(3000);

            return redirect()->route('mahasiswa.edom.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saat menyimpan EDOM:', [
                'mahasiswa_id' => $mahasiswaId,
                'dosen_id' => $dosen_id,
                'krs_id' => $krs_id,
                'error' => $e->getMessage(),
            ]);

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

        if (! $mahasiswa) {
            return redirect()->route('mahasiswa.edom.index')->with('error', 'Mahasiswa tidak ditemukan.');
        }

        // Ambil Tahun Akademik aktif
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTA) {
            Alert::error('Error', 'Tidak ada Tahun Akademik aktif.')->persistent('Close');

            return redirect()->route('mahasiswa.edom.index');
        }

        // Ambil semua `krs_id` yang diambil mahasiswa untuk semester aktif
        $krsList = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereHas('kurikulum.mataKuliah', function ($q) use ($mahasiswa) {
                $q->where('smt', $mahasiswa->semester);
            })
            ->pluck('krs_id');

        if ($krsList->isEmpty()) {
            Alert::error('Error', 'Anda belum mengambil KRS untuk semester ini.')->persistent('Close');

            return redirect()->route('mahasiswa.edom.index');
        }

        // Ambil kurikulum_id dari KRS yang sudah diambil
        $kurikulumIds = Krs::whereIn('krs_id', $krsList)->pluck('kurikulum_id');

        // Cek apakah semua dosen di mata kuliah KRS sudah diisi di tabel `penilaian`
        $totalMatkul = DB::table('dosen_mata_kuliah')
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->count();

        // Hitung jumlah penilaian unik (per dosen per kurikulum) yang sudah diisi
        $totalEvaluasi = DB::table('penilaian')
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->distinct()
            ->count(DB::raw('CONCAT(dosen_id, "-", kurikulum_id)'));

        // Debugging log
        Log::info('Cek apakah semua EDOM sudah diisi:', [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'totalMatkul' => $totalMatkul,
            'totalEvaluasi' => $totalEvaluasi,
        ]);

        if ($totalMatkul == 0) {
            Alert::error('Error', 'Tidak ada data mata kuliah ditemukan untuk KRS Anda.')->persistent('Close');

            return redirect()->route('mahasiswa.edom.index');
        }

        if ($totalEvaluasi < $totalMatkul) {
            Alert::error('Error', 'Anda belum mengisi semua EDOM. ('.$totalEvaluasi.'/'.$totalMatkul.')')->persistent('Close');

            return redirect()->route('mahasiswa.edom.index');
        }

        // Update status EDOM menggunakan transaction
        DB::beginTransaction();
        try {
            $mahasiswa->update(['status_edom' => 1]);

            DB::commit();
            activity_log('konfirmasi_edom', 'Mahasiswa mengkonfirmasi pengisian seluruh EDOM');
            Alert::success('Berhasil', 'Konfirmasi EDOM berhasil!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saat mengupdate status EDOM:', ['error' => $e->getMessage()]);
            Alert::error('Gagal', 'Terjadi kesalahan saat memperbarui status.');
        }

        return redirect()->route('mahasiswa.edom.index');
    }
}
