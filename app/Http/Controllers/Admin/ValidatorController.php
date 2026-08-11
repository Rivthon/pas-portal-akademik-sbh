<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KegiatanTambahan;
use App\Models\P2mwProgram;
use App\Models\PenguasaanBahasa;
use App\Models\PkmProgram;
use App\Models\Ppsm;
use App\Models\Sertifikasi;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ValidatorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:skpi-list', ['only' => ['index']]);
        $this->middleware('permission:skpi-sertifikasi-list', ['only' => [
            'sertifikasi', 'sertifikasiDisetujui', 'sertifikasiDitolak', 'sertifikasiDitinjau', 'sertifikasiMenunggu',
        ]]);
        $this->middleware('permission:skpi-sertifikasi-edit', ['only' => ['updateCatatan']]);
        $this->middleware('permission:skpi-wirausaha-list', ['only' => [
            'wirausaha', 'wirausahaDisetujui', 'wirausahaDitolak', 'wirausahaDitinjau', 'wirausahaMenunggu',
        ]]);
        $this->middleware('permission:skpi-wirausaha-edit', ['only' => ['updateCatatanWirausaha']]);
        $this->middleware('permission:skpi-ppsm-list', ['only' => [
            'ppsm', 'ppsmDisetujui', 'ppsmDitolak', 'ppsmDitinjau', 'ppsmMenunggu',
        ]]);
        $this->middleware('permission:skpi-ppsm-edit', ['only' => ['updateCatatanPpsm']]);
        $this->middleware('permission:skpi-pkm-list', ['only' => [
            'pkm', 'pkmDisetujui', 'pkmDitolak', 'pkmDitinjau', 'pkmMenunggu',
        ]]);
        $this->middleware('permission:skpi-pkm-edit', ['only' => ['updateCatatanPkm']]);
        $this->middleware('permission:skpi-tambahan-list', ['only' => [
            'tambahan', 'tambahanDisetujui', 'tambahanDitolak', 'tambahanDitinjau', 'tambahanMenunggu',
        ]]);
        $this->middleware('permission:skpi-tambahan-edit', ['only' => ['updateCatatanTambahan']]);
        $this->middleware('permission:skpi-bahasa-list', ['only' => [
            'bahasa', 'bahasaDisetujui', 'bahasaDitolak', 'bahasaDitinjau', 'bahasaMenunggu',
        ]]);
        $this->middleware('permission:skpi-bahasa-edit', ['only' => ['updateCatatanBahasa']]);
    }

    public function index()
    {
        activity_log('akses_validasi_skpi', 'Admin mengakses halaman validasi SKPI');

        return view('admin.validator.index');
    }

    public function sertifikasi(Request $request)
    {
        $query = Sertifikasi::query();

        $count_ditinjau = Sertifikasi::where('status_validasi', 'ditinjau')->count();
        $count_disetujui = Sertifikasi::where('status_validasi', 'disetujui')->count();
        $count_ditolak = Sertifikasi::where('status_validasi', 'ditolak')->count();
        $count_menunggu = Sertifikasi::where('status_validasi', 'menunggu')->count();

        // Kirim ke view jika diperlukan
        return view('admin.validator.sertifikasi.index', [
            'count_ditinjau' => $count_ditinjau,
            'count_disetujui' => $count_disetujui,
            'count_ditolak' => $count_ditolak,
            'count_menunggu' => $count_menunggu,
        ]);

        return view('admin.validator.sertifikasi.index');
    }

    public function sertifikasiDisetujui(Request $request)
    {
        $search = $request->input('search');
        $query = Sertifikasi::where('status_validasi', 'disetujui');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.sertifikasi.approval', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function sertifikasiDitolak(Request $request)
    {
        $search = $request->input('search');
        $query = Sertifikasi::where('status_validasi', 'ditolak');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.sertifikasi.rejected', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function sertifikasiDitinjau(Request $request)
    {
        $search = $request->input('search');
        $query = Sertifikasi::where('status_validasi', 'ditinjau');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.sertifikasi.pending', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function sertifikasiMenunggu(Request $request)
    {
        $search = $request->input('search');
        $query = Sertifikasi::where('status_validasi', 'menunggu');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.sertifikasi.waiting', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function updateCatatan(Request $request, $id)
    {
        $request->validate([
            'catatan_validator' => 'nullable|string',
            'status_validasi' => 'required|in:menunggu,disetujui,ditolak,ditinjau',
        ]);

        $sertifikasi = Sertifikasi::findOrFail($id);
        $sertifikasi->catatan_validator = $request->catatan_validator;
        $sertifikasi->status_validasi = $request->status_validasi;
        $sertifikasi->bobot = $request->bobot;

        $sertifikasi->save();
        activity_log('validasi_sertifikasi', 'Admin memvalidasi sertifikasi ID: '.$id.' status: '.$request->status_validasi);
        Alert::success('Status dan catatan berhasil diperbarui.');

        return redirect()->back()
            ->with('success', 'Status dan catatan berhasil diperbarui.')
            ->with('item_id', $id);

    }

    public function wirausaha(Request $request)
    {
        $query = P2mwProgram::query();

        $count_ditinjau = P2mwProgram::where('status_validasi', 'ditinjau')->count();
        $count_disetujui = P2mwProgram::where('status_validasi', 'disetujui')->count();
        $count_ditolak = P2mwProgram::where('status_validasi', 'ditolak')->count();
        $count_menunggu = P2mwProgram::where('status_validasi', 'menunggu')->count();

        return view('admin.validator.wirausaha.index', [
            'count_ditinjau' => $count_ditinjau,
            'count_disetujui' => $count_disetujui,
            'count_ditolak' => $count_ditolak,
            'count_menunggu' => $count_menunggu,
        ]);
    }

    public function wirausahaDisetujui(Request $request)
    {
        $search = $request->input('search');
        $query = P2mwProgram::where('status_validasi', 'disetujui');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_usaha', 'like', "%{$search}%")
                    ->orWhere('jenis_usaha', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.wirausaha.approval', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function wirausahaDitolak(Request $request)
    {
        $search = $request->input('search');
        $query = P2mwProgram::where('status_validasi', 'ditolak');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_usaha', 'like', "%{$search}%")
                    ->orWhere('jenis_usaha', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.wirausaha.rejected', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function wirausahaDitinjau(Request $request)
    {
        $search = $request->input('search');
        $query = P2mwProgram::where('status_validasi', 'ditinjau');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_usaha', 'like', "%{$search}%")
                    ->orWhere('jenis_usaha', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.wirausaha.pending', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function wirausahaMenunggu(Request $request)
    {
        $search = $request->input('search');
        $query = P2mwProgram::where('status_validasi', 'menunggu');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_usaha', 'like', "%{$search}%")
                    ->orWhere('jenis_usaha', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.wirausaha.waiting', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function updateCatatanWirausaha(Request $request, $id)
    {
        $request->validate([
            'catatan_validator' => 'nullable|string',
            'status_validasi' => 'required|in:menunggu,disetujui,ditolak,ditinjau',
        ]);

        $p2mw = P2mwProgram::findOrFail($id);
        $p2mw->catatan_validator = $request->catatan_validator;
        $p2mw->status_validasi = $request->status_validasi;
        $p2mw->bobot = $request->bobot;

        $p2mw->save();
        activity_log('validasi_wirausaha', 'Admin memvalidasi wirausaha ID: '.$id.' status: '.$request->status_validasi);
        Alert::success('Status dan catatan berhasil diperbarui.');

        return redirect()->back()
            ->with('success', 'Status dan catatan berhasil diperbarui.')
            ->with('item_id', $id);
    }

    public function ppsm(Request $request)
    {
        $query = Ppsm::query();

        $count_ditinjau = Ppsm::where('status_validasi', 'ditinjau')->count();
        $count_disetujui = Ppsm::where('status_validasi', 'disetujui')->count();
        $count_ditolak = Ppsm::where('status_validasi', 'ditolak')->count();
        $count_menunggu = Ppsm::where('status_validasi', 'menunggu')->count();

        return view('admin.validator.ppsm.index', [
            'count_ditinjau' => $count_ditinjau,
            'count_disetujui' => $count_disetujui,
            'count_ditolak' => $count_ditolak,
            'count_menunggu' => $count_menunggu,
        ]);
    }

    public function ppsmDisetujui(Request $request)
    {
        $search = $request->input('search');
        $query = Ppsm::where('status_validasi', 'disetujui');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('tahun_kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.ppsm.approval', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function ppsmDitolak(Request $request)
    {
        $search = $request->input('search');
        $query = Ppsm::where('status_validasi', 'ditolak');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('tahun_kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.ppsm.rejected', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function ppsmDitinjau(Request $request)
    {
        $search = $request->input('search');
        $query = Ppsm::where('status_validasi', 'ditinjau');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('tahun_kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.ppsm.pending', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function ppsmMenunggu(Request $request)
    {
        $search = $request->input('search');
        $query = Ppsm::where('status_validasi', 'menunggu');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('tahun_kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.ppsm.waiting', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function updateCatatanPpsm(Request $request, $id)
    {
        $request->validate([
            'catatan_validator' => 'nullable|string',
            'status_validasi' => 'required|in:menunggu,disetujui,ditolak,ditinjau',
        ]);

        $ppsm = Ppsm::findOrFail($id);
        $ppsm->catatan_validator = $request->catatan_validator;
        $ppsm->status_validasi = $request->status_validasi;
        $ppsm->bobot = $request->bobot;

        $ppsm->save();
        activity_log('validasi_ppsm', 'Admin memvalidasi PPSM ID: '.$id.' status: '.$request->status_validasi);
        Alert::success('Status dan catatan berhasil diperbarui.');

        return redirect()->back()
            ->with('success', 'Status dan catatan berhasil diperbarui.')
            ->with('item_id', $id);
    }

    public function pkm(Request $request)
    {
        $query = PkmProgram::query();

        $count_ditinjau = PkmProgram::where('status_validasi', 'ditinjau')->count();
        $count_disetujui = PkmProgram::where('status_validasi', 'disetujui')->count();
        $count_ditolak = PkmProgram::where('status_validasi', 'ditolak')->count();
        $count_menunggu = PkmProgram::where('status_validasi', 'menunggu')->count();

        return view('admin.validator.pkm.index', [
            'count_ditinjau' => $count_ditinjau,
            'count_disetujui' => $count_disetujui,
            'count_ditolak' => $count_ditolak,
            'count_menunggu' => $count_menunggu,
        ]);
    }

    public function pkmDisetujui(Request $request)
    {
        $search = $request->input('search');
        $query = PkmProgram::where('status_validasi', 'disetujui');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%{$search}%")
                    ->orWhere('jenis_pkm', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.pkm.approval', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function pkmDitolak(Request $request)
    {
        $search = $request->input('search');
        $query = PkmProgram::where('status_validasi', 'ditolak');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%{$search}%")
                    ->orWhere('jenis_pkm', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.pkm.rejected', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function pkmDitinjau(Request $request)
    {
        $search = $request->input('search');
        $query = PkmProgram::where('status_validasi', 'ditinjau');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%{$search}%")
                    ->orWhere('jenis_pkm', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.pkm.pending', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function pkmMenunggu(Request $request)
    {
        $search = $request->input('search');
        $query = PkmProgram::where('status_validasi', 'menunggu');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%{$search}%")
                    ->orWhere('jenis_pkm', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.pkm.waiting', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function updateCatatanPkm(Request $request, $id)
    {
        $request->validate([
            'catatan_validator' => 'nullable|string',
            'status_validasi' => 'required|in:menunggu,disetujui,ditolak,ditinjau',
        ]);

        $pkm = PkmProgram::findOrFail($id);
        $pkm->catatan_validator = $request->catatan_validator;
        $pkm->status_validasi = $request->status_validasi;
        $pkm->bobot = $request->bobot;

        $pkm->save();
        activity_log('validasi_pkm', 'Admin memvalidasi PKM ID: '.$id.' status: '.$request->status_validasi);
        Alert::success('Status dan catatan berhasil diperbarui.');

        return redirect()->back()
            ->with('success', 'Status dan catatan berhasil diperbarui.')
            ->with('item_id', $id);
    }

    public function tambahan(Request $request)
    {
        $query = KegiatanTambahan::query();

        $count_ditinjau = KegiatanTambahan::where('status_validasi', 'ditinjau')->count();
        $count_disetujui = KegiatanTambahan::where('status_validasi', 'disetujui')->count();
        $count_ditolak = KegiatanTambahan::where('status_validasi', 'ditolak')->count();
        $count_menunggu = KegiatanTambahan::where('status_validasi', 'menunggu')->count();

        return view('admin.validator.tambahan.index', [
            'count_ditinjau' => $count_ditinjau,
            'count_disetujui' => $count_disetujui,
            'count_ditolak' => $count_ditolak,
            'count_menunggu' => $count_menunggu,
        ]);
    }

    public function tambahanDisetujui(Request $request)
    {
        $search = $request->input('search');
        $query = KegiatanTambahan::where('status_validasi', 'disetujui');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('bentuk_kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.tambahan.approval', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function tambahanDitolak(Request $request)
    {
        $search = $request->input('search');
        $query = KegiatanTambahan::where('status_validasi', 'ditolak');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('bentuk_kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.tambahan.rejected', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function tambahanDitinjau(Request $request)
    {
        $search = $request->input('search');
        $query = KegiatanTambahan::where('status_validasi', 'ditinjau');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('bentuk_kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.tambahan.pending', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function tambahanMenunggu(Request $request)
    {
        $search = $request->input('search');
        $query = KegiatanTambahan::where('status_validasi', 'menunggu');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('bentuk_kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.tambahan.waiting', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function updateCatatanTambahan(Request $request, $id)
    {
        $request->validate([
            'catatan_validator' => 'nullable|string',
            'status_validasi' => 'required|in:menunggu,disetujui,ditolak,ditinjau',
        ]);

        $tambahan = KegiatanTambahan::findOrFail($id);
        $tambahan->catatan_validator = $request->catatan_validator;
        $tambahan->status_validasi = $request->status_validasi;
        $tambahan->bobot = $request->bobot;

        $tambahan->save();
        activity_log('validasi_kegiatan_tambahan', 'Admin memvalidasi kegiatan tambahan ID: '.$id.' status: '.$request->status_validasi);
        Alert::success('Status dan catatan berhasil diperbarui.');

        return redirect()->back()
            ->with('success', 'Status dan catatan berhasil diperbarui.')
            ->with('item_id', $id);
    }

    public function bahasa(Request $request)
    {
        $query = PenguasaanBahasa::query();

        $count_ditinjau = PenguasaanBahasa::where('status_validasi', 'ditinjau')->count();
        $count_disetujui = PenguasaanBahasa::where('status_validasi', 'disetujui')->count();
        $count_ditolak = PenguasaanBahasa::where('status_validasi', 'ditolak')->count();
        $count_menunggu = PenguasaanBahasa::where('status_validasi', 'menunggu')->count();

        return view('admin.validator.bahasa.index', [
            'count_ditinjau' => $count_ditinjau,
            'count_disetujui' => $count_disetujui,
            'count_ditolak' => $count_ditolak,
            'count_menunggu' => $count_menunggu,
        ]);
    }

    public function bahasaDisetujui(Request $request)
    {
        $search = $request->input('search');
        $query = PenguasaanBahasa::where('status_validasi', 'disetujui');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_bahasa', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.bahasa.approval', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function bahasaDitolak(Request $request)
    {
        $search = $request->input('search');
        $query = PenguasaanBahasa::where('status_validasi', 'ditolak');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_bahasa', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.bahasa.rejected', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function bahasaDitinjau(Request $request)
    {
        $search = $request->input('search');
        $query = PenguasaanBahasa::where('status_validasi', 'ditinjau');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_bahasa', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.bahasa.pending', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function bahasaMenunggu(Request $request)
    {
        $search = $request->input('search');
        $query = PenguasaanBahasa::where('status_validasi', 'menunggu');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_bahasa', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.bahasa.waiting', [
            'query' => $result,
            'search' => $search,
        ]);
    }

    public function updateCatatanBahasa(Request $request, $id)
    {
        $request->validate([
            'catatan_validator' => 'nullable|string',
            'status_validasi' => 'required|in:menunggu,disetujui,ditolak,ditinjau',
        ]);

        $bahasa = PenguasaanBahasa::findOrFail($id);
        $bahasa->catatan_validator = $request->catatan_validator;
        $bahasa->status_validasi = $request->status_validasi;
        $bahasa->bobot = $request->bobot;

        $bahasa->save();
        activity_log('validasi_bahasa', 'Admin memvalidasi penguasaan bahasa ID: '.$id.' status: '.$request->status_validasi);
        Alert::success('Status dan catatan berhasil diperbarui.');

        return redirect()->back()
            ->with('success', 'Status dan catatan berhasil diperbarui.')
            ->with('item_id', $id);
    }
}
