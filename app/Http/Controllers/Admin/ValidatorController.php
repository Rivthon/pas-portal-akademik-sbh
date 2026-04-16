<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Sertifikasi;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ValidatorController extends Controller
{
    public function index()
    {

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
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                  ->orWhereHas('mahasiswa', function($mq) use ($search) {
                      $mq->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.sertifikasi.approval', [
            'query' => $result,
            'search' => $search
        ]);
    }
    public function sertifikasiDitolak(Request $request)
    {
        $search = $request->input('search');
        $query = Sertifikasi::where('status_validasi', 'ditolak');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.sertifikasi.rejected', [
            'query' => $result,
            'search' => $search
        ]);
    }
    public function sertifikasiDitinjau(Request $request)
    {
        $search = $request->input('search');
        $query = Sertifikasi::where('status_validasi', 'ditinjau');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.sertifikasi.pending', [
            'query' => $result,
            'search' => $search
        ]);
    }
    public function sertifikasiMenunggu(Request $request)
    {
        $search = $request->input('search');
        $query = Sertifikasi::where('status_validasi', 'menunggu');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function($mq) use ($search) {
                        $mq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $result = $query->paginate(10);

        return view('admin.validator.sertifikasi.waiting', [
            'query' => $result,
            'search' => $search
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
            Alert::success('Status dan catatan berhasil diperbarui.');
            return redirect()->back()
            ->with('success', 'Status dan catatan berhasil diperbarui.')
            ->with('item_id', $id);

        }
        public function wirausaha(Request $request)
        {
            $query = \App\Models\P2mwProgram::query();

            $count_ditinjau = \App\Models\P2mwProgram::where('status_validasi', 'ditinjau')->count();
            $count_disetujui = \App\Models\P2mwProgram::where('status_validasi', 'disetujui')->count();
            $count_ditolak = \App\Models\P2mwProgram::where('status_validasi', 'ditolak')->count();
            $count_menunggu = \App\Models\P2mwProgram::where('status_validasi', 'menunggu')->count();

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
            $query = \App\Models\P2mwProgram::where('status_validasi', 'disetujui');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_usaha', 'like', "%{$search}%")
                        ->orWhere('jenis_usaha', 'like', "%{$search}%")
                        ->orWhereHas('mahasiswa', function($mq) use ($search) {
                            $mq->where('nama', 'like', "%{$search}%");
                        });
                });
            }

            $result = $query->paginate(10);

            return view('admin.validator.wirausaha.approval', [
                'query' => $result,
                'search' => $search
            ]);
        }

        public function wirausahaDitolak(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\P2mwProgram::where('status_validasi', 'ditolak');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_usaha', 'like', "%{$search}%")
                        ->orWhere('jenis_usaha', 'like', "%{$search}%")
                        ->orWhereHas('mahasiswa', function($mq) use ($search) {
                            $mq->where('nama', 'like', "%{$search}%");
                        });
                });
            }

            $result = $query->paginate(10);

            return view('admin.validator.wirausaha.rejected', [
                'query' => $result,
                'search' => $search
            ]);
        }

        public function wirausahaDitinjau(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\P2mwProgram::where('status_validasi', 'ditinjau');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_usaha', 'like', "%{$search}%")
                        ->orWhere('jenis_usaha', 'like', "%{$search}%")
                        ->orWhereHas('mahasiswa', function($mq) use ($search) {
                            $mq->where('nama', 'like', "%{$search}%");
                        });
                });
            }

            $result = $query->paginate(10);

            return view('admin.validator.wirausaha.pending', [
                'query' => $result,
                'search' => $search
            ]);
        }

        public function wirausahaMenunggu(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\P2mwProgram::where('status_validasi', 'menunggu');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_usaha', 'like', "%{$search}%")
                        ->orWhere('jenis_usaha', 'like', "%{$search}%")
                        ->orWhereHas('mahasiswa', function($mq) use ($search) {
                            $mq->where('nama', 'like', "%{$search}%");
                        });
                });
            }

            $result = $query->paginate(10);

            return view('admin.validator.wirausaha.waiting', [
                'query' => $result,
                'search' => $search
            ]);
        }

        public function updateCatatanWirausaha(Request $request, $id)
        {
            $request->validate([
                'catatan_validator' => 'nullable|string',
                'status_validasi' => 'required|in:menunggu,disetujui,ditolak,ditinjau',
            ]);

            $p2mw = \App\Models\P2mwProgram::findOrFail($id);
            $p2mw->catatan_validator = $request->catatan_validator;
            $p2mw->status_validasi = $request->status_validasi;
            $p2mw->bobot = $request->bobot;

            $p2mw->save();
            Alert::success('Status dan catatan berhasil diperbarui.');
            return redirect()->back()
                ->with('success', 'Status dan catatan berhasil diperbarui.')
                ->with('item_id', $id);
        }
        public function ppsm(Request $request)
        {
            $query = \App\Models\Ppsm::query();

            $count_ditinjau = \App\Models\Ppsm::where('status_validasi', 'ditinjau')->count();
            $count_disetujui = \App\Models\Ppsm::where('status_validasi', 'disetujui')->count();
            $count_ditolak = \App\Models\Ppsm::where('status_validasi', 'ditolak')->count();
            $count_menunggu = \App\Models\Ppsm::where('status_validasi', 'menunggu')->count();

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
            $query = \App\Models\Ppsm::where('status_validasi', 'disetujui');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                ->orWhere('tahun_kegiatan', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.ppsm.approval', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function ppsmDitolak(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\Ppsm::where('status_validasi', 'ditolak');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                ->orWhere('tahun_kegiatan', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.ppsm.rejected', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function ppsmDitinjau(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\Ppsm::where('status_validasi', 'ditinjau');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                ->orWhere('tahun_kegiatan', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.ppsm.pending', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function ppsmMenunggu(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\Ppsm::where('status_validasi', 'menunggu');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                ->orWhere('tahun_kegiatan', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.ppsm.waiting', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function updateCatatanPpsm(Request $request, $id)
        {
            $request->validate([
            'catatan_validator' => 'nullable|string',
            'status_validasi' => 'required|in:menunggu,disetujui,ditolak,ditinjau',
            ]);

            $ppsm = \App\Models\Ppsm::findOrFail($id);
            $ppsm->catatan_validator = $request->catatan_validator;
            $ppsm->status_validasi = $request->status_validasi;
            $ppsm->bobot = $request->bobot;

            $ppsm->save();
            Alert::success('Status dan catatan berhasil diperbarui.');
            return redirect()->back()
            ->with('success', 'Status dan catatan berhasil diperbarui.')
            ->with('item_id', $id);
        }

        public function pkm(Request $request)
        {
            $query = \App\Models\PkmProgram::query();

            $count_ditinjau = \App\Models\PkmProgram::where('status_validasi', 'ditinjau')->count();
            $count_disetujui = \App\Models\PkmProgram::where('status_validasi', 'disetujui')->count();
            $count_ditolak = \App\Models\PkmProgram::where('status_validasi', 'ditolak')->count();
            $count_menunggu = \App\Models\PkmProgram::where('status_validasi', 'menunggu')->count();

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
            $query = \App\Models\PkmProgram::where('status_validasi', 'disetujui');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%{$search}%")
                ->orWhere('jenis_pkm', 'like', "%{$search}%")
                ->orWhere('penyelenggara', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.pkm.approval', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function pkmDitolak(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\PkmProgram::where('status_validasi', 'ditolak');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%{$search}%")
                ->orWhere('jenis_pkm', 'like', "%{$search}%")
                ->orWhere('penyelenggara', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.pkm.rejected', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function pkmDitinjau(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\PkmProgram::where('status_validasi', 'ditinjau');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%{$search}%")
                ->orWhere('jenis_pkm', 'like', "%{$search}%")
                ->orWhere('penyelenggara', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.pkm.pending', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function pkmMenunggu(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\PkmProgram::where('status_validasi', 'menunggu');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%{$search}%")
                ->orWhere('jenis_pkm', 'like', "%{$search}%")
                ->orWhere('penyelenggara', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.pkm.waiting', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function updateCatatanPkm(Request $request, $id)
        {
            $request->validate([
            'catatan_validator' => 'nullable|string',
            'status_validasi' => 'required|in:menunggu,disetujui,ditolak,ditinjau',
            ]);

            $pkm = \App\Models\PkmProgram::findOrFail($id);
            $pkm->catatan_validator = $request->catatan_validator;
            $pkm->status_validasi = $request->status_validasi;
            $pkm->bobot = $request->bobot;

            $pkm->save();
            Alert::success('Status dan catatan berhasil diperbarui.');
            return redirect()->back()
            ->with('success', 'Status dan catatan berhasil diperbarui.')
            ->with('item_id', $id);
        }


        public function tambahan(Request $request)
        {
            $query = \App\Models\KegiatanTambahan::query();

            $count_ditinjau = \App\Models\KegiatanTambahan::where('status_validasi', 'ditinjau')->count();
            $count_disetujui = \App\Models\KegiatanTambahan::where('status_validasi', 'disetujui')->count();
            $count_ditolak = \App\Models\KegiatanTambahan::where('status_validasi', 'ditolak')->count();
            $count_menunggu = \App\Models\KegiatanTambahan::where('status_validasi', 'menunggu')->count();

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
            $query = \App\Models\KegiatanTambahan::where('status_validasi', 'disetujui');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                ->orWhere('bentuk_kegiatan', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.tambahan.approval', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function tambahanDitolak(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\KegiatanTambahan::where('status_validasi', 'ditolak');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                ->orWhere('bentuk_kegiatan', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.tambahan.rejected', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function tambahanDitinjau(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\KegiatanTambahan::where('status_validasi', 'ditinjau');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                ->orWhere('bentuk_kegiatan', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.tambahan.pending', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function tambahanMenunggu(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\KegiatanTambahan::where('status_validasi', 'menunggu');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                ->orWhere('bentuk_kegiatan', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.tambahan.waiting', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function updateCatatanTambahan(Request $request, $id)
        {
            $request->validate([
            'catatan_validator' => 'nullable|string',
            'status_validasi' => 'required|in:menunggu,disetujui,ditolak,ditinjau',
            ]);

            $tambahan = \App\Models\KegiatanTambahan::findOrFail($id);
            $tambahan->catatan_validator = $request->catatan_validator;
            $tambahan->status_validasi = $request->status_validasi;
            $tambahan->bobot = $request->bobot;

            $tambahan->save();
            Alert::success('Status dan catatan berhasil diperbarui.');
            return redirect()->back()
            ->with('success', 'Status dan catatan berhasil diperbarui.')
            ->with('item_id', $id);
        }
        public function bahasa(Request $request)
        {
            $query = \App\Models\PenguasaanBahasa::query();

            $count_ditinjau = \App\Models\PenguasaanBahasa::where('status_validasi', 'ditinjau')->count();
            $count_disetujui = \App\Models\PenguasaanBahasa::where('status_validasi', 'disetujui')->count();
            $count_ditolak = \App\Models\PenguasaanBahasa::where('status_validasi', 'ditolak')->count();
            $count_menunggu = \App\Models\PenguasaanBahasa::where('status_validasi', 'menunggu')->count();

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
            $query = \App\Models\PenguasaanBahasa::where('status_validasi', 'disetujui');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_bahasa', 'like', "%{$search}%")
                ->orWhere('penyelenggara', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.bahasa.approval', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function bahasaDitolak(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\PenguasaanBahasa::where('status_validasi', 'ditolak');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_bahasa', 'like', "%{$search}%")
                ->orWhere('penyelenggara', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.bahasa.rejected', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function bahasaDitinjau(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\PenguasaanBahasa::where('status_validasi', 'ditinjau');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_bahasa', 'like', "%{$search}%")
                ->orWhere('penyelenggara', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.bahasa.pending', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function bahasaMenunggu(Request $request)
        {
            $search = $request->input('search');
            $query = \App\Models\PenguasaanBahasa::where('status_validasi', 'menunggu');

            if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_bahasa', 'like', "%{$search}%")
                ->orWhere('penyelenggara', 'like', "%{$search}%")
                ->orWhereHas('mahasiswa', function($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%");
                });
            });
            }

            $result = $query->paginate(10);

            return view('admin.validator.bahasa.waiting', [
            'query' => $result,
            'search' => $search
            ]);
        }

        public function updateCatatanBahasa(Request $request, $id)
        {
            $request->validate([
            'catatan_validator' => 'nullable|string',
            'status_validasi' => 'required|in:menunggu,disetujui,ditolak,ditinjau',
            ]);

            $bahasa = \App\Models\PenguasaanBahasa::findOrFail($id);
            $bahasa->catatan_validator = $request->catatan_validator;
            $bahasa->status_validasi = $request->status_validasi;
            $bahasa->bobot = $request->bobot;

            $bahasa->save();
            Alert::success('Status dan catatan berhasil diperbarui.');
            return redirect()->back()
            ->with('success', 'Status dan catatan berhasil diperbarui.')
            ->with('item_id', $id);
        }

}
