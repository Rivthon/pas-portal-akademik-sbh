<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Evaluasi;
use App\Models\Krs;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RealRashid\SweetAlert\Facades\Alert;

class EdomController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        if (! $mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $tahunAkademikAktif = TahunAkademik::where('status_ta', 1)
            ->first(['ta_id', 'nama', 'semester', 'status_ta']);

        if (! $tahunAkademikAktif) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $requestedTaId = $request->integer('ta_id');
        $selectedTaId = $requestedTaId ?: (int) $tahunAkademikAktif->ta_id;
        $activeTA = TahunAkademik::find($selectedTaId);

        if (! $activeTA) {
            abort(404, 'Tahun Akademik tidak ditemukan.');
        }

        // TA selain TA aktif hanya boleh diakses jika mahasiswa memiliki KRS pada TA tersebut.
        if ($requestedTaId && ! Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $selectedTaId)
            ->exists()) {
            abort(404, 'Data EDOM untuk Tahun Akademik tersebut tidak ditemukan.');
        }

        $mahasiswaId = $mahasiswa->mahasiswa_id;
        $isHistorical = (int) $activeTA->ta_id !== (int) $tahunAkademikAktif->ta_id;

        $kelasMap = [
            'pagi' => 'reguler',
            'reguler' => 'reguler',
            'reguler a' => 'reguler',
            'reguler b' => 'karyawan',
            'karyawan' => 'karyawan',
        ];
        $searchKelas = $kelasMap[strtolower(trim((string) $mahasiswa->kelas))]
            ?? strtolower(trim((string) $mahasiswa->kelas));

        // Seluruh data EDOM wajib berasal dari KRS pada TA yang sedang dipilih.
        $krsData = Krs::with([
            'kurikulum.mataKuliah',
            'kurikulum.dosenToMatakuliah.dosen',
        ])
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('ta_id', $selectedTaId)
            ->get();

        $kurikulumIds = $krsData->pluck('kurikulum_id')->unique()->values();
        $existingRatings = DB::table('penilaian')
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->select('dosen_id', 'kurikulum_id')
            ->distinct()
            ->get()
            ->groupBy('kurikulum_id')
            ->map(fn ($items) => $items->pluck('dosen_id')->map(fn ($id) => (int) $id)->all());

        $krsList = $krsData->map(function ($krs) use ($existingRatings, $searchKelas) {
            $dosenAssignments = collect($krs->kurikulum?->dosenToMatakuliah)
                ->filter(function ($dtm) use ($searchKelas) {
                    $jenisKelas = strtolower((string) ($dtm->jenis_kelas ?? ''));
                    $jenisDosen = strtolower((string) ($dtm->jenis_dosen ?? ''));

                    if ($jenisDosen === 'teori') {
                        return $jenisKelas === $searchKelas;
                    }

                    if ($jenisDosen === 'praktik') {
                        return $jenisKelas === $searchKelas || $jenisKelas === '';
                    }

                    return $jenisKelas === $searchKelas;
                });

            return [
                'krs_id' => $krs->krs_id,
                'kurikulum_id' => $krs->kurikulum_id,
                'kode_matakuliah' => $krs->kurikulum?->mataKuliah?->matakuliah_id,
                'nama_matakuliah' => $krs->kurikulum?->mataKuliah?->nama ?? 'Tidak ada data',
                'dosen' => $dosenAssignments
                    ->map(function ($dtm) use ($existingRatings, $krs) {
                        $dosenId = (int) ($dtm->dosen?->dosen_id ?? 0);
                        $ratedDosens = $existingRatings->get($krs->kurikulum_id, []);

                        return [
                            'id' => $dosenId,
                            'nama' => $dtm->dosen?->nama ?? 'Tidak ada data',
                            'jenis_dosen' => strtolower((string) ($dtm->jenis_dosen ?? '')),
                            'is_rated' => in_array($dosenId, $ratedDosens, true),
                        ];
                    })
                    ->filter(fn ($dosen) => $dosen['id'] > 0)
                    ->unique(fn ($dosen) => $dosen['id'].'_'.$dosen['jenis_dosen'])
                    ->values(),
            ];
        });

        $allFilled = $krsList->isNotEmpty() && $krsList->every(function ($item) {
            return collect($item['dosen'])->isNotEmpty()
                && collect($item['dosen'])->every(fn ($dosen) => $dosen['is_rated']);
        });

        activity_log(
            'akses_edom',
            'Mahasiswa mengakses EDOM Tahun Akademik '.$activeTA->nama.' '.$activeTA->semester
        );

        return view('mahasiswa.edom.index', compact(
            'mahasiswa',
            'activeTA',
            'tahunAkademikAktif',
            'selectedTaId',
            'isHistorical',
            'krsList',
            'allFilled'
        ));
    }

    public function form(Request $request, $krs_id)
    {
        try {
            $dosen_id = request('dosen_id');  // Ambil dosen_id dari request

            // Ambil data KRS berdasarkan id dengan relasi terkait
            $krs = Krs::with(['kurikulum.mataKuliah', 'kurikulum.programStudi', 'tahunAjaran'])
                ->where('mahasiswa_id', auth('mahasiswa')->id())
                ->findOrFail($krs_id);

            abort_if(
                $request->filled('ta_id') && (int) $request->integer('ta_id') !== (int) $krs->ta_id,
                404,
                'Data EDOM tidak sesuai dengan Tahun Akademik yang dipilih.'
            );

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
                    ->route('mahasiswa.edom.index', array_filter(['ta_id' => $request->integer('ta_id')]))
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
                    ->route('mahasiswa.edom.index', array_filter(['ta_id' => $request->integer('ta_id')]));
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
                ->route('mahasiswa.edom.index', array_filter(['ta_id' => $request->integer('ta_id')]))
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
                ->route('mahasiswa.edom.index', array_filter(['ta_id' => $request->integer('ta_id')]))
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

            return redirect()->route('mahasiswa.edom.index', array_filter(['ta_id' => $request->integer('ta_id')]));
        }

        // Cari jenis_dosen dari tabel pivot `dosen_mata_kuliah`
        $jenisDosen = DB::table('dosen_mata_kuliah')
            ->where('kurikulum_id', $kurikulum_id)
            ->where('dosen_id', $dosen_id)
            ->value('jenis_dosen');

        if (! $jenisDosen) {
            return redirect()
                ->route('mahasiswa.edom.index', array_filter(['ta_id' => $request->integer('ta_id')]))
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

            return redirect()->route('mahasiswa.edom.index', array_filter(['ta_id' => $request->integer('ta_id')]));
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
        $mahasiswa = Auth::guard('mahasiswa')->user();

        if (! $mahasiswa) {
            return redirect()->route('mahasiswa.edom.index')->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $tahunAkademikAktif = TahunAkademik::where('status_ta', 1)->first();
        $selectedTaId = $request->integer('ta_id') ?: (int) ($tahunAkademikAktif?->ta_id ?? 0);
        $selectedTA = TahunAkademik::find($selectedTaId);
        $redirectParameters = array_filter(['ta_id' => $selectedTaId]);

        if (! $selectedTA) {
            return redirect()->route('mahasiswa.edom.index')
                ->with('error', 'Tahun Akademik EDOM tidak ditemukan.');
        }

        $krsList = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $selectedTaId)
            ->pluck('krs_id');

        if ($krsList->isEmpty()) {
            Alert::error('Error', 'KRS pada Tahun Akademik yang dipilih tidak ditemukan.')->persistent('Close');

            return redirect()->route('mahasiswa.edom.index', $redirectParameters);
        }

        $kurikulumIds = Krs::whereIn('krs_id', $krsList)->pluck('kurikulum_id');
        $kelasMahasiswa = strtolower(trim((string) $mahasiswa->kelas));
        $searchKelas = match ($kelasMahasiswa) {
            'karyawan', 'reguler b' => 'karyawan',
            default => 'reguler',
        };

        $requiredPairs = DB::table('dosen_mata_kuliah')
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->where(function ($query) use ($searchKelas) {
                $query->whereRaw('LOWER(COALESCE(jenis_kelas, "")) = ?', [$searchKelas]);
                if ($searchKelas === 'reguler') {
                    $query->orWhere(function ($praktik) {
                        $praktik->whereRaw('LOWER(COALESCE(jenis_dosen, "")) = ?', ['praktik'])
                            ->where(function ($kelas) {
                                $kelas->whereNull('jenis_kelas')->orWhere('jenis_kelas', '');
                            });
                    });
                }
            })
            ->select('dosen_id', 'kurikulum_id')
            ->distinct()
            ->get()
            ->map(fn ($row) => $row->dosen_id.'-'.$row->kurikulum_id);

        $filledPairs = DB::table('penilaian')
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->select('dosen_id', 'kurikulum_id')
            ->distinct()
            ->get()
            ->map(fn ($row) => $row->dosen_id.'-'.$row->kurikulum_id);

        $belumTerisi = $requiredPairs->diff($filledPairs)->count();
        if ($requiredPairs->isEmpty() || $belumTerisi > 0) {
            Alert::error(
                'Error',
                $requiredPairs->isEmpty()
                    ? 'Tidak ada data dosen untuk KRS pada Tahun Akademik ini.'
                    : 'Anda belum mengisi semua EDOM. Masih ada '.$belumTerisi.' dosen yang belum dinilai.'
            )->persistent('Close');

            return redirect()->route('mahasiswa.edom.index', $redirectParameters);
        }

        // status_edom adalah status semester aktif; EDOM historis cukup dibuktikan
        // oleh data penilaian per kurikulum agar tidak mengubah status TA berjalan.
        if ((int) $selectedTA->status_ta === 1) {
            $mahasiswa->update(['status_edom' => 1]);
        }

        activity_log(
            'konfirmasi_edom',
            'Mahasiswa mengkonfirmasi EDOM Tahun Akademik '.$selectedTA->nama.' '.$selectedTA->semester
        );
        Alert::success('Berhasil', 'Konfirmasi EDOM berhasil!');

        return redirect()->route('mahasiswa.edom.index', $redirectParameters);
    }
}
