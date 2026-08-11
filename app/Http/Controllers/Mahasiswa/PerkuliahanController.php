<?php

namespace App\Http\Controllers\Mahasiswa;

// --- Framework & Facades ---
use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\JadwalUap;
// --- Controllers ---
use App\Models\JadwalUas;
// --- Models ---
use App\Models\JadwalUts;
use App\Models\ProgramStudi;
use App\Models\Setting;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
// --- Third-Party Libraries ---
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PerkuliahanController extends Controller
{
    /**
     * Cache TahunAkademik aktif agar tidak query berulang
     */
    private function getActiveTA()
    {
        return Cache::remember('active_tahun_akademik', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first();
        });
    }

    /**
     * Ambil data mahasiswa yang sedang login (sekali saja)
     */
    private function getMahasiswa()
    {
        return Auth::guard('mahasiswa')->user();
    }

    public function index(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();
        $semester = $mahasiswa->semester;
        $prodi = $mahasiswa->jurusan_id;
        $kelas = $mahasiswa->kelas;

        $activeTA = $this->getActiveTA();

        // Pastikan ada tahun ajaran aktif
        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
        }

        // Query dengan eager loading - Auth::guard() hanya dipanggil sekali di atas
        $jadwals = Jadwal::with([
            'kurikulum.mataKuliah',
            'kurikulum.dosenToMatakuliah.dosen',
            'programStudi',
            'ruangan',
        ])
            ->where('ta_id', $activeTA->ta_id)
            ->where(function ($query) use ($kelas) {
                if ($kelas === 'reguler') {
                    $query->where('jenis_kelas', 'reguler');
                } elseif ($kelas === 'karyawan') {
                    $query->where('jenis_kelas', 'karyawan');
                }
            })
            ->whereHas('kurikulum.mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester);
            })
            ->whereHas('programStudi', function ($query) use ($prodi) {
                $query->where('jurusan_id', $prodi);
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
                        return $dosen['jenis_dosen'] === 'teori';
                    })->unique('id')->values(),
                ];
            });

        activity_log('lihat_jadwal', 'Mahasiswa melihat jadwal teori');

        return view('mahasiswa.jadwal.index', compact('jadwals'));
    }

    public function jadwalPraktik(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();
        $semester = $mahasiswa->semester;
        $prodi = $mahasiswa->jurusan_id;
        $kelas = $mahasiswa->kelas;

        $activeTA = $this->getActiveTA();

        // Pastikan ada tahun ajaran aktif
        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
        }

        // Query dengan eager loading
        $jadwals = JadwalPraktik::with([
            'kurikulum.mataKuliah',
            'kurikulum.dosenToMatakuliah.dosen',
            'programStudi',
            'ruangan',
        ])
            ->where('ta_id', $activeTA->ta_id)
            ->where(function ($query) use ($kelas) {
                if ($kelas === 'reguler') {
                    $query->where('jenis_kelas', 'reguler');
                } elseif ($kelas === 'karyawan') {
                    $query->where('jenis_kelas', 'karyawan');
                }
            })
            ->whereHas('kurikulum.mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester);
            })
            ->whereHas('programStudi', function ($query) use ($prodi) {
                $query->where('jurusan_id', $prodi);
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
                        return $dosen['jenis_dosen'] === 'praktik';
                    })->unique('id')->values(),
                ];
            });

        activity_log('lihat_jadwal_praktik', 'Mahasiswa melihat jadwal praktik');

        return view('mahasiswa.jadwal.praktik', compact('jadwals'));
    }

    public function jadwalUts(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();
        $semester = $mahasiswa->semester;
        $prodi = $mahasiswa->jurusan_id;
        $kelas = $mahasiswa->kelas;

        $activeTA = $this->getActiveTA();

        // Pastikan ada tahun ajaran aktif
        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
        }

        // Query dengan eager loading
        $jadwalUts = JadwalUts::with(['programStudi', 'mataKuliah', 'ruangan'])
            ->where('ta_id', $activeTA->ta_id)
            ->where(function ($query) use ($kelas) {
                if ($kelas === 'pagi') {
                    $query->where('jenis_kelas', 'reguler');
                } elseif ($kelas === 'karyawan') {
                    $query->where('jenis_kelas', 'karyawan');
                }
            })
            ->whereHas('mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester);
            })
            ->whereHas('programStudi', function ($query) use ($prodi) {
                $query->where('jurusan_id', $prodi);
            })
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        activity_log('lihat_jadwal_uts', 'Mahasiswa melihat jadwal UTS');

        return view('mahasiswa.jadwal-uts.index', compact('jadwalUts'));
    }

    public function jadwalUas(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();
        $semester = $mahasiswa->semester;
        $prodi = $mahasiswa->jurusan_id;
        $kelas = $mahasiswa->kelas;

        $activeTA = $this->getActiveTA();

        // Pastikan ada tahun ajaran aktif
        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
        }

        // Query dengan eager loading
        $jadwalUas = JadwalUas::with(['programStudi', 'mataKuliah', 'ruangan'])
            ->where('ta_id', $activeTA->ta_id)
            ->where(function ($query) use ($kelas) {
                if ($kelas === 'pagi') {
                    $query->where('jenis_kelas', 'reguler');
                } elseif ($kelas === 'karyawan') {
                    $query->where('jenis_kelas', 'karyawan');
                }
            })
            ->whereHas('mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester);
            })
            ->whereHas('programStudi', function ($query) use ($prodi) {
                $query->where('jurusan_id', $prodi);
            })
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        activity_log('lihat_jadwal_uas', 'Mahasiswa melihat jadwal UAS');

        return view('mahasiswa.jadwal-uas.index', compact('jadwalUas'));
    }

    public function jadwalUap()
    {
        $mahasiswa = $this->getMahasiswa();
        $isSemester6 = ((int) $mahasiswa->semester === 6);

        if (! $isSemester6) {
            return view('mahasiswa.jadwal-uap.index', [
                'jadwal' => collect(),
                'accessDenied' => true,
            ])->with('error', 'Hanya mahasiswa semester 6 yang dapat mengakses jadwal UAP.');
        }

        $tahunAjaran = $this->getActiveTA();

        if (! $tahunAjaran) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran yang aktif.');
        }

        // Ambil semua program studi
        $programStudi = ProgramStudi::all();

        if ($programStudi->isEmpty()) {
            return redirect()->back()->with('error', 'Data program studi tidak tersedia.');
        }

        // Ambil semua jadwal UAP
        $jadwal = JadwalUap::where('ta_id', $tahunAjaran->ta_id)->get();

        activity_log('lihat_jadwal_uap', 'Mahasiswa melihat jadwal UAP');

        return view('mahasiswa.jadwal-uap.index', compact('jadwal'));
    }

    /**
     * Helper: Build query jadwal UTS untuk reuse antara tampil dan cetak
     */
    private function buildJadwalUtsQuery($activeTA, $semester, $kelas, $prodi)
    {
        return JadwalUts::with(['programStudi', 'mataKuliah', 'ruangan'])
            ->where('ta_id', $activeTA->ta_id)
            ->where(function ($query) use ($kelas) {
                if ($kelas === 'pagi') {
                    $query->where('jenis_kelas', 'reguler');
                } elseif ($kelas === 'karyawan') {
                    $query->where('jenis_kelas', 'karyawan');
                }
            })
            ->whereHas('mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester);
            })
            ->whereHas('programStudi', function ($query) use ($prodi) {
                $query->where('jurusan_id', $prodi);
            })
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();
    }

    /**
     * Helper: Build query jadwal UAS untuk reuse antara tampil dan cetak
     */
    private function buildJadwalUasQuery($activeTA, $semester, $kelas, $prodi)
    {
        return JadwalUas::with(['programStudi', 'mataKuliah', 'ruangan'])
            ->where('ta_id', $activeTA->ta_id)
            ->where(function ($query) use ($kelas) {
                if ($kelas === 'pagi') {
                    $query->where('jenis_kelas', 'reguler');
                } elseif ($kelas === 'karyawan') {
                    $query->where('jenis_kelas', 'karyawan');
                }
            })
            ->whereHas('mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester);
            })
            ->whereHas('programStudi', function ($query) use ($prodi) {
                $query->where('jurusan_id', $prodi);
            })
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();
    }

    /**
     * Helper: Ambil logo dan ttd base64 untuk cetak PDF
     */
    private function getLogoAndTtd($mahasiswa)
    {
        $settings = Cache::remember('app_settings', 3600, function () {
            return Setting::first();
        });

        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/'.$settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }

        $ttd = null;
        if ($mahasiswa && $mahasiswa->programStudi && $mahasiswa->programStudi->ttd) {
            $ttdPath = public_path('storage/'.$mahasiswa->programStudi->ttd);
            if (file_exists($ttdPath)) {
                $ttd = base64_encode(file_get_contents($ttdPath));
            }
        }

        return [$logoBase64, $ttd];
    }

    public function cetakUts()
    {
        $mahasiswa = $this->getMahasiswa();

        $semester = $mahasiswa->semester;
        $prodi = $mahasiswa->jurusan_id;
        $kelas = $mahasiswa->kelas;

        $activeTA = $this->getActiveTA();
        if (! $activeTA) {
            return back()->with('error', 'Tahun Akademik Aktif tidak ditemukan.');
        }

        [$logoBase64, $ttd] = $this->getLogoAndTtd($mahasiswa);

        // Reuse query builder
        $jadwalUts = $this->buildJadwalUtsQuery($activeTA, $semester, $kelas, $prodi);

        // QR Code Verifikasi Keabsahan Dokumen
        $payload = json_encode(['m' => $mahasiswa->mahasiswa_id, 't' => $activeTA->ta_id, 'type' => 'UTS']);
        $token = base64_encode(Crypt::encryptString($payload));
        $verifyUrl = route('verify.ujian', ['token' => $token]);

        // --- QR CODE GENERATION (simpan ke file temp agar DOMPDF bisa render) ---
        $qrCodeSvg = (string) QrCode::size(200)->margin(1)->generate($verifyUrl);
        $qrTempDir = storage_path('app/temp');
        if (! file_exists($qrTempDir)) {
            mkdir($qrTempDir, 0755, true);
        }
        $qrFilePath = $qrTempDir.'/qr_'.md5($verifyUrl).'.svg';
        file_put_contents($qrFilePath, $qrCodeSvg);
        // ------------------------------

        $fileName = 'Kartu_UTS_'.$mahasiswa->nama.'.pdf';
        $pdf = PDF::loadView('mahasiswa.jadwal-uts.kartu', compact('mahasiswa', 'jadwalUts', 'activeTA', 'logoBase64', 'ttd', 'qrFilePath'));

        activity_log('cetak_kartu_ujian', 'Mahasiswa mencetak Kartu Ujian UTS');

        return $pdf->download($fileName);
    }

    public function cetakUas()
    {
        $mahasiswa = $this->getMahasiswa();

        $semester = $mahasiswa->semester;
        $prodi = $mahasiswa->jurusan_id;
        $kelas = $mahasiswa->kelas;

        $activeTA = $this->getActiveTA();
        if (! $activeTA) {
            return back()->with('error', 'Tahun Akademik Aktif tidak ditemukan.');
        }

        [$logoBase64, $ttd] = $this->getLogoAndTtd($mahasiswa);

        // Reuse query builder
        $jadwalUas = $this->buildJadwalUasQuery($activeTA, $semester, $kelas, $prodi);

        // QR Code Verifikasi Keabsahan Dokumen
        $payload = json_encode(['m' => $mahasiswa->mahasiswa_id, 't' => $activeTA->ta_id, 'type' => 'UAS']);
        $token = base64_encode(Crypt::encryptString($payload));
        $verifyUrl = route('verify.ujian', ['token' => $token]);

        // --- QR CODE GENERATION (simpan ke file temp agar DOMPDF bisa render) ---
        $qrCodeSvg = (string) QrCode::size(200)->margin(1)->generate($verifyUrl);
        $qrTempDir = storage_path('app/temp');
        if (! file_exists($qrTempDir)) {
            mkdir($qrTempDir, 0755, true);
        }
        $qrFilePath = $qrTempDir.'/qr_'.md5($verifyUrl).'.svg';
        file_put_contents($qrFilePath, $qrCodeSvg);
        // ------------------------------

        $fileName = 'Kartu_UAS_'.$mahasiswa->nama.'.pdf';
        $pdf = PDF::loadView('mahasiswa.jadwal-uas.kartu', compact('mahasiswa', 'jadwalUas', 'activeTA', 'logoBase64', 'ttd', 'qrFilePath'));

        activity_log('cetak_kartu_ujian', 'Mahasiswa mencetak Kartu Ujian UAS');

        return $pdf->download($fileName);
    }

    public function cetakUap()
    {
        $mahasiswa = $this->getMahasiswa();

        if ((int) $mahasiswa->semester !== 6) {
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Hanya mahasiswa semester 6 yang dapat mencetak kartu UAP.');
        }

        $semester = $mahasiswa->semester;
        $prodi = $mahasiswa->jurusan_id;

        $activeTA = $this->getActiveTA();
        if (! $activeTA) {
            return back()->with('error', 'Tahun Akademik Aktif tidak ditemukan.');
        }

        [$logoBase64, $ttd] = $this->getLogoAndTtd($mahasiswa);

        // Ambil jadwal UAP sesuai prodi dan semester mahasiswa
        $jadwalUap = JadwalUap::with(['programStudi'])
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

        // QR Code Verifikasi Keabsahan Dokumen
        $payload = json_encode(['m' => $mahasiswa->mahasiswa_id, 't' => $activeTA->ta_id, 'type' => 'UAP']);
        $token = base64_encode(Crypt::encryptString($payload));
        $verifyUrl = route('verify.ujian', ['token' => $token]);

        // --- QR CODE GENERATION (simpan ke file temp agar DOMPDF bisa render) ---
        $qrCodeSvg = (string) QrCode::size(200)->margin(1)->generate($verifyUrl);
        $qrTempDir = storage_path('app/temp');
        if (! file_exists($qrTempDir)) {
            mkdir($qrTempDir, 0755, true);
        }
        $qrFilePath = $qrTempDir.'/qr_'.md5($verifyUrl).'.svg';
        file_put_contents($qrFilePath, $qrCodeSvg);
        // ------------------------------

        $fileName = 'Kartu_UAP_'.$mahasiswa->nama.'.pdf';
        $pdf = PDF::loadView('mahasiswa.jadwal-uap.kartu', compact('mahasiswa', 'jadwalUap', 'activeTA', 'logoBase64', 'ttd', 'qrFilePath'));

        activity_log('cetak_kartu_ujian', 'Mahasiswa mencetak Kartu Ujian UAP');

        return $pdf->download($fileName);
    }
}
