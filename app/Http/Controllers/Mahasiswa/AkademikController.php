<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\KhsPublication;
use App\Models\Krs;
use App\Models\KrsGuidanceMessage;
use App\Models\Kurikulum;
use App\Models\Setting;
use App\Models\TahunAkademik;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDF;
use RealRashid\SweetAlert\Facades\Alert;

class AkademikController extends Controller
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

    /**
     * Cache Setting agar tidak query berulang
     */
    private function getSettings()
    {
        return Cache::remember('app_settings', 3600, function () {
            return Setting::first();
        });
    }

    public function index(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();
        $semester = $mahasiswa->semester;
        $prodi = $mahasiswa->jurusan_id;
        $activeTA = $this->getActiveTA();

        // Pastikan ada tahun ajaran aktif
        if (! $activeTA) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Tidak ada Tahun Ajaran yang aktif.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
        }

        // Query awal dengan eager loading
        $existingMatakuliahIds = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $activeTA->ta_id)
            ->whereNotNull('matakuliah_id')
            ->pluck('matakuliah_id');
        $hasExistingKrs = $existingMatakuliahIds->isNotEmpty();

        $krs = Kurikulum::with(['programStudi', 'mataKuliah'])
            ->where('ta_id', $activeTA->ta_id)
            ->whereNotIn('matakuliah_id', $existingMatakuliahIds)
            ->whereHas('mataKuliah', function ($query) use ($semester) {
                $query->where('smt', $semester);
            })
            ->whereHas('programStudi', function ($query) use ($prodi) {
                $query->where('jurusan_id', $prodi);
            })
            ->get()
            ->unique('matakuliah_id')
            ->values();

        return view('mahasiswa.krs.index', compact('krs', 'hasExistingKrs'));
    }

    public function nyimpenKrs(Request $request)
    {
        // Ambil ID mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (! $mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan.',
            ], 401);
        }

        // Ambil Tahun Akademik Aktif
        $ta = TahunAkademik::where('status_ta', 1)->first();
        if (! $ta) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun Akademik Aktif tidak ditemukan.',
            ], 400);
        }

        // Validasi input data
        $validated = $request->validate([
            'krs' => 'required|array|min:1',
            'krs.*' => 'integer|distinct|exists:kurikulum,kurikulum_id',
        ]);

        $krsSudahDisetujui = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->whereNotNull('disetujui_pada')
            ->exists();

        if ($krsSudahDisetujui) {
            return response()->json([
                'success' => false,
                'message' => 'KRS sudah disetujui oleh Dosen Pembimbing dan tidak dapat diubah.',
            ], 422);
        }

        try {
            $kurikulumList = Kurikulum::with('mataKuliah')
                ->whereIn('kurikulum_id', array_unique($validated['krs']))
                ->where('ta_id', $ta->ta_id)
                ->where('jurusan_id', $mahasiswa->jurusan_id)
                ->whereHas('mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get();

            if ($kurikulumList->count() !== count(array_unique($validated['krs']))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terdapat mata kuliah yang tidak sesuai dengan prodi, semester, atau tahun akademik aktif.',
                ], 422);
            }

            if ($kurikulumList->pluck('matakuliah_id')->duplicates()->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mata kuliah yang sama tidak boleh dipilih lebih dari satu kali.',
                ], 422);
            }

            $sudahDiambil = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $ta->ta_id)
                ->whereIn('matakuliah_id', $kurikulumList->pluck('matakuliah_id'))
                ->exists();

            if ($sudahDiambil) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salah satu mata kuliah sudah pernah diambil pada KRS tahun akademik ini.',
                ], 422);
            }

            DB::transaction(function () use ($kurikulumList, $mahasiswa, $ta) {
                foreach ($kurikulumList as $kurikulum) {
                    Krs::create([
                        'kurikulum_id' => $kurikulum->kurikulum_id,
                        'matakuliah_id' => $kurikulum->matakuliah_id,
                        'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                        'ta_id' => $ta->ta_id,
                    ]);
                }
            });

            activity_log('isi_krs', 'Mahasiswa mengisi KRS untuk '.$kurikulumList->count().' mata kuliah');

            return response()->json([
                'success' => true,
                'message' => 'KRS berhasil disimpan.',
            ]);
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => 'Mata kuliah tersebut sudah ada di KRS dan tidak dapat diambil dua kali.',
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan KRS.',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan KRS: '.$e->getMessage(),
            ], 500);
        }
    }

    public function tampilkanKrs()
    {
        $mahasiswa = $this->getMahasiswa();
        if (! $mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }
        $mahasiswa->loadMissing('dosen');
        $mahasiswaId = $mahasiswa->mahasiswa_id;
        $activeTA = $this->getActiveTA();

        // Pastikan ada tahun ajaran aktif
        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
        }

        try {
            $krs = Krs::with(['kurikulum.mataKuliah', 'disetujuiOleh'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->where('ta_id', $activeTA->ta_id)
                ->get();

            activity_log('lihat_krs', 'Mahasiswa melihat status KRS');

            // Redirect ke view baru dengan data KRS
            $krsDisetujui = $krs->isNotEmpty() && $krs->every(fn (Krs $item) => $item->disetujui_pada !== null);
            $guidanceMessages = KrsGuidanceMessage::where('mahasiswa_id', $mahasiswaId)
                ->where('dosen_id', $mahasiswa->dosen_id)
                ->where('ta_id', $activeTA->ta_id)
                ->oldest()
                ->get();

            return view('mahasiswa.krs.status-krs', compact('mahasiswa', 'krs', 'krsDisetujui', 'activeTA', 'guidanceMessages'));
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal memuat data KRS: '.$e->getMessage());
        }
    }

    public function storeKrsGuidanceReply(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();
        $activeTA = $this->getActiveTA();

        if (! $mahasiswa || ! $mahasiswa->dosen_id) {
            return back()->with('error', 'Dosen Pembimbing Akademik belum ditentukan.');
        }
        if (! $activeTA) {
            return back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'message.required' => 'Umpan balik tidak boleh kosong.',
            'message.max' => 'Umpan balik maksimal 2.000 karakter.',
        ]);

        KrsGuidanceMessage::create([
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'dosen_id' => $mahasiswa->dosen_id,
            'ta_id' => $activeTA->ta_id,
            'sender_type' => 'mahasiswa',
            'message' => trim($validated['message']),
        ]);

        activity_log('balas_bimbingan_krs', 'Mahasiswa mengirim umpan balik KRS kepada Dosen Pembimbing');

        return back()->with('success', 'Umpan balik berhasil dikirim kepada Dosen Pembimbing.');
    }

    public function hapusKrs($id)
    {
        $krs = Krs::where('mahasiswa_id', $this->getMahasiswa()->mahasiswa_id)->findOrFail($id);

        if ($krs->disetujui_pada !== null) {
            return back()->with('error', 'KRS sudah disetujui oleh Dosen Pembimbing dan tidak dapat dihapus.');
        }

        $krs->delete();
        activity_log('hapus_krs_item', 'Mahasiswa menghapus mata kuliah jadwal ID '.$id.' dari KRS');
        Alert::success('Berhasil', 'KRS berhasil dihapus');

        return redirect()->back();
    }

    public function cetakKapro()
    {
        $mahasiswa = $this->getMahasiswa();
        $mahasiswaId = $mahasiswa->mahasiswa_id;

        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        if (! $ta) {
            return redirect()->back()->with('error', 'Tahun Akademik Aktif tidak ditemukan.');
        }
        if (! $this->krsAktifSudahDisetujui($mahasiswaId, $ta->ta_id)) {
            return back()->with('error', 'KRS belum dapat dicetak karena belum disetujui Dosen Pembimbing.');
        }
        $settings = $this->getSettings();
        $taId = $ta->ta_id;
        $headerKrs = null;
        if ($mahasiswa && $mahasiswa->programStudi && $mahasiswa->programStudi->header_kapro) {
            $logoPath = storage_path('app/public/'.$mahasiswa->programStudi->header_kapro);
            if (file_exists($logoPath)) {
                $headerKrs = base64_encode(file_get_contents($logoPath));
            }
        }
        $ttd = null;
        if ($mahasiswa && $mahasiswa->programStudi && $mahasiswa->programStudi->ttd) {
            $logoPath = storage_path('app/public/'.$mahasiswa->programStudi->ttd);
            if (file_exists($logoPath)) {
                $ttd = base64_encode(file_get_contents($logoPath));
            }
        }
        $logo = null;
        if ($settings && $settings->logo) {
            $logoPath = storage_path('app/public/'.$settings->logo);
            if (file_exists($logoPath)) {
                $logo = base64_encode(file_get_contents($logoPath));
            }
        }

        try {
            $krs = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get();

            // Load view khusus untuk PDF
            $pdf = PDF::loadView('mahasiswa.krs.cetak-pdf-kapro', compact('krs', 'mahasiswa', 'taId', 'headerKrs', 'ttd', 'ta', 'logo', 'settings'))
                ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                ->setPaper('a4', 'portrait');

            activity_log('cetak_krs', 'Mahasiswa mencetak KRS (Template Kaprodi)');

            // download file PDF
            return $pdf->download('KRS-Mahasiswa-Kaprodi.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencetak KRS: '.$e->getMessage());
        }
    }

    public function cetakDospem()
    {
        $mahasiswa = $this->getMahasiswa();
        $mahasiswaId = $mahasiswa->mahasiswa_id;
        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        if (! $ta) {
            return redirect()->back()->with('error', 'Tahun Akademik Aktif tidak ditemukan.');
        }
        if (! $this->krsAktifSudahDisetujui($mahasiswaId, $ta->ta_id)) {
            return back()->with('error', 'KRS belum dapat dicetak karena belum disetujui Dosen Pembimbing.');
        }
        $taId = $ta->ta_id;
        $settings = $this->getSettings();

        $headerKrs = null;
        if ($mahasiswa && $mahasiswa->programStudi && $mahasiswa->programStudi->header_dospem) {
            $logoPath = storage_path('app/public/'.$mahasiswa->programStudi->header_dospem);
            if (file_exists($logoPath)) {
                $headerKrs = base64_encode(file_get_contents($logoPath));
            }
        }
        $ttd = null;
        if ($mahasiswa && $mahasiswa->programStudi && $mahasiswa->programStudi->ttd) {
            $logoPath = storage_path('app/public/'.$mahasiswa->programStudi->ttd);
            if (file_exists($logoPath)) {
                $ttd = base64_encode(file_get_contents($logoPath));
            }
        }
        $logo = null;
        if ($settings && $settings->logo) {
            $logoPath = storage_path('app/public/'.$settings->logo);
            if (file_exists($logoPath)) {
                $logo = base64_encode(file_get_contents($logoPath));
            }
        }

        try {
            $krs = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get();

            // Load view khusus untuk PDF
            $pdf = PDF::loadView('mahasiswa.krs.cetak-pdf-dospem', compact('krs', 'mahasiswa', 'taId', 'headerKrs', 'ttd', 'ta', 'logo', 'settings'))
                ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                ->setPaper('a4', 'portrait');

            activity_log('cetak_krs', 'Mahasiswa mencetak KRS (Template Dospem)');

            return $pdf->download('KRS-Mahasiswa-Dospem.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencetak KRS: '.$e->getMessage());
        }
    }

    public function cetakBaak()
    {
        $mahasiswa = $this->getMahasiswa();
        $mahasiswaId = $mahasiswa->mahasiswa_id;
        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        if (! $ta) {
            return redirect()->back()->with('error', 'Tahun Akademik Aktif tidak ditemukan.');
        }
        if (! $this->krsAktifSudahDisetujui($mahasiswaId, $ta->ta_id)) {
            return back()->with('error', 'KRS belum dapat dicetak karena belum disetujui Dosen Pembimbing.');
        }
        $taId = $ta->ta_id;
        $settings = $this->getSettings();

        $headerKrs = null;
        if ($mahasiswa && $mahasiswa->programStudi && $mahasiswa->programStudi->header_baak) {
            $logoPath = storage_path('app/public/'.$mahasiswa->programStudi->header_baak);
            if (file_exists($logoPath)) {
                $headerKrs = base64_encode(file_get_contents($logoPath));
            }
        }
        $ttd = null;
        if ($mahasiswa && $mahasiswa->programStudi && $mahasiswa->programStudi->ttd) {
            $logoPath = storage_path('app/public/'.$mahasiswa->programStudi->ttd);
            if (file_exists($logoPath)) {
                $ttd = base64_encode(file_get_contents($logoPath));
            }
        }
        $logo = null;
        if ($settings && $settings->logo) {
            $logoPath = storage_path('app/public/'.$settings->logo);
            if (file_exists($logoPath)) {
                $logo = base64_encode(file_get_contents($logoPath));
            }
        }

        try {
            $krs = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get();

            // Load view khusus untuk PDF
            $pdf = PDF::loadView('mahasiswa.krs.cetak-pdf-baak', compact('krs', 'mahasiswa', 'taId', 'headerKrs', 'ttd', 'ta', 'logo', 'settings'))
                ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                ->setPaper('a4', 'portrait');

            activity_log('cetak_krs', 'Mahasiswa mencetak KRS (Template BAAK)');

            // Download file PDF
            return $pdf->download('KRS-Mahasiswa-BAAK.pdf');
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal mencetak KRS: '.$e->getMessage());
        }
    }

    public function cetakMahasiswa()
    {
        $mahasiswa = $this->getMahasiswa();
        $mahasiswaId = $mahasiswa->mahasiswa_id;
        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        if (! $ta) {
            return redirect()->back()->with('error', 'Tahun Akademik Aktif tidak ditemukan.');
        }
        if (! $this->krsAktifSudahDisetujui($mahasiswaId, $ta->ta_id)) {
            return back()->with('error', 'KRS belum dapat dicetak karena belum disetujui Dosen Pembimbing.');
        }
        $taId = $ta->ta_id;
        $settings = $this->getSettings();

        $headerKrs = null;
        if ($mahasiswa && $mahasiswa->programStudi && $mahasiswa->programStudi->header_mhs) {
            $logoPath = storage_path('app/public/'.$mahasiswa->programStudi->header_mhs);
            if (file_exists($logoPath)) {
                $headerKrs = base64_encode(file_get_contents($logoPath));
            }
        }
        $ttd = null;
        if ($mahasiswa && $mahasiswa->programStudi && $mahasiswa->programStudi->ttd) {
            $logoPath = storage_path('app/public/'.$mahasiswa->programStudi->ttd);
            if (file_exists($logoPath)) {
                $ttd = base64_encode(file_get_contents($logoPath));
            }
        }
        $logo = null;
        if ($settings && $settings->logo) {
            $logoPath = storage_path('app/public/'.$settings->logo);
            if (file_exists($logoPath)) {
                $logo = base64_encode(file_get_contents($logoPath));
            }
        }

        try {
            $krs = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereHas('kurikulum.mataKuliah', function ($query) use ($mahasiswa) {
                    $query->where('smt', $mahasiswa->semester);
                })
                ->get();

            // Load view khusus untuk PDF
            $pdf = PDF::loadView('mahasiswa.krs.cetak-pdf-mhs', compact('krs', 'mahasiswa', 'taId', 'headerKrs', 'ttd', 'ta', 'logo', 'settings'))
                ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                ->setPaper('a4', 'portrait');

            activity_log('cetak_krs', 'Mahasiswa mencetak KRS (Template Mahasiswa)');

            // Download file PDF
            return $pdf->download('KRS-Mahasiswa.pdf');
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->with('error', 'Gagal mencetak KRS: '.$e->getMessage());
        }
    }

    public function tampilanKartuHasil(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();

        if (! $mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $activeTa = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);

        try {
            $allPublishedKhs = Krs::with(['kurikulum.mataKuliah', 'kurikulum.tahunAjaran'])
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->whereNotNull('khs')
                ->whereHas('kurikulum.mataKuliah')
                ->get()
                ->filter(fn ($item) => $item->kurikulum && $item->kurikulum->mataKuliah);
            $allPublishedKhs = KhsPublication::filterPublishedKrs($allPublishedKhs, $mahasiswa);
            $availableTaIds = $allPublishedKhs->pluck('ta_id')->map(fn ($id) => (int) $id)->unique();
            $tahunAjaranOptions = TahunAkademik::whereIn('ta_id', $availableTaIds)
                ->orderByDesc('ta_id')
                ->get(['ta_id', 'nama', 'semester']);

            if ($tahunAjaranOptions->isEmpty()) {
                return view('mahasiswa.khs.belum-terbit');
            }

            $requestedTaId = $request->integer('ta_id');
            abort_if($request->filled('ta_id') && ! $availableTaIds->contains($requestedTaId), 404, 'Riwayat KHS tidak ditemukan.');
            $selectedTaId = $requestedTaId
                ?: ($activeTa && $availableTaIds->contains((int) $activeTa->ta_id)
                    ? (int) $activeTa->ta_id
                    : (int) $tahunAjaranOptions->first()->ta_id);
            $ta = $tahunAjaranOptions->firstWhere('ta_id', $selectedTaId);
            $khs = $allPublishedKhs->where('ta_id', $selectedTaId)->values();
            $khsPublished = true;
            $isHistorical = ! $activeTa || (int) $selectedTaId !== (int) $activeTa->ta_id;
            $semesterKhs = $khs->pluck('kurikulum.mataKuliah.smt')->filter()->unique()->sort()->implode(', ');

            [$ipsTotalSks, $ipsTotalBobot] = $this->calculateTotal($khs);
            $ips = $ipsTotalSks > 0 ? $ipsTotalBobot / $ipsTotalSks : 0;

            // IPK pada arsip hanya menghitung nilai yang telah terbit sampai tahun yang dipilih.
            $allKhs = $allPublishedKhs->where('ta_id', '<=', $selectedTaId)->values();

            [$ipkTotalSks, $ipkTotalBobot] = $this->calculateTotal($allKhs);
            $ipk = $ipkTotalSks > 0 ? $ipkTotalBobot / $ipkTotalSks : 0;

            activity_log('lihat_khs', 'Mahasiswa melihat Kartu Hasil Studi (KHS)');

            return view('mahasiswa.khs.index', compact(
                'khs',
                'mahasiswa',
                'ta',
                'ips',
                'ipk',
                'khsPublished',
                'tahunAjaranOptions',
                'selectedTaId',
                'isHistorical',
                'semesterKhs'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data KHS: '.$e->getMessage());
        }
    }

    /**
     * Hitung total SKS dan total Bobot dari data KRS
     */
    private function calculateTotal($khsCollection)
    {
        $totalSks = $khsCollection->sum(function ($item) {
            return $item->kurikulum->mataKuliah->sks ?? 0;
        });

        $totalBobot = $khsCollection->sum(function ($item) {
            $sks = $item->kurikulum->mataKuliah->sks ?? 0;
            $bobot = $this->calculateWeight($item->khs);

            return $sks * $bobot;
        });

        return [$totalSks, $totalBobot];
    }

    private function calculateWeight($grade)
    {
        return match ($grade) {
            'A' => 4.00,
            'AB' => 3.75,
            'BA' => 3.50,
            'B' => 3.00,
            'BC' => 2.75,
            'C' => 2.00,
            'D' => 1.00,
            'E' => 0,
            default => 0,
        };
    }

    private function getPredikat($ipk)
    {
        return match (true) {
            $ipk >= 3.51 => 'Dengan Pujian',
            $ipk >= 3.00 => 'Sangat Baik',
            $ipk >= 2.50 => 'Baik',
            $ipk >= 2.00 => 'Cukup',
            default => 'Kurang',
        };
    }

    public function cetakKhs(Request $request)
    {
        $settings = $this->getSettings();
        $mahasiswa = $this->getMahasiswa();
        if (! $mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $mahasiswaId = $mahasiswa->mahasiswa_id;
        $activeTaId = TahunAkademik::where('status_ta', 1)->value('ta_id');
        $selectedTaId = $request->integer('ta_id') ?: $activeTaId;
        abort_unless($selectedTaId, 403, 'Tahun akademik tidak ditemukan.');
        abort_unless((int) $mahasiswa->status_akhir === 1, 403, 'Akses KHS masih dikunci oleh sistem administrasi.');
        abort_if(
            (int) $selectedTaId === (int) $activeTaId && (int) $mahasiswa->status_edom !== 1,
            403,
            'Silakan selesaikan EDOM sebelum mencetak KHS semester berjalan.'
        );
        $ta = TahunAkademik::findOrFail($selectedTaId);

        // Logo base64
        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = storage_path('app/public/'.$settings->logo); // ✅ lebih aman pakai storage_path
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }

        // Warna header & text berdasarkan jurusan
        $programStudi = strtolower($mahasiswa->jurusan_id ?? '');
        $headerColor = match ($programStudi) {
            '13211' => '#fffbea',
            '48201' => '#f3e8ff',
            '15401' => '#eaf6ff',
            default => '#f3e8ff',
        };
        $textColor = match ($programStudi) {
            '13211' => '#a68c00',
            '48201' => '#6b3fa0',
            '15401' => '#005a9e',
            default => '#6b3fa0',
        };

        try {
            // Ambil KHS pada tahun akademik yang dipilih.
            $khs = Krs::with(['kurikulum.mataKuliah', 'kurikulum.tahunAjaran'])
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $selectedTaId)
                ->whereNotNull('khs')
                ->whereHas('kurikulum.mataKuliah')
                ->get()
                ->filter(function ($item) {
                    return $item->kurikulum && $item->kurikulum->mataKuliah;
                });
            $khs = KhsPublication::filterPublishedKrs($khs, $mahasiswa);
            abort_if($khs->isEmpty(), 403, 'KHS belum diterbitkan oleh BAAK.');

            $semesterKhs = $khs->pluck('kurikulum.mataKuliah.smt')->filter()->unique()->sort()->implode(', ');

            [$ipsTotalSks, $ipsTotalBobot] = $this->calculateTotal($khs);
            $ips = $ipsTotalSks > 0 ? $ipsTotalBobot / $ipsTotalSks : 0;

            // Ambil semua KHS terbit sampai tahun akademik yang dipilih.
            $allKhs = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->where('ta_id', '<=', $selectedTaId)
                ->whereNotNull('khs')
                ->whereHas('kurikulum.mataKuliah')
                ->get()
                ->filter(function ($item) {
                    return $item->kurikulum && $item->kurikulum->mataKuliah && ! is_null($item->khs);
                });
            $allKhs = KhsPublication::filterPublishedKrs($allKhs, $mahasiswa);

            [$ipkTotalSks, $ipkTotalBobot] = $this->calculateTotal($allKhs);
            $ipk = $ipkTotalSks > 0 ? $ipkTotalBobot / $ipkTotalSks : 0;

            // Tentukan predikat dari IPK
            $predikat = $this->getPredikat($ipk);

            // Generate PDF
            $pdf = PDF::loadView('mahasiswa.khs.pdf', compact(
                'khs',
                'mahasiswa',
                'ta',
                'logoBase64',
                'headerColor',
                'textColor',
                'ips',
                'ipk',
                'predikat',
                'semesterKhs'
            ))->setPaper('a4', 'portrait');

            activity_log('cetak_khs', 'Mahasiswa mencetak KHS');

            return $pdf->download('khs-'.$mahasiswa->nama.'-'.$ta->nama.'-'.$ta->semester.'.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data KHS: '.$e->getMessage());
        }
    }

    private function krsAktifSudahDisetujui(int $mahasiswaId, int $taId): bool
    {
        $query = Krs::where('mahasiswa_id', $mahasiswaId)
            ->where('ta_id', $taId);

        return (clone $query)->exists()
            && ! (clone $query)->whereNull('disetujui_pada')->exists();
    }

    public function cetakTranskrip()
    {
        $mahasiswa = $this->getMahasiswa();
        $mahasiswaId = $mahasiswa->mahasiswa_id;

        // Ambil Tahun Akademik Aktif
        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        if (! $ta) {
            return redirect()->back()->with('error', 'Tahun Akademik tidak ditemukan.');
        }

        $taId = $ta->ta_id;

        try {
            // Ambil Data KRS beserta Mata Kuliah
            $khs = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                ->where('krs.mahasiswa_id', $mahasiswaId)
                ->select('krs.*', 'matakuliah.nama as nama', 'matakuliah.sks')
                ->get();

            if ($khs->isEmpty()) {
                return redirect()->back()->with('error', 'Data KHS tidak ditemukan.');
            }

            // Perhitungan IPS
            $totalSks = $khs->sum(fn ($item) => optional($item->kurikulum->mataKuliah)->sks ?? 0);
            $totalBobot = $khs->sum(fn ($item) => optional($item->kurikulum->mataKuliah)->sks * $this->calculateWeight($item->khs));
            $ips = $totalSks > 0 ? $totalBobot / $totalSks : 0;

            // Perhitungan IPK (Dari Semua Semester)
            $allKhs = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                ->where('krs.mahasiswa_id', $mahasiswaId)
                ->select('krs.*', 'matakuliah.nama as nama', 'matakuliah.sks')
                ->get();

            // Filter data agar hanya yang memiliki nilai 'khs' yang tidak null
            $filteredAllKhs = $allKhs->filter(fn ($item) => ! is_null($item->khs));
            $totalSksAll = $filteredAllKhs->sum(fn ($item) => optional($item->kurikulum->mataKuliah)->sks ?? 0);
            $totalBobotAll = $filteredAllKhs->sum(fn ($item) => optional($item->kurikulum->mataKuliah)->sks * $this->calculateWeight($item->khs));
            $ipk = $totalSksAll > 0 ? $totalBobotAll / $totalSksAll : 0;

            // Tentukan Predikat
            $predikat = $this->getPredikat($ipk);

            // Set warna default jika tidak ada
            $programStudi = strtolower($mahasiswa->jurusan_id ?? '');
            $headerColor = match ($programStudi) {
                '13211' => '#fffbea',
                '48201' => '#f3e8ff',
                '15401' => '#eaf6ff',
                default => '#f3e8ff',
            };
            $textColor = match ($programStudi) {
                '13211' => '#a68c00',
                '48201' => '#6b3fa0',
                '15401' => '#005a9e',
                default => '#6b3fa0',
            };

            // Generate PDF
            $pdf = PDF::loadView('mahasiswa.pengajuan.cetak-transkrip', compact(
                'khs',
                'mahasiswa',
                'ta',
                'ips',
                'ipk',
                'predikat',
                'headerColor',
                'textColor'
            ))->setPaper('F4', 'portrait');

            activity_log('cetak_transkrip', 'Mahasiswa mencetak Transkrip Nilai');

            return $pdf->download('transkrip.pdf');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Transkrip belum dapat dicetak. Silakan coba kembali.');
        }
    }
}
