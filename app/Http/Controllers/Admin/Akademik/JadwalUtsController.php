<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\JadwalUts;
use App\Models\Kurikulum;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use App\Models\Ruangan;
use App\Models\TahunAkademik;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class JadwalUtsController extends Controller
{
    /**
     * Menampilkan halaman manajemen jadwal UTS.
     */
    public function index(): View|RedirectResponse
    {
        try {
            $tahunAjaran = $this->getTahunAkademikAktif();

            if (! $tahunAjaran) {
                return redirect()
                    ->back()
                    ->with('error', 'Tidak ada tahun ajaran yang aktif.');
            }

            $matakuliah = Matakuliah::all();

            $programStudi = ProgramStudi::query()
                ->orderBy('nama')
                ->get();

            $ruangan = Ruangan::query()
                ->orderBy('nama')
                ->get();

            if ($programStudi->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('error', 'Data program studi tidak tersedia.');
            }

            return view(
                'admin.akademik.jadwal-uts.index',
                compact(
                    'programStudi',
                    'tahunAjaran',
                    'matakuliah',
                    'ruangan'
                )
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Terjadi kesalahan saat membuka halaman jadwal UTS.'
                );
        }
    }

    /**
     * Membuat data awal jadwal UTS berdasarkan kurikulum.
     */
    public function generateJadwalUTS(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'jurusan_id' => [
                'required',
                'exists:program_studi,jurusan_id',
            ],
            'jenis_kelas' => [
                'required',
                Rule::in(['Reguler', 'Karyawan']),
            ],
        ], [
            'jurusan_id.required' =>
                'Program studi wajib dipilih.',
            'jurusan_id.exists' =>
                'Program studi tidak ditemukan.',
            'jenis_kelas.required' =>
                'Jenis kelas wajib dipilih.',
            'jenis_kelas.in' =>
                'Jenis kelas tidak valid.',
        ]);

        try {
            $tahunAjaran = $this->getTahunAkademikAktif();

            if (! $tahunAjaran) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Tidak ada tahun ajaran yang aktif.'
                    );
            }

            /*
             * Jadwal hanya dibuat dari kurikulum pada tahun
             * akademik aktif.
             */
            $kurikulums = Kurikulum::query()
                ->where(
                    'jurusan_id',
                    $validated['jurusan_id']
                )
                ->where(
                    'ta_id',
                    $tahunAjaran->ta_id
                )
                ->get();

            if ($kurikulums->isEmpty()) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Data kurikulum tidak ditemukan untuk program studi dan tahun akademik aktif.'
                    );
            }

            $importedCount = DB::transaction(
                function () use ($kurikulums, $validated) {
                    $count = 0;

                    foreach ($kurikulums as $kurikulum) {
                        $exists = JadwalUts::query()
                            ->where(
                                'ta_id',
                                $kurikulum->ta_id
                            )
                            ->where(
                                'jurusan_id',
                                $kurikulum->jurusan_id
                            )
                            ->where(
                                'matakuliah_id',
                                $kurikulum->matakuliah_id
                            )
                            ->where(
                                'jenis_kelas',
                                $validated['jenis_kelas']
                            )
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        JadwalUts::create([
                            'ta_id' =>
                                $kurikulum->ta_id,
                            'jurusan_id' =>
                                $kurikulum->jurusan_id,
                            'matakuliah_id' =>
                                $kurikulum->matakuliah_id,
                            'ruangan_id' => null,
                            'jam_mulai' => null,
                            'jam_selesai' => null,

                            /*
                             * Tanggal default tujuh hari
                             * setelah jadwal ditarik.
                             */
                            'tanggal' =>
                                now()->addDays(7)->format('Y-m-d'),

                            'jenis_kelas' =>
                                $validated['jenis_kelas'],
                        ]);

                        $count++;
                    }

                    return $count;
                }
            );

            if ($importedCount > 0) {
                activity_log(
                    'generate_jadwal_uts',
                    'Admin generate ' .
                    $importedCount .
                    ' jadwal UTS untuk prodi ' .
                    $validated['jurusan_id'] .
                    ' kelas ' .
                    $validated['jenis_kelas']
                );

                Alert::toast(
                    $importedCount .
                    ' jadwal UTS berhasil di-import.',
                    'success'
                )
                    ->position('center')
                    ->autoClose(3000);
            } else {
                Alert::toast(
                    'Tidak ada data baru yang di-import.',
                    'warning'
                )
                    ->position('center')
                    ->autoClose(3000);
            }

            return redirect()->back();
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Terjadi kesalahan saat menarik data jadwal UTS.'
                );
        }
    }

    /**
     * Mengambil jadwal berdasarkan program studi,
     * semester, jenis kelas, dan tahun akademik aktif.
     */
    public function filter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'programStudi' => [
                'required',
                'exists:program_studi,jurusan_id',
            ],
            'semester' => [
                'required',
                'integer',
                'between:1,8',
            ],
            'jenis_kelas' => [
                'required',
                Rule::in(['Reguler', 'Karyawan']),
            ],
        ], [
            'programStudi.required' =>
                'Program studi wajib dipilih.',
            'programStudi.exists' =>
                'Program studi tidak ditemukan.',
            'semester.required' =>
                'Semester wajib dipilih.',
            'semester.integer' =>
                'Semester harus berupa angka.',
            'semester.between' =>
                'Semester harus antara 1 sampai 8.',
            'jenis_kelas.required' =>
                'Jenis kelas wajib dipilih.',
            'jenis_kelas.in' =>
                'Jenis kelas tidak valid.',
        ]);

        try {
            $tahunAjaran = $this->getTahunAkademikAktif();

            if (! $tahunAjaran) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Tidak ada tahun ajaran yang aktif.',
                ], 404);
            }

            $jadwal = JadwalUts::query()
                ->select([
                    'jadwal_uts.id',
                    'jadwal_uts.ta_id',
                    'jadwal_uts.jurusan_id',
                    'jadwal_uts.matakuliah_id',
                    'matakuliah.nama as nama_matakuliah',
                    'matakuliah.smt as semester',
                    'jadwal_uts.jam_mulai',
                    'jadwal_uts.jam_selesai',
                    'jadwal_uts.tanggal',
                    'ruangan.nama as nama_ruangan',
                    'jadwal_uts.ruangan_id',
                    'jadwal_uts.jenis_kelas',
                ])
                ->join(
                    'matakuliah',
                    'jadwal_uts.matakuliah_id',
                    '=',
                    'matakuliah.matakuliah_id'
                )
                ->leftJoin(
                    'ruangan',
                    'jadwal_uts.ruangan_id',
                    '=',
                    'ruangan.ruangan_id'
                )
                ->where(
                    'jadwal_uts.jurusan_id',
                    $validated['programStudi']
                )
                ->where(
                    'matakuliah.smt',
                    $validated['semester']
                )
                ->where(
                    'jadwal_uts.jenis_kelas',
                    $validated['jenis_kelas']
                )
                ->where(
                    'jadwal_uts.ta_id',
                    $tahunAjaran->ta_id
                )
                ->orderByRaw(
                    'jadwal_uts.tanggal IS NULL ASC'
                )
                ->orderBy(
                    'jadwal_uts.tanggal',
                    'asc'
                )
                ->orderByRaw(
                    'jadwal_uts.jam_mulai IS NULL ASC'
                )
                ->orderBy(
                    'jadwal_uts.jam_mulai',
                    'asc'
                )
                ->orderBy(
                    'matakuliah.nama',
                    'asc'
                )
                ->get();

            if ($jadwal->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Tidak ada jadwal UTS yang ditemukan untuk program studi, semester, dan jenis kelas tersebut.',
                ], 404);
            }

            return response()->json($jadwal);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Terjadi kesalahan pada server saat mengambil jadwal.',
            ], 500);
        }
    }

    /**
     * Memperbarui satu field jadwal melalui AJAX.
     */
    public function update(
        Request $request,
        int|string $id
    ): JsonResponse {
        /*
         * Field dibatasi agar pengguna tidak dapat mengubah
         * kolom lain dengan memanipulasi request.
         */
        $request->validate([
            'field' => [
                'required',
                Rule::in([
                    'tanggal',
                    'jam_mulai',
                    'jam_selesai',
                    'ruangan_id',
                ]),
            ],
        ], [
            'field.required' =>
                'Field yang akan diperbarui tidak ditemukan.',
            'field.in' =>
                'Field tersebut tidak diizinkan untuk diperbarui.',
        ]);

        $jadwal = JadwalUts::find($id);

        if (! $jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan.',
            ], 404);
        }

        $field = $request->input('field');

        /*
         * Validasi value disesuaikan dengan field.
         */
        $rules = match ($field) {
            'tanggal' => [
                'required',
                'date_format:Y-m-d',
            ],

            'jam_mulai' => [
                'required',
                'date_format:H:i',
            ],

            'jam_selesai' => [
                'required',
                'date_format:H:i',
                function (
                    string $attribute,
                    mixed $value,
                    \Closure $fail
                ) use ($request, $jadwal) {
                    $jamMulai = $jadwal->jam_mulai;

                    /*
                     * Apabila request yang akan datang nantinya
                     * membawa jam_mulai, gunakan nilai tersebut.
                     */
                    if ($request->filled('jam_mulai')) {
                        $jamMulai =
                            $request->input('jam_mulai');
                    }

                    if (
                        $jamMulai &&
                        $value <= substr(
                            (string) $jamMulai,
                            0,
                            5
                        )
                    ) {
                        $fail(
                            'Jam selesai harus lebih besar dari jam mulai.'
                        );
                    }
                },
            ],

            'ruangan_id' => [
                'required',
                'exists:ruangan,ruangan_id',
            ],

            default => [],
        };

        $messages = [
            'value.required' =>
                'Nilai wajib diisi.',
            'value.date_format' =>
                $field === 'tanggal'
                    ? 'Format tanggal harus lengkap: tanggal, bulan, dan tahun.'
                    : 'Format jam tidak valid.',
            'value.exists' =>
                'Ruangan yang dipilih tidak ditemukan.',
        ];

        $validatedValue = $request->validate(
            [
                'value' => $rules,
            ],
            $messages
        );

        try {
            /*
             * Validasi tambahan ketika jam mulai diubah
             * tetapi jam selesai sudah tersedia.
             */
            if (
                $field === 'jam_mulai' &&
                $jadwal->jam_selesai
            ) {
                $jamMulaiBaru =
                    substr($validatedValue['value'], 0, 5);

                $jamSelesai =
                    substr(
                        (string) $jadwal->jam_selesai,
                        0,
                        5
                    );

                if ($jamMulaiBaru >= $jamSelesai) {
                    return response()->json([
                        'success' => false,
                        'message' =>
                            'Jam mulai harus lebih kecil dari jam selesai.',
                    ], 422);
                }
            }

            $value = $validatedValue['value'];

            /*
             * Jam disimpan dalam format HH:mm:ss agar
             * sesuai dengan kolom TIME pada database.
             */
            if (
                in_array(
                    $field,
                    ['jam_mulai', 'jam_selesai'],
                    true
                )
            ) {
                $value .= ':00';
            }

            $jadwal->update([
                $field => $value,
            ]);

            activity_log(
                'update_jadwal_uts',
                'Admin memperbarui jadwal UTS ID: ' .
                $id .
                ' (' .
                $field .
                ') menjadi ' .
                $value
            );

            return response()->json([
                'success' => true,
                'message' =>
                    $this->getUpdateSuccessMessage($field),
                'data' => [
                    'id' => $jadwal->id,
                    'field' => $field,
                    'value' => $value,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Terjadi kesalahan saat menyimpan perubahan.',
            ], 500);
        }
    }

    /**
     * Menghapus jadwal UTS.
     */
    public function destroy(
        int|string $id
    ): JsonResponse {
        try {
            $jadwal = JadwalUts::find($id);

            if (! $jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }

            activity_log(
                'hapus_jadwal_uts',
                'Admin menghapus jadwal UTS ID: ' .
                $id
            );

            $jadwal->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Terjadi kesalahan saat menghapus jadwal.',
            ], 500);
        }
    }

    /**
     * Mengambil tahun akademik aktif dari cache.
     */
    private function getTahunAkademikAktif(): ?TahunAkademik
    {
        return Cache::remember(
            'active_tahun_akademik',
            3600,
            function () {
                return TahunAkademik::query()
                    ->where('status_ta', 1)
                    ->first();
            }
        );
    }

    /**
     * Pesan sukses berdasarkan field yang diperbarui.
     */
    private function getUpdateSuccessMessage(
        string $field
    ): string {
        return match ($field) {
            'tanggal' =>
                'Tanggal ujian berhasil diperbarui.',

            'jam_mulai' =>
                'Jam mulai berhasil diperbarui.',

            'jam_selesai' =>
                'Jam selesai berhasil diperbarui.',

            'ruangan_id' =>
                'Ruangan ujian berhasil diperbarui.',

            default =>
                'Jadwal berhasil diperbarui.',
        };
    }
}