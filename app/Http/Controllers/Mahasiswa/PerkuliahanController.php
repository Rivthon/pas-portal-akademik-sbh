<?php

namespace App\Http\Controllers\mahasiswa;

use PDF;
use App\Models\Jadwal;
use App\Models\Setting;
use App\Models\Jadwaluap;
use App\Models\Jadwaluas;
use App\Models\Jadwaluts;
use App\Models\Kurikulum;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\JadwalPraktik;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PerkuliahanController extends Controller
{
    public function index(Request $request)

        {
            $semester = Auth::guard('mahasiswa')->user()->semester;
            $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
            $search = $request->input('search');
            $activeTA = TahunAkademik::where('status_ta', 1)->first(); // Ambil Tahun Ajaran Aktif

            // Pastikan ada tahun ajaran aktif
            if (!$activeTA) {
                if ($request->ajax()) {
                    return response()->json([
                        'error' => 'Tidak ada Tahun Ajaran yang aktif.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
            }

            // Query awal dengan eager loading
          $semester = Auth::guard('mahasiswa')->user()->semester;
            $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
            $search = $request->input('search');
            $activeTA = TahunAkademik::where('status_ta', 1)->first(); // Ambil Tahun Ajaran Aktif

            // Pastikan ada tahun ajaran aktif
            if (!$activeTA) {
                if ($request->ajax()) {
                    return response()->json([
                        'error' => 'Tidak ada Tahun Ajaran yang aktif.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
            }

            // Query awal dengan eager loading
           $jadwals = Jadwal::with([
                'kurikulum.mataKuliah', // Ambil mata kuliah dari kurikulum
                'kurikulum.dosenToMatakuliah.dosen', // Ambil dosen dari relasi dosenToMatakuliah
                'programStudi',
                'ruangan' // Ambil program studi untuk filter
            ])
            ->where('ta_id', $activeTA->ta_id)
            ->where(function ($query) {
                $jenisKelas = Auth::guard('mahasiswa')->user()->kelas;
                if ($jenisKelas === 'reguler') {
                    $query->where('jenis_kelas', 'reguler');
                } elseif ($jenisKelas === 'karyawan') {
                    $query->where('jenis_kelas', 'karyawan');
                }
            })
            ->whereHas('kurikulum.mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester); // Filter berdasarkan semester mata kuliah
            })
            ->whereHas('programStudi', function ($query) {
                $jurusanId = Auth::guard('mahasiswa')->user()->jurusan_id; // Ambil jurusan_id dari mahasiswa
                $query->where('jurusan_id', $jurusanId);
            })
            ->get()
            ->map(function ($jadwal) {
                return [
                    'jadwal_id' => $jadwal->id,
                    'nama_matakuliah' => $jadwal->kurikulum->mataKuliah->nama ?? 'Tidak ada data',
                    'kode_matakuliah' => $jadwal->kurikulum->mataKuliah->kode ?? '-',
                    'semester' => $jadwal->kurikulum->mataKuliah->smt ?? '-',
                    'hari' => $jadwal->hari ?? '-',
                    'jam_mulai' => $jadwal->jam_mulai ?? '-',
                    'jam_selesai' => $jadwal->jam_selesai ?? '-',
                    'ruangan' => $jadwal->ruangan->nama ?? 'Tidak ada data',
                    'dosen' => $jadwal->kurikulum->dosenToMatakuliah->map(function ($dosenToMatakuliah) {
                            return [
                                'id' => $dosenToMatakuliah->dosen->dosen_id ?? null,
                                'nama' => $dosenToMatakuliah->dosen->nama ?? 'Tidak ada data',
                                'jenis_dosen' => $dosenToMatakuliah->jenis_dosen ?? 'tidak diketahui',
                                 'jenis_kelas' => $dosenToMatakuliah->jenis_kelas ?? 'tidak diketahui',
                            ];
                             })->filter(function ($dosen) {
                            return $dosen['jenis_dosen'] === 'teori'; // Hanya ambil dosen praktik
                        })->unique('id')->values(),
                    ];
            });
            // Jika bukan AJAX, kirim ke view utama
            return view('students.jadwal.index', compact('jadwals'));
        }

        public function jadwalPraktik(Request $request)

        {
            $semester = Auth::guard('mahasiswa')->user()->semester;
            $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
            $search = $request->input('search');
            $activeTA = TahunAkademik::where('status_ta', 1)->first(); // Ambil Tahun Ajaran Aktif

            // Pastikan ada tahun ajaran aktif
            if (!$activeTA) {
                if ($request->ajax()) {
                    return response()->json([
                        'error' => 'Tidak ada Tahun Ajaran yang aktif.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
            }

            // Query awal dengan eager loading
           $jadwals = JadwalPraktik::with([
                'kurikulum.mataKuliah', // Ambil mata kuliah dari kurikulum
                'kurikulum.dosenToMatakuliah.dosen', // Ambil dosen dari relasi dosenToMatakuliah
                'programStudi',
                'ruangan' // Ambil program studi untuk filter
            ])
            ->where('ta_id', $activeTA->ta_id)
            ->where(function ($query) {
                $jenisKelas = Auth::guard('mahasiswa')->user()->kelas;
                if ($jenisKelas === 'reguler') {
                    $query->where('jenis_kelas', 'reguler');
                } elseif ($jenisKelas === 'karyawan') {
                    $query->where('jenis_kelas', 'karyawan');
                }
            })
            ->whereHas('kurikulum.mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester); // Filter berdasarkan semester mata kuliah
            })

            ->whereHas('programStudi', function ($query) {
                $jurusanId = Auth::guard('mahasiswa')->user()->jurusan_id; // Ambil jurusan_id dari mahasiswa
                $query->where('jurusan_id', $jurusanId);
            })
            ->get()
            ->map(function ($jadwal) {
                return [
                    'jadwal_id' => $jadwal->id,
                    'nama_matakuliah' => $jadwal->kurikulum->mataKuliah->nama ?? 'Tidak ada data',
                    'kode_matakuliah' => $jadwal->kurikulum->mataKuliah->kode ?? '-',
                    'semester' => $jadwal->kurikulum->mataKuliah->smt ?? '-',
                    'hari' => $jadwal->hari ?? '-',
                    'jam_mulai' => $jadwal->jam_mulai ?? '-',
                    'jam_selesai' => $jadwal->jam_selesai ?? '-',
                    'ruangan' => $jadwal->ruangan->nama ?? 'Tidak ada data',
                    'dosen' => $jadwal->kurikulum->dosenToMatakuliah->map(function ($dosenToMatakuliah) {
                            return [
                                'id' => $dosenToMatakuliah->dosen->dosen_id ?? null,
                                'nama' => $dosenToMatakuliah->dosen->nama ?? 'Tidak ada data',
                                'jenis_dosen' => $dosenToMatakuliah->jenis_dosen ?? 'tidak diketahui',
                                 'jenis_kelas' => $dosenToMatakuliah->jenis_kelas ?? 'tidak diketahui',
                            ];
                             })->filter(function ($dosen) {
                            return $dosen['jenis_dosen'] === 'praktik'; // Hanya ambil dosen praktik
                        })->unique('id')->values(),
                    ];
            });
            // Jika bukan AJAX, kirim ke view utama
            return view('students.jadwal.praktik', compact('jadwals'));
        }


    public function jadwalUts(Request $request)
   {
            $semester = Auth::guard('mahasiswa')->user()->semester;
            $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
            $search = $request->input('search');
            $activeTA = TahunAkademik::where('status_ta', 1)->first(); // Ambil Tahun Ajaran Aktif

            // Pastikan ada tahun ajaran aktif
            if (!$activeTA) {
                if ($request->ajax()) {
                    return response()->json([
                        'error' => 'Tidak ada Tahun Ajaran yang aktif.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
            }

            // Query awal dengan eager loading
            $jadwalUts = Jadwaluts::with(['programStudi', 'mataKuliah', 'ruangan'])
            ->where('ta_id', $activeTA->ta_id)
              ->where(function ($query) {
                $jenisKelas = Auth::guard('mahasiswa')->user()->kelas;
                if ($jenisKelas === 'pagi') {
                    $query->where('jenis_kelas', 'reguler');
                } elseif ($jenisKelas === 'karyawan') {
                    $query->where('jenis_kelas', 'karyawan');
                }
            })
            ->whereHas('mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester);
            })
            ->whereHas('programStudi', function ($query) {
                $jurusanId = Auth::guard('mahasiswa')->user()->jurusan_id;
                $query->where('jurusan_id', $jurusanId);
            })
            ->get();
            // Jika bukan AJAX, kirim ke view utama
            return view('students.jadwal-uts.index', compact('jadwalUts'));
        }


     public function jadwalUas(Request $request)
   {

            $semester = Auth::guard('mahasiswa')->user()->semester;
            $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;

            $search = $request->input('search');
            $activeTA = TahunAkademik::where('status_ta', 1)->first(); // Ambil Tahun Ajaran Aktif

            // Pastikan ada tahun ajaran aktif
            if (!$activeTA) {
                if ($request->ajax()) {
                    return response()->json([
                        'error' => 'Tidak ada Tahun Ajaran yang aktif.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
            }

            // Query awal dengan eager loading
            $jadwalUas = Jadwaluas::with(['programStudi', 'mataKuliah', 'ruangan'])
            ->where('ta_id', $activeTA->ta_id)
              ->where(function ($query) {
                $jenisKelas = Auth::guard('mahasiswa')->user()->kelas;
                if ($jenisKelas === 'pagi') {
                    $query->where('jenis_kelas', 'reguler');
                } elseif ($jenisKelas === 'karyawan') {
                    $query->where('jenis_kelas', 'karyawan');
                }
            })
            ->whereHas('mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester);
            })
            ->whereHas('programStudi', function ($query) {
                $jurusanId = Auth::guard('mahasiswa')->user()->jurusan_id;
                $query->where('jurusan_id', $jurusanId);
            })
            ->get();
            // Jika bukan AJAX, kirim ke view utama
            return view('students.jadwal-uas.index', compact('jadwalUas'));
        }

        public function jadwalUap()
        {
            $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();

            if (!$tahunAjaran) {
                return redirect()->back()->with('error', 'Tidak ada tahun ajaran yang aktif.');
            }

            // Ambil semua program studi
            $programStudi = ProgramStudi::all();

            if ($programStudi->isEmpty()) {
                return redirect()->back()->with('error', 'Data program studi tidak tersedia.');
            }

            // Ambil semua jadwal UAP (tanpa relasi matakuliah dan ruangan)
            $jadwal = Jadwaluap::where('ta_id', $tahunAjaran->ta_id)->get();

            return view('students.jadwal-uap.index', compact('jadwal'));
        }

    public function cetakUts()
        {
             $settings = Setting::first();
            // Ambil data mahasiswa yang sedang login
            $mahasiswa = auth()->guard('mahasiswa')->user();
            $semester = $mahasiswa->semester;
            $prodi = $mahasiswa->jurusan_id;

            // Ambil Tahun Akademik Aktif
            $activeTA = TahunAkademik::where('status_ta', 1)->first();

            if (!$activeTA) {
                return back()->with('error', 'Tahun Akademik Aktif tidak ditemukan.');
            }
             $logoBase64 = null;
            if ($settings && $settings->logo) {
                $logoPath = public_path('storage/' . $settings->logo);
                if (file_exists($logoPath)) {
                    $logoBase64 = base64_encode(file_get_contents($logoPath));
                }
            }
             $ttd = null;
            if ($mahasiswa && $mahasiswa->programStudi->ttd) {
                $logoPath = public_path('storage/' . $mahasiswa->programStudi->ttd);
                if (file_exists($logoPath)) {
                    $ttd = base64_encode(file_get_contents($logoPath));
                }
            }
            // Query awal dengan eager loading
            $jadwalUts = Jadwaluts::with(['programStudi', 'mataKuliah', 'ruangan'])
            ->where('ta_id', $activeTA->ta_id)
              ->where(function ($query) {
                $jenisKelas = Auth::guard('mahasiswa')->user()->kelas;
                if ($jenisKelas === 'pagi') {
                    $query->where('jenis_kelas', 'reguler');
                } elseif ($jenisKelas === 'karyawan') {
                    $query->where('jenis_kelas', 'karyawan');
                }
            })
            ->whereHas('mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester);
            })
            ->whereHas('programStudi', function ($query) {
                $jurusanId = Auth::guard('mahasiswa')->user()->jurusan_id;
                $query->where('jurusan_id', $jurusanId);
            })
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

            // Nama file PDF
            $fileName = 'Kartu_UTS_' . $mahasiswa->nama . '.pdf';
            // Generate PDF
            $pdf = PDF::loadView('students.jadwal-uts.kartu', compact('mahasiswa', 'jadwalUts', 'activeTA','logoBase64','ttd'));
            return $pdf->stream($fileName);
        }


        public function cetakUas()
        {
             $settings = Setting::first();
            // Ambil data mahasiswa yang sedang login
            $mahasiswa = auth()->guard('mahasiswa')->user();
            $semester = $mahasiswa->semester;
            $prodi = $mahasiswa->jurusan_id;

            // Ambil Tahun Akademik Aktif
            $activeTA = TahunAkademik::where('status_ta', 1)->first();

            if (!$activeTA) {
                return back()->with('error', 'Tahun Akademik Aktif tidak ditemukan.');
            }
             $logoBase64 = null;
            if ($settings && $settings->logo) {
                $logoPath = public_path('storage/' . $settings->logo);
                if (file_exists($logoPath)) {
                    $logoBase64 = base64_encode(file_get_contents($logoPath));
                }
            }
             $ttd = null;
            if ($mahasiswa && $mahasiswa->programStudi->ttd) {
                $logoPath = public_path('storage/' . $mahasiswa->programStudi->ttd);
                if (file_exists($logoPath)) {
                    $ttd = base64_encode(file_get_contents($logoPath));
                }
            }
             // Query awal dengan eager loading
             // Query awal dengan eager loading
            $jadwalUas = Jadwaluas::with(['programStudi', 'mataKuliah', 'ruangan'])
            ->where('ta_id', $activeTA->ta_id)
              ->where(function ($query) {
                $jenisKelas = Auth::guard('mahasiswa')->user()->kelas;
                if ($jenisKelas === 'pagi') {
                    $query->where('jenis_kelas', 'reguler');
                } elseif ($jenisKelas === 'karyawan') {
                    $query->where('jenis_kelas', 'karyawan');
                }
            })
            ->whereHas('mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester);
            })
            ->whereHas('programStudi', function ($query) {
                $jurusanId = Auth::guard('mahasiswa')->user()->jurusan_id;
                $query->where('jurusan_id', $jurusanId);
            })
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

            // Nama file PDF
            $fileName = 'Kartu_UAS_' . $mahasiswa->nama . '.pdf';

            // Generate PDF
            $pdf = PDF::loadView('students.jadwal-uas.kartu', compact('mahasiswa', 'jadwalUas', 'activeTA','logoBase64','ttd'));
            return $pdf->stream($fileName);
        }


        public function cetakUap()
        {
            $settings = Setting::first();
            $mahasiswa = auth()->guard('mahasiswa')->user();
            $semester = $mahasiswa->semester;
            $prodi = $mahasiswa->jurusan_id;

            // Ambil Tahun Akademik Aktif
            $activeTA = TahunAkademik::where('status_ta', 1)->first();

            if (!$activeTA) {
                return back()->with('error', 'Tahun Akademik Aktif tidak ditemukan.');
            }

            // Ambil logo base64 jika ada
            $logoBase64 = null;
            if ($settings && $settings->logo) {
                $logoPath = public_path('storage/' . $settings->logo);
                if (file_exists($logoPath)) {
                    $logoBase64 = base64_encode(file_get_contents($logoPath));
                }
            }

            // Ambil ttd base64 jika ada
            $ttd = null;
            if ($mahasiswa && $mahasiswa->programStudi && $mahasiswa->programStudi->ttd) {
                $ttdPath = public_path('storage/' . $mahasiswa->programStudi->ttd);
                if (file_exists($ttdPath)) {
                    $ttd = base64_encode(file_get_contents($ttdPath));
                }
            }

            // Ambil jadwal UAP sesuai prodi dan semester mahasiswa
            $jadwalUap = Jadwaluap::with(['programStudi'])
                ->where('ta_id', $activeTA->ta_id)
                ->whereHas('programStudi', function ($query) use ($prodi) {
                    $query->where('jurusan_id', $prodi);
                })
                ->orderBy('tanggal')
                ->orderBy('jam_mulai')
                ->get();

            if ($jadwalUap->isEmpty()) {
                return back()->with('error', 'Jadwal UAP tidak tersedia.');
            }

            $fileName = 'Kartu_UAP_' . $mahasiswa->nama . '.pdf';
            $pdf = PDF::loadView('students.jadwal-uap.kartu', compact('mahasiswa', 'jadwalUap', 'activeTA', 'logoBase64', 'ttd'));
            return $pdf->download($fileName);
        }

}