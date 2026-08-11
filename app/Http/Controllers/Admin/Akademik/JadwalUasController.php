<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\JadwalUas;
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

class JadwalUasController extends Controller
{
    public function index(): View|RedirectResponse
    {
        try {
            $tahunAjaran = $this->getTahunAkademikAktif();

            if (! $tahunAjaran) {
                return redirect()->back()->with('error', 'Tidak ada tahun ajaran yang aktif.');
            }

            $matakuliah = Matakuliah::all();
            $programStudi = ProgramStudi::query()->orderBy('nama')->get();
            $ruangan = Ruangan::query()->orderBy('nama')->get();

            if ($programStudi->isEmpty()) {
                return redirect()->back()->with('error', 'Data program studi tidak tersedia.');
            }

            return view(
                'admin.akademik.jadwal-uas.index',
                compact('programStudi', 'tahunAjaran', 'matakuliah', 'ruangan')
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat membuka halaman jadwal UAS.');
        }
    }

    public function filter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'programStudi' => ['required', 'exists:program_studi,jurusan_id'],
            'semester' => ['required', 'integer', 'between:1,8'],
            'jenis_kelas' => ['required', Rule::in(['Reguler', 'Karyawan'])],
        ], [
            'programStudi.required' => 'Program studi wajib dipilih.',
            'programStudi.exists' => 'Program studi tidak ditemukan.',
            'semester.required' => 'Semester wajib dipilih.',
            'semester.between' => 'Semester harus antara 1 sampai 8.',
            'jenis_kelas.required' => 'Jenis kelas wajib dipilih.',
            'jenis_kelas.in' => 'Jenis kelas tidak valid.',
        ]);

        try {
            $tahunAjaran = $this->getTahunAkademikAktif();

            if (! $tahunAjaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tahun ajaran yang aktif.',
                ], 404);
            }

            $jadwal = JadwalUas::query()
                ->select([
                    'jadwal_uas.id',
                    'jadwal_uas.ta_id',
                    'jadwal_uas.jurusan_id',
                    'jadwal_uas.matakuliah_id',
                    'matakuliah.nama as nama_matakuliah',
                    'matakuliah.smt as semester',
                    'jadwal_uas.jam_mulai',
                    'jadwal_uas.jam_selesai',
                    'jadwal_uas.tanggal',
                    'ruangan.nama as nama_ruangan',
                    'jadwal_uas.ruangan_id',
                    'jadwal_uas.jenis_kelas',
                ])
                ->join('matakuliah', 'jadwal_uas.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                ->leftJoin('ruangan', 'jadwal_uas.ruangan_id', '=', 'ruangan.ruangan_id')
                ->where('jadwal_uas.jurusan_id', $validated['programStudi'])
                ->where('matakuliah.smt', $validated['semester'])
                ->where('jadwal_uas.jenis_kelas', $validated['jenis_kelas'])
                ->where('jadwal_uas.ta_id', $tahunAjaran->ta_id)
                ->orderByRaw('jadwal_uas.tanggal IS NULL ASC')
                ->orderBy('jadwal_uas.tanggal')
                ->orderByRaw('jadwal_uas.jam_mulai IS NULL ASC')
                ->orderBy('jadwal_uas.jam_mulai')
                ->orderBy('matakuliah.nama')
                ->get();

            if ($jadwal->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada jadwal UAS yang ditemukan untuk program studi, semester, dan jenis kelas tersebut.',
                ], 404);
            }

            return response()->json($jadwal);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server saat mengambil jadwal UAS.',
            ], 500);
        }
    }

    public function generateJadwalUAS(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jurusan_id' => ['required', 'exists:program_studi,jurusan_id'],
            'jenis_kelas' => ['required', Rule::in(['Reguler', 'Karyawan'])],
        ]);

        try {
            $tahunAjaran = $this->getTahunAkademikAktif();

            if (! $tahunAjaran) {
                return redirect()->back()->with('error', 'Tidak ada tahun ajaran yang aktif.');
            }

            $kurikulums = Kurikulum::query()
                ->where('jurusan_id', $validated['jurusan_id'])
                ->where('ta_id', $tahunAjaran->ta_id)
                ->get();

            if ($kurikulums->isEmpty()) {
                return redirect()->back()->with(
                    'error',
                    'Data kurikulum tidak ditemukan untuk program studi dan tahun akademik aktif.'
                );
            }

            $importedCount = DB::transaction(function () use ($kurikulums, $validated) {
                $count = 0;

                foreach ($kurikulums as $kurikulum) {
                    $exists = JadwalUas::query()->where([
                        'ta_id' => $kurikulum->ta_id,
                        'jurusan_id' => $kurikulum->jurusan_id,
                        'matakuliah_id' => $kurikulum->matakuliah_id,
                        'jenis_kelas' => $validated['jenis_kelas'],
                    ])->exists();

                    if ($exists) {
                        continue;
                    }

                    JadwalUas::create([
                        'ta_id' => $kurikulum->ta_id,
                        'jurusan_id' => $kurikulum->jurusan_id,
                        'matakuliah_id' => $kurikulum->matakuliah_id,
                        'ruangan_id' => null,
                        'jam_mulai' => null,
                        'jam_selesai' => null,
                        'tanggal' => now()->addDays(7)->format('Y-m-d'),
                        'jenis_kelas' => $validated['jenis_kelas'],
                    ]);

                    $count++;
                }

                return $count;
            });

            if ($importedCount > 0) {
                activity_log(
                    'generate_jadwal_uas',
                    'Admin generate '.$importedCount.' jadwal UAS untuk prodi '.$validated['jurusan_id']
                );

                Alert::toast("$importedCount Jadwal UAS berhasil di-import.", 'success')
                    ->position('center')
                    ->autoClose(3000);
            } else {
                Alert::toast('Tidak ada data baru yang di-import.', 'warning')
                    ->position('center')
                    ->autoClose(3000);
            }

            return redirect()->back();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menarik data jadwal UAS.');
        }
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $request->validate([
            'field' => [
                'required',
                Rule::in(['tanggal', 'jam_mulai', 'jam_selesai', 'ruangan_id']),
            ],
        ]);

        $jadwal = JadwalUas::find($id);

        if (! $jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan.',
            ], 404);
        }

        $field = $request->input('field');

        $rules = match ($field) {
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam_mulai', 'jam_selesai' => ['required', 'date_format:H:i'],
            'ruangan_id' => ['required', 'exists:ruangan,ruangan_id'],
            default => [],
        };

        $validated = $request->validate([
            'value' => $rules,
        ], [
            'value.required' => 'Nilai wajib diisi.',
            'value.date_format' => $field === 'tanggal'
                ? 'Tanggal harus diisi lengkap dengan format tanggal, bulan, dan tahun.'
                : 'Format jam tidak valid.',
            'value.exists' => 'Ruangan yang dipilih tidak ditemukan.',
        ]);

        try {
            $value = $validated['value'];

            if ($field === 'jam_mulai' && $jadwal->jam_selesai) {
                $jamSelesai = substr((string) $jadwal->jam_selesai, 0, 5);

                if ($value >= $jamSelesai) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Jam mulai harus lebih kecil dari jam selesai.',
                    ], 422);
                }
            }

            if ($field === 'jam_selesai' && $jadwal->jam_mulai) {
                $jamMulai = substr((string) $jadwal->jam_mulai, 0, 5);

                if ($value <= $jamMulai) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Jam selesai harus lebih besar dari jam mulai.',
                    ], 422);
                }
            }

            if (in_array($field, ['jam_mulai', 'jam_selesai'], true)) {
                $value .= ':00';
            }

            $jadwal->update([$field => $value]);

            activity_log(
                'update_jadwal_uas',
                'Admin memperbarui jadwal UAS ID: '.$id.' ('.$field.')'
            );

            return response()->json([
                'success' => true,
                'message' => $this->getUpdateSuccessMessage($field),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan perubahan.',
            ], 500);
        }
    }

    public function destroy(int|string $id): JsonResponse
    {
        try {
            $jadwal = JadwalUas::find($id);

            if (! $jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }

            activity_log('hapus_jadwal_uas', 'Admin menghapus jadwal UAS ID: '.$id);
            $jadwal->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus jadwal UAS.',
            ], 500);
        }
    }

    private function getTahunAkademikAktif(): ?TahunAkademik
    {
        return Cache::remember('active_tahun_akademik', 3600, function () {
            return TahunAkademik::query()->where('status_ta', 1)->first();
        });
    }

    private function getUpdateSuccessMessage(string $field): string
    {
        return match ($field) {
            'tanggal' => 'Tanggal ujian berhasil diperbarui.',
            'jam_mulai' => 'Jam mulai berhasil diperbarui.',
            'jam_selesai' => 'Jam selesai berhasil diperbarui.',
            'ruangan_id' => 'Ruangan ujian berhasil diperbarui.',
            default => 'Jadwal berhasil diperbarui.',
        };
    }
}