<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Evaluasi;
use App\Models\KhsPublication;
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

        $searchKelas = $this->normalizeJenisKelas($mahasiswa->kelas);

        // Seluruh data EDOM wajib berasal dari KRS pada TA yang sedang dipilih.
        $krsData = Krs::with([
            'kurikulum.mataKuliah',
            'kurikulum.dosenToMatakuliah.dosen',
        ])
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('ta_id', $selectedTaId)
            ->get();

        // Pada periode lama, hanya KRS yang sudah dicakup penerbitan KHS BAAK
        // yang boleh ditampilkan dan diisi EDOM-nya.
        if ($isHistorical) {
            $krsData = KhsPublication::filterPublishedKrs($krsData, $mahasiswa);
        }

        $kurikulumIds = $krsData->pluck('kurikulum_id')->unique()->values();
        $existingRatings = DB::table('penilaian')
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->select('dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas')
            ->distinct()
            ->get()
            ->mapWithKeys(fn ($row) => [
                $this->edomKey($row->dosen_id, $row->kurikulum_id, $row->jenis_dosen, $row->jenis_kelas) => true,
            ]);

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
                        $jenisDosen = strtolower(trim((string) ($dtm->jenis_dosen ?? '')));
                        $jenisKelas = $this->normalizeJenisKelas($dtm->jenis_kelas);

                        return [
                            'id' => $dosenId,
                            'nama' => $dtm->dosen?->nama ?? 'Tidak ada data',
                            'jenis_dosen' => $jenisDosen,
                            'jenis_kelas' => $jenisKelas,
                            'is_rated' => $existingRatings->has(
                                $this->edomKey($dosenId, $krs->kurikulum_id, $jenisDosen, $jenisKelas)
                            ),
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
            $dosen_id = $request->integer('dosen_id');
            $jenis_dosen = strtolower(trim((string) $request->input('jenis_dosen')));

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

            $jenis_kelas = $this->normalizeJenisKelas($krs->mahasiswa?->kelas ?? auth('mahasiswa')->user()?->kelas);

            // Pastikan dosen memang ditugaskan pada metode dan kelas mahasiswa ini.
            $dosenData = DB::table('dosen_mata_kuliah')
                ->where('kurikulum_id', $kurikulum_id)
                ->where('dosen_id', $dosen_id)
                ->whereRaw('LOWER(jenis_dosen) = ?', [$jenis_dosen])
                ->where(function ($query) use ($jenis_kelas, $jenis_dosen) {
                    $query->whereRaw('LOWER(COALESCE(jenis_kelas, "")) = ?', [$jenis_kelas]);
                    if ($jenis_kelas === 'reguler' && $jenis_dosen === 'praktik') {
                        $query->orWhereNull('jenis_kelas')->orWhere('jenis_kelas', '');
                    }
                })
                ->select('dosen_id', 'jenis_dosen', 'jenis_kelas')
                ->first();

            if (! $dosenData) {
                return redirect()
                    ->route('mahasiswa.edom.index', array_filter(['ta_id' => $request->integer('ta_id')]))
                    ->with('error', 'Jenis dosen tidak ditemukan.');
            }

            // Masukkan jenis_dosen ke dalam $dosen
            $dosen->jenis_dosen = $dosenData->jenis_dosen;
            $jenis_kelas = $this->normalizeJenisKelas($dosenData->jenis_kelas) ?? $jenis_kelas;

            $existingPenilaian = DB::table('penilaian')
                ->where('mahasiswa_id', auth('mahasiswa')->id())
                ->where('dosen_id', $dosen_id)
                ->where('kurikulum_id', $kurikulum_id)
                ->where('jenis_dosen', $jenis_dosen)
                ->where('jenis_kelas', $jenis_kelas)
                ->exists();

            // Cek apakah mahasiswa sudah mengisi saran
            $existingSaran = DB::table('saran')
                ->where('mahasiswa_id', auth('mahasiswa')->id())
                ->where('dosen_id', $dosen_id)
                ->where('kurikulum_id', $kurikulum_id)
                ->where('jenis_dosen', $jenis_dosen)
                ->where('jenis_kelas', $jenis_kelas)
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
                'jenis_kelas' => $jenis_kelas,
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
        $jenisDosen = strtolower(trim((string) $request->input('jenis_dosen')));

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
        $jenisKelas = $this->normalizeJenisKelas(auth('mahasiswa')->user()?->kelas);

        $assignment = DB::table('dosen_mata_kuliah')
            ->where('kurikulum_id', $kurikulum_id)
            ->where('dosen_id', $dosen_id)
            ->whereRaw('LOWER(jenis_dosen) = ?', [$jenisDosen])
            ->where(function ($query) use ($jenisKelas, $jenisDosen) {
                $query->whereRaw('LOWER(COALESCE(jenis_kelas, "")) = ?', [$jenisKelas]);
                if ($jenisKelas === 'reguler' && $jenisDosen === 'praktik') {
                    $query->orWhereNull('jenis_kelas')->orWhere('jenis_kelas', '');
                }
            })
            ->first(['jenis_dosen', 'jenis_kelas']);

        if (! $assignment) {
            return redirect()
                ->route('mahasiswa.edom.index', array_filter(['ta_id' => $request->integer('ta_id')]))
                ->with('error', 'Penugasan dosen tidak sesuai dengan kelas mahasiswa.');
        }

        $jenisDosen = strtolower((string) $assignment->jenis_dosen);
        $jenisKelas = $this->normalizeJenisKelas($assignment->jenis_kelas) ?? $jenisKelas;

        // 🛡️ Proteksi duplikasi: cek apakah sudah pernah submit untuk kombinasi ini
        $alreadySubmitted = DB::table('penilaian')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('dosen_id', $dosen_id)
            ->where('kurikulum_id', $kurikulum_id)
            ->where('jenis_dosen', $jenisDosen)
            ->where('jenis_kelas', $jenisKelas)
            ->exists();

        if ($alreadySubmitted) {
            Alert::error('Error', 'Anda sudah mengisi evaluasi untuk dosen ini. Data tidak dapat diubah.');

            return redirect()->route('mahasiswa.edom.index', array_filter(['ta_id' => $request->integer('ta_id')]));
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
                    'jenis_kelas' => $jenisKelas,
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
                'jenis_kelas' => $jenisKelas,
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

        $krsRecords = Krs::with('kurikulum.mataKuliah')
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $selectedTaId)
            ->get();

        if ((int) $selectedTA->status_ta !== 1) {
            $krsRecords = KhsPublication::filterPublishedKrs($krsRecords, $mahasiswa);
        }

        $krsList = $krsRecords->pluck('krs_id');

        if ($krsList->isEmpty()) {
            Alert::error('Error', 'KRS pada Tahun Akademik yang dipilih tidak ditemukan.')->persistent('Close');

            return redirect()->route('mahasiswa.edom.index', $redirectParameters);
        }

        $kurikulumIds = $krsRecords->pluck('kurikulum_id');
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
            ->select('dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas')
            ->distinct()
            ->get()
            ->map(fn ($row) => $this->edomKey($row->dosen_id, $row->kurikulum_id, $row->jenis_dosen, $row->jenis_kelas));

        $filledPairs = DB::table('penilaian')
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->select('dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas')
            ->distinct()
            ->get()
            ->map(fn ($row) => $this->edomKey($row->dosen_id, $row->kurikulum_id, $row->jenis_dosen, $row->jenis_kelas));

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

    private function normalizeJenisKelas(?string $jenisKelas): string
    {
        return match (strtolower(trim((string) $jenisKelas))) {
            'karyawan', 'reguler b' => 'karyawan',
            default => 'reguler',
        };
    }

    private function edomKey($dosenId, $kurikulumId, ?string $jenisDosen, ?string $jenisKelas): string
    {
        return implode('|', [
            (int) $dosenId,
            (int) $kurikulumId,
            strtolower(trim((string) $jenisDosen)),
            $this->normalizeJenisKelas($jenisKelas),
        ]);
    }
}
