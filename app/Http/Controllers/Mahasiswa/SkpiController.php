<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Models\Ppsm;
use App\Models\Setting;
use App\Models\Pkm_program;
use App\Models\Sertifikasi;
use App\Models\P2mw_program;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use App\Models\KegiatanTambahan;
use App\Models\PenguasaanBahasa;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use RealRashid\SweetAlert\Facades\Alert;
use PDF;

class SkpiController extends Controller
{
    public function index()
    {
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $settings = Setting::first();
        // Ambil data mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();
         $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        return view('students.skpi.index', compact('settings', 'mahasiswa', 'semester', 'prodi','ta'));
    }

    public function sertifikasi(Request $r)
    {
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $settings = Setting::first();
        // Ambil data mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();
         $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        $query = Sertifikasi::where('mahasiswa_id', Auth::guard('mahasiswa')->user()->mahasiswa_id);

        if ($r->filled('status')) {
            $query->where('status_validasi', $r->status);
        }

        $sertifikasi = $query->latest()->paginate(10);

        return view('students.skpi.sertifikasi.index', compact ('settings', 'mahasiswa', 'semester', 'prodi','ta','sertifikasi'));
    }
    public function store_sertifikasi(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $data = $request->validate([
            'nama_kegiatan'     => 'required|string|max:255',
            'penyelenggara'     => 'required|string|max:255',
            'tingkat_kegiatan'  => 'required|in:Lokal,Regional,Nasional,Internasional',
            'prestasi'          => 'nullable|string|max:255',
            'tanggal'           => 'required|date',
            'jenis_sertifikat'  => 'required',
            'file_sertifikat'   => 'required|url|max:2048',
            'dokumen_pendukung'   => 'required|url|max:2048',
        ]);

        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;      // atau $request->user()->id
        $data['status_validasi'] = 'Menunggu';        // default
        $data['bobot']           = 0;                 // belum diisi validator
        Sertifikasi::create($data);
        Alert::success('Sertifikasi berhasil ditambahkan')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.sertifikasi');
    }
    public function edit_sertifikasi($id)
    {
        $settings = Setting::first();
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        $sertifikasi = Sertifikasi::findOrFail($id);
        return view('students.skpi.sertifikasi.edit', compact ('settings', 'mahasiswa', 'semester', 'prodi','ta','sertifikasi'));
    }
    public function update_sertifikasi(Request $request, $id)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $data = $request->validate([
            'nama_kegiatan'     => 'required|string|max:255',
            'penyelenggara'     => 'required|string|max:255',
            'tingkat_kegiatan'  => 'required|in:Lokal,Regional,Nasional,Internasional',
            'prestasi'          => 'nullable|string|max:255',
            'tanggal'           => 'required|date',
            'jenis_sertifikat'  => 'required',
            'file_sertifikat'   => 'required|url|max:2048',
            'dokumen_pendukung'   => 'required|url|max:2048',
        ]);

        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;      // atau $request->user()->id
        $data['status_validasi'] = 'Menunggu';        // default
        $data['bobot']           = 0;                 // belum diisi validator
        Sertifikasi::where('id', $id)->update($data);
        Alert::success('Sertifikasi berhasil diubah')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.sertifikasi');
    }
    public function destroy_sertifikasi($id)
    {
        $sertifikasi = Sertifikasi::findOrFail($id);
        $sertifikasi->delete();
        Alert::success('Sertifikasi berhasil dihapus')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.sertifikasi');
    }

    public function bahasa(Request $r)
    {
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $settings = Setting::first();
        // Ambil data mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();
         $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        $query = PenguasaanBahasa::where('mahasiswa_id', Auth::guard('mahasiswa')->user()->mahasiswa_id);

        if ($r->filled('status')) {
            $query->where('status_validasi', $r->status);
        }

        $bahasa = $query->latest()->paginate(10);

        return view('students.skpi.bahasa.index', compact ('settings', 'mahasiswa', 'semester', 'prodi','ta','bahasa'));
    }
    public function store_bahasa(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $data = $request->validate([
            'nama_bahasa'       => 'required|string|max:255',
            'level'             => 'required|string|max:255',
            'penyelenggara'     => 'required|string|max:255',
            'tanggal_tes'       => 'required|date',
            'skor'              => 'nullable|integer',
            'file_sertifikat'   => 'required|url|max:2048',

        ]);

        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;      // atau $request->user()->id
        $data['status_validasi'] = 'Menunggu';        // default
        $data['bobot']           = 0;                 // belum diisi validator
        PenguasaanBahasa::create($data);
        Alert::success('Penguasaan Bahasa berhasil ditambahkan')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.bahasa');
    }
    public function edit_bahasa($id)
    {
        $settings = Setting::first();
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        $bahasa = PenguasaanBahasa::findOrFail($id);
        return view('students.skpi.bahasa.edit', compact ('settings', 'mahasiswa', 'semester', 'prodi','ta','bahasa'));
    }
    public function update_bahasa(Request $request, $id)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $data = $request->validate([
            'nama_bahasa'       => 'required|string|max:255',
            'level'             => 'required|string|max:255',
            'penyelenggara'     => 'required|string|max:255',
            'tanggal_tes'       => 'required|date',
            'skor'              => 'nullable|integer',
            'file_sertifikat'   => 'required|url|max:2048',
        ]);

        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;      // atau $request->user()->id
        $data['status_validasi'] = 'Menunggu';        // default
        $data['bobot']           = 0;                 // belum diisi validator
        PenguasaanBahasa::where('id', $id)->update($data);
        Alert::success('Penguasaan Bahasa Asing berhasil diubah')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.bahasa');
    }
    public function destroy_bahasa($id)
    {
        $bahasa = PenguasaanBahasa::findOrFail($id);
        $bahasa->delete();
        Alert::success('Penguasaan Bahasa Asing berhasil dihapus')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.bahasa');
    }
    public function wirausaha(Request $r)
    {
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $settings = Setting::first();
        // Ambil data mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();
         $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        $query = P2mw_program::where('mahasiswa_id', Auth::guard('mahasiswa')->user()->mahasiswa_id);

        if ($r->filled('status')) {
            $query->where('status_validasi', $r->status);
        }
        $wirausaha = $query->latest()->paginate(10);
        return view('students.skpi.wirausaha.index', compact ('settings', 'mahasiswa', 'semester', 'prodi','ta','wirausaha'));
    }
    public function store_wirausaha(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $data = $request->validate([
            'nama_usaha'        => 'required|string|max:255',
            'jenis_usaha'       => 'required|string|max:255',
            'penyelenggara'     => 'required|string|max:255',
            'status_pendanaan'  => 'required|in:Mandiri,Didanai',
            'tanggal'           => 'required|date',
            'file_lampiran'     => 'required|url|max:2048',
            'file_sertifikat'   => 'required|url|max:2048',
        ]);

        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;      // atau $request->user()->id
        $data['status_validasi'] = 'Menunggu';        // default
        $data['bobot']           = 0;                 // belum diisi validator
        P2mw_program::create($data);
        Alert::success('Wirausaha berhasil ditambahkan')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.wirausaha');
    }
    public function edit_wirausaha($id)
    {
        $settings = Setting::first();
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        $wirausaha = P2mw_program::findOrFail($id);
        return view('students.skpi.wirausaha.edit', compact ('settings', 'mahasiswa', 'semester', 'prodi','ta','wirausaha'));
    }
    public function update_wirausaha(Request $request, $id)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $data = $request->validate([
            'nama_usaha'        => 'required|string|max:255',
            'jenis_usaha'       => 'required|string|max:255',
            'penyelenggara'     => 'required|string|max:255',
            'status_pendanaan'  => 'required|in:Mandiri,Didanai',
            'tanggal'           => 'required|date',
            'file_lampiran'     => 'required|url|max:2048',
            'file_sertifikat'   => 'required|url|max:2048',
        ]);

        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;      // atau $request->user()->id
        $data['status_validasi'] = 'Menunggu';        // default
        $data['bobot']           = 0;                 // belum diisi validator
        P2mw_program::where('id', $id)->update($data);
        Alert::success('Wirausaha berhasil diubah')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.wirausaha');
    }
    public function destroy_wirausaha($id)
    {
        $wirausaha = P2mw_program::findOrFail($id);
        $wirausaha->delete();
        Alert::success('Wirausaha berhasil dihapus')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.wirausaha');
    }
    public function pkm(Request $r)
    {
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $settings = Setting::first();
        // Ambil data mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();
         $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        $query = Pkm_program::where('mahasiswa_id', Auth::guard('mahasiswa')->user()->mahasiswa_id);

        if ($r->filled('status')) {
            $query->where('status_validasi', $r->status);
        }
        $pkm = $query->latest()->paginate(10);
        return view('students.skpi.pkm.index', compact ('settings', 'mahasiswa', 'semester', 'prodi','ta','pkm'));
    }
    public function store_pkm(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $data = $request->validate([
            'judul_kegiatan'    => 'required|string|max:255',
            'jenis_pkm'         => 'required|string|max:255',
            'penyelenggara'     => 'required|string|max:255',
            'tanggal'           => 'required|date',
            'prestasi'          => 'nullable|string|max:255',
            'file_laporan'      => 'required|url|max:2048',
            'file_lampiran'     => 'required|url|max:2048',
        ]);

        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;      // atau $request->user()->id
        $data['status_validasi'] = 'Menunggu';        // default
        $data['bobot']           = 0;                 // belum diisi validator
        Pkm_program::create($data);
        Alert::success('PKM berhasil ditambahkan')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.pkm');
    }
    public function edit_pkm($id)
    {
        $settings = Setting::first();
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        $pkm = Pkm_program::findOrFail($id);
        return view('students.skpi.pkm.edit', compact ('settings', 'mahasiswa', 'semester', 'prodi','ta','pkm'));
    }
    public function update_pkm(Request $request, $id)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $data = $request->validate([
            'judul_kegiatan'    => 'required|string|max:255',
            'jenis_pkm'         => 'required|string|max:255',
            'penyelenggara'     => 'required|string|max:255',
            'tanggal'           => 'required|date',
            'prestasi'          => 'nullable|string|max:255',
            'file_laporan'      => 'required|url|max:2048',
            'file_lampiran'     => 'required|url|max:2048',
        ]);

        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;      // atau $request->user()->id
        $data['status_validasi'] = 'Menunggu';        // default
        $data['bobot']           = 0;                 // belum diisi validator
        Pkm_program::where('id', $id)->update($data);
        Alert::success('Pkm berhasil diubah')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.pkm');
    }
    public function destroy_pkm($id)
    {
        $pkm = P2mw_program::findOrFail($id);
        $pkm->delete();
        Alert::success('PKM berhasil dihapus')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.pkm');
    }
    public function ppsm(Request $r)
    {
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $settings = Setting::first();
        // Ambil data mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();
         $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        $query = Ppsm::where('mahasiswa_id', Auth::guard('mahasiswa')->user()->mahasiswa_id);

        if ($r->filled('status')) {
            $query->where('status_validasi', $r->status);
        }
        $ppsm = $query->latest()->paginate(10);
        return view('students.skpi.ppsm.index', compact ('settings', 'mahasiswa', 'semester', 'prodi','ta','ppsm'));
    }
    public function store_ppsm(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $data = $request->validate([
            'nama_kegiatan'   => 'required|string|max:255',
            'tahun_kegiatan'  => 'required|integer|min:1900|max:' . date('Y'),
            'keterangan'      => 'nullable|string|max:255',
            'file_sertifikat' => 'required|url|max:2048',
            'file_lampiran'   => 'required|url|max:2048',
        ]);

        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;      // atau $request->user()->id
        $data['status_validasi'] = 'Menunggu';        // default
        $data['bobot']           = 0;                 // belum diisi validator
        Ppsm::create($data);
        Alert::success('PPSM berhasil ditambahkan')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.ppsm');
    }
    public function edit_ppsm($id)
    {
        $settings = Setting::first();
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        $pkm = Ppsm::findOrFail($id);
        return view('students.skpi.wirausaha.edit', compact ('settings', 'mahasiswa', 'semester', 'prodi','ta','pkm'));
    }
    public function update_ppsm(Request $request, $id)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $data = $request->validate([
            'nama_kegiatan'   => 'required|string|max:255',
            'tahun_kegiatan'  => 'required|integer|min:1900|max:' . date('Y'),
            'keterangan'      => 'nullable|string|max:255',
            'file_sertifikat' => 'required|url|max:2048',
            'file_lampiran'   => 'required|url|max:2048',
        ]);

        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;      // atau $request->user()->id
        $data['status_validasi'] = 'Menunggu';        // default
        $data['bobot']           = 0;                 // belum diisi validator
        Ppsm::where('id', $id)->update($data);
        Alert::success('PPSM berhasil diubah')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.ppsm');
    }
    public function destroy_ppsm($id)
    {
        $pkm = Ppsm::findOrFail($id);
        $pkm->delete();
        Alert::success('PPSM berhasil dihapus')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.ppsm');
    }
    public function tambahan(Request $r)
    {
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $settings = Setting::first();
        // Ambil data mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();
         $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        $query = KegiatanTambahan::where('mahasiswa_id', Auth::guard('mahasiswa')->user()->mahasiswa_id);
        if ($r->filled('kategori')) {
            $query->where('kategori', $r->kategori);
        }
        if ($r->filled('bentuk_kegiatan')) {
            $query->where('bentuk_kegiatan', $r->bentuk_kegiatan);
        }
        if ($r->filled('status')) {
            $query->where('status_validasi', $r->status);
        }
        $tambahan = $query->latest()->paginate(10);
        return view('students.skpi.kegiatan-tambahan.index', compact ('settings', 'mahasiswa', 'semester', 'prodi','ta','tambahan'));
    }
    public function store_tambahan(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $data = $request->validate([
            'kategori'         => 'required|string|max:255',
            'nama_kegiatan'    => 'required|string|max:255',
            'bentuk_kegiatan'  => 'required|string|max:255',
            'tingkat'          => 'required|in:Lokal,Regional,Nasional,Internasional',
            'penyelenggara'    => 'required|string|max:255',
            'peran'            => 'required|string|max:255',
            'tanggal'          => 'required|date',
            'file_sertifikat'  => 'required|url|max:2048',
            'file_lampiran'    => 'required|url|max:2048',
        ]);
        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;      // atau $request->user()->id
        $data['status_validasi'] = 'Menunggu';        // default
        $data['bobot']           = 0;                 // belum diisi validator
        KegiatanTambahan::create($data);
        Alert::success('Kegiatan berhasil ditambahkan')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.tambahan');
    }
    public function edit_tambahan($id)
    {
        $settings = Setting::first();
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });
        $tambahan = KegiatanTambahan::findOrFail($id);
        return view('students.skpi.kegiatan-tambahan.edit', compact ('settings', 'mahasiswa', 'semester', 'prodi','ta','tambahan'));
    }
    public function update_tambahan(Request $request, $id)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $data = $request->validate([
            'kategori'         => 'required|string|max:255',
            'nama_kegiatan'    => 'required|string|max:255',
            'bentuk_kegiatan'  => 'required|string|max:255',
            'tingkat'          => 'required|in:Lokal,Regional,Nasional,Internasional',
            'penyelenggara'    => 'required|string|max:255',
            'peran'            => 'required|string|max:255',
            'tanggal'          => 'required|date',
            'file_sertifikat'  => 'required|url|max:2048',
            'file_lampiran'    => 'required|url|max:2048',
        ]);

        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;      // atau $request->user()->id
        $data['status_validasi'] = 'Menunggu';        // default
        $data['bobot']           = 0;                 // belum diisi validator
        KegiatanTambahan::where('id', $id)->update($data);
        Alert::success('PPSM berhasil diubah')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.tambahan');
    }
    public function destroy_tambahan($id)
    {
        $pkm = KegiatanTambahan::findOrFail($id);
        $pkm->delete();
        Alert::success('Kegiatan Tambahan berhasil dihapus')
            ->autoclose(3000);
        return redirect()->route('mahasiswa.skpi.tambahan');
    }

    public function cetak()
    {
        $settings = Setting::first();
        $mahasiswa = Auth::guard('mahasiswa')->user();

        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $ta = TahunAkademik::where('status_ta', 1)->first();
        $logoBase64 = null;

        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/' . $settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }

        // Kegiatan Wajib
        $sertifikasi = Sertifikasi::where('mahasiswa_id', $mahasiswa->mahasiswa_id)->where('status_validasi', 'Disetujui')->get();
        $ppsm = Ppsm::where('mahasiswa_id', $mahasiswa->mahasiswa_id)->where('status_validasi', 'Disetujui')->get();
        $bahasa = PenguasaanBahasa::where('mahasiswa_id', $mahasiswa->mahasiswa_id)->where('status_validasi', 'Disetujui')->get();
        $p2mw = P2mw_program::where('mahasiswa_id', $mahasiswa->mahasiswa_id)->where('status_validasi', 'Disetujui')->get();
        $pkm = Pkm_program::where('mahasiswa_id', $mahasiswa->mahasiswa_id)->where('status_validasi', 'Disetujui')->get();

        // Kegiatan Tambahan
        $tambahan = KegiatanTambahan::where('mahasiswa_id', $mahasiswa->mahasiswa_id)->get();

        // Hitung total skor
        $totalWajib = $sertifikasi->sum('bobot') + $ppsm->sum('bobot') + $bahasa->sum('bobot') + $p2mw->sum('bobot') + $pkm->sum('bobot');
        $totalTambahan = $tambahan->sum('bobot');
        $totalSkor = $totalWajib + $totalTambahan;

        $pdf = Pdf::loadView('students.skpi.cetak-skpi', compact(
            'mahasiswa', 'ta', 'logoBase64',
            'sertifikasi', 'ppsm', 'bahasa', 'p2mw', 'pkm',
            'tambahan', 'totalWajib', 'totalTambahan', 'totalSkor'
        ))->setPaper('A4', 'portrait');

        return $pdf->stream('laporan-skpi-' . $mahasiswa->nama . '.pdf');
    }

}
