<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminKrsController extends Controller
{
    /**
     * Halaman utama Manajemen KRS Admin
     */
    public function index(Request $request)
    {
        $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();
        $programStudiList = ProgramStudi::orderBy('nama')->get();

        if (! $tahunAjaran) {
            return redirect()->route('admin.home')->with('error', 'Tidak ada Tahun Ajaran aktif.');
        }

        // Statistik
        $totalMahasiswaAktif = Mahasiswa::where('status_mhs', 'aktif')->count();

        $totalKrsRecords = Krs::where('ta_id', $tahunAjaran->ta_id)->count();

        $mahasiswaWithKrs = Krs::where('ta_id', $tahunAjaran->ta_id)
            ->distinct('mahasiswa_id')
            ->count('mahasiswa_id');

        $mahasiswaTanpaKrs = $totalMahasiswaAktif - $mahasiswaWithKrs;
        if ($mahasiswaTanpaKrs < 0) {
            $mahasiswaTanpaKrs = 0;
        }

        $totalKurikulumAktif = Kurikulum::where('ta_id', $tahunAjaran->ta_id)->count();

        $stats = [
            'total_mahasiswa' => $totalMahasiswaAktif,
            'total_krs' => $totalKrsRecords,
            'mahasiswa_with_krs' => $mahasiswaWithKrs,
            'mahasiswa_tanpa_krs' => $mahasiswaTanpaKrs,
            'total_kurikulum' => $totalKurikulumAktif,
            'persentase_krs' => $totalMahasiswaAktif > 0
                ? round($mahasiswaWithKrs / $totalMahasiswaAktif * 100, 1)
                : 0,
        ];

        return view('admin.akademik.krs.index', compact(
            'tahunAjaran',
            'programStudiList',
            'stats'
        ));
    }

    /**
     * AJAX: Ambil data KRS berdasarkan filter
     */
    public function getKrsByFilter(Request $request)
    {
        $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();

        if (! $tahunAjaran) {
            return response()->json(['message' => 'Tidak ada Tahun Ajaran aktif.'], 400);
        }

        $jurusanId = $request->input('jurusan_id');
        $semester = $request->input('semester');
        $search = $request->input('search');

        // Query mahasiswa dengan KRS mereka
        $query = Mahasiswa::with(['programStudi', 'krs' => function ($q) use ($tahunAjaran, $semester) {
            $q->where('ta_id', $tahunAjaran->ta_id)
                ->with(['kurikulum.mataKuliah']);

            if ($semester) {
                $q->whereHas('kurikulum.mataKuliah', function ($mq) use ($semester) {
                    $mq->where('smt', $semester);
                });
            }
        }])
            ->where('status_mhs', 'aktif')
            ->whereHas('krs', function ($q) use ($tahunAjaran, $semester) {
                $q->where('ta_id', $tahunAjaran->ta_id);
                if ($semester) {
                    $q->whereHas('kurikulum.mataKuliah', function ($mq) use ($semester) {
                        $mq->where('smt', $semester);
                    });
                }
            });

        if ($jurusanId) {
            $query->where('jurusan_id', $jurusanId);
        }

        if ($semester) {
            $query->where('semester', $semester);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('nim', 'like', '%'.$search.'%');
            });
        }

        $mahasiswa = $query->orderBy('nama', 'asc')->paginate(15)->appends($request->query());

        return response()->json([
            'html' => view('admin.akademik.krs.partials_list', compact('mahasiswa', 'tahunAjaran'))->render(),
        ]);
    }

    /**
     * AJAX: Ambil daftar mahasiswa untuk Select2
     */
    public function getMahasiswaForKrs(Request $request)
    {
        $search = $request->input('search', '');
        $jurusanId = $request->input('jurusan_id');
        $semester = $request->input('semester');

        $query = Mahasiswa::with('programStudi')
            ->where('status_mhs', 'aktif')
            ->orderBy('nama');

        if ($jurusanId) {
            $query->where('jurusan_id', $jurusanId);
        }

        if ($semester) {
            $query->where('semester', $semester);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('nim', 'like', '%'.$search.'%');
            });
        }

        $mahasiswa = $query->limit(50)->get()->map(function ($m) {
            return [
                'id' => $m->mahasiswa_id,
                'text' => $m->nim.' - '.$m->nama.' (Smt '.$m->semester.')',
                'nama' => $m->nama,
                'nim' => $m->nim,
                'semester' => $m->semester,
                'prodi' => $m->programStudi->nama ?? '-',
            ];
        });

        return response()->json($mahasiswa);
    }

    /**
     * AJAX: Ambil daftar kurikulum untuk assign KRS
     */
    public function getKurikulumForKrs(Request $request)
    {
        $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();

        if (! $tahunAjaran) {
            return response()->json([]);
        }

        $jurusanId = $request->input('jurusan_id');
        $semester = $request->input('semester');

        $query = Kurikulum::with(['mataKuliah', 'programStudi'])
            ->where('ta_id', $tahunAjaran->ta_id);

        if ($jurusanId) {
            $query->where('jurusan_id', $jurusanId);
        }

        if ($semester) {
            $query->whereHas('mataKuliah', function ($q) use ($semester) {
                $q->where('smt', $semester);
            });
        }

        $kurikulum = $query->get()->map(function ($k) {
            return [
                'id' => $k->kurikulum_id,
                'text' => ($k->mataKuliah->matakuliah_id ?? '').' - '.($k->mataKuliah->nama ?? 'N/A').' ('.($k->mataKuliah->sks ?? 0).' SKS, Smt '.($k->mataKuliah->smt ?? '-').')',
                'nama' => $k->mataKuliah->nama ?? 'N/A',
                'sks' => $k->mataKuliah->sks ?? 0,
                'smt' => $k->mataKuliah->smt ?? '-',
            ];
        });

        return response()->json($kurikulum);
    }

    /**
     * Simpan KRS individual: 1 mahasiswa + N mata kuliah
     */
    public function store(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|integer|exists:mahasiswa,mahasiswa_id',
            'kurikulum_ids' => 'required|array|min:1',
            'kurikulum_ids.*' => 'integer|exists:kurikulum,kurikulum_id',
        ]);

        $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();
        if (! $tahunAjaran) {
            return response()->json(['success' => false, 'message' => 'Tidak ada Tahun Ajaran aktif.']);
        }

        try {
            $created = 0;
            $skipped = 0;

            $kurikulumList = Kurikulum::whereIn('kurikulum_id', $request->kurikulum_ids)
                ->where('ta_id', $tahunAjaran->ta_id)
                ->get()
                ->unique('matakuliah_id');

            foreach ($kurikulumList as $kurikulum) {
                $exists = Krs::where('matakuliah_id', $kurikulum->matakuliah_id)
                    ->where('mahasiswa_id', $request->mahasiswa_id)
                    ->where('ta_id', $tahunAjaran->ta_id)
                    ->exists();

                if (! $exists) {
                    Krs::create([
                        'kurikulum_id' => $kurikulum->kurikulum_id,
                        'matakuliah_id' => $kurikulum->matakuliah_id,
                        'mahasiswa_id' => $request->mahasiswa_id,
                        'ta_id' => $tahunAjaran->ta_id,
                    ]);
                    $created++;
                } else {
                    $skipped++;
                }
            }

            $mahasiswa = Mahasiswa::find($request->mahasiswa_id);
            activity_log('admin_input_krs', 'Admin menambahkan '.$created.' KRS untuk mahasiswa: '.($mahasiswa->nama ?? 'Unknown'));

            $message = $created.' mata kuliah berhasil ditambahkan ke KRS.';
            if ($skipped > 0) {
                $message .= ' '.$skipped.' mata kuliah sudah ada sebelumnya (dilewati).';
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            Log::error('Error store KRS: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: '.$e->getMessage()]);
        }
    }

    /**
     * Bulk assign KRS: semua mahasiswa sesuai filter → assign semua kurikulum
     */
    public function bulkStore(Request $request)
    {
        $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();
        if (! $tahunAjaran) {
            return response()->json(['success' => false, 'message' => 'Tidak ada Tahun Ajaran aktif.']);
        }

        $jurusanId = $request->input('jurusan_id');
        $semester = $request->input('semester');
        $kelas = $request->input('kelas');

        if (! $jurusanId || ! $semester) {
            return response()->json(['success' => false, 'message' => 'Program Studi dan Semester wajib diisi untuk bulk assign.']);
        }

        try {
            // Ambil semua mahasiswa aktif sesuai filter
            $mahasiswaQuery = Mahasiswa::where('status_mhs', 'aktif')
                ->where('jurusan_id', $jurusanId)
                ->where('semester', $semester);

            if ($kelas) {
                $mahasiswaQuery->where('kelas', $kelas);
            }

            $mahasiswaList = $mahasiswaQuery->get();

            // Ambil semua kurikulum sesuai prodi + semester + TA aktif
            $kurikulumList = Kurikulum::where('ta_id', $tahunAjaran->ta_id)
                ->where('jurusan_id', $jurusanId)
                ->whereHas('mataKuliah', function ($q) use ($semester) {
                    $q->where('smt', $semester);
                })
                ->get()
                ->unique('matakuliah_id')
                ->values();

            if ($mahasiswaList->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada mahasiswa yang memenuhi filter.']);
            }

            if ($kurikulumList->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada kurikulum/mata kuliah yang tersedia untuk semester ini.']);
            }

            $created = 0;
            $skipped = 0;

            DB::beginTransaction();

            foreach ($mahasiswaList as $mhs) {
                foreach ($kurikulumList as $kur) {
                    $exists = Krs::where('matakuliah_id', $kur->matakuliah_id)
                        ->where('mahasiswa_id', $mhs->mahasiswa_id)
                        ->where('ta_id', $tahunAjaran->ta_id)
                        ->exists();

                    if (! $exists) {
                        Krs::create([
                            'kurikulum_id' => $kur->kurikulum_id,
                            'matakuliah_id' => $kur->matakuliah_id,
                            'mahasiswa_id' => $mhs->mahasiswa_id,
                            'ta_id' => $tahunAjaran->ta_id,
                        ]);
                        $created++;
                    } else {
                        $skipped++;
                    }
                }
            }

            DB::commit();

            activity_log('admin_bulk_krs', 'Admin bulk assign KRS: '.$created.' records untuk '.$mahasiswaList->count().' mahasiswa');

            $message = 'Berhasil! '.$created.' KRS dibuat untuk '.$mahasiswaList->count().' mahasiswa × '.$kurikulumList->count().' mata kuliah.';
            if ($skipped > 0) {
                $message .= ' ('.$skipped.' sudah ada, dilewati)';
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk store KRS: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: '.$e->getMessage()]);
        }
    }

    /**
     * Hapus KRS individual
     */
    public function destroy($id)
    {
        try {
            $krs = Krs::findOrFail($id);
            $mahasiswa = $krs->mahasiswa;
            $kurikulum = $krs->kurikulum;

            $krs->delete();

            activity_log('admin_hapus_krs', 'Admin menghapus KRS ID '.$id.' milik mahasiswa: '.($mahasiswa->nama ?? 'Unknown').' - MK: '.($kurikulum->mataKuliah->nama ?? 'Unknown'));

            return response()->json(['success' => true, 'message' => 'KRS berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Error delete KRS: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal menghapus KRS.']);
        }
    }
}
