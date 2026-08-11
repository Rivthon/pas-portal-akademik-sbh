<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TagihanMahasiswa;
use App\Models\TahunAkademik;
use App\Models\TarifPerSemester;
use App\Models\TenorPembayaran;
use App\Models\TransaksiPembayaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;

class TagihanMahasiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:tagihan-list|tagihan-create|tagihan-edit|tagihan-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:tagihan-create', ['only' => ['store']]);
        $this->middleware('permission:tagihan-edit', ['only' => ['update']]);
        $this->middleware('permission:tagihan-delete', ['only' => ['destroy']]);
    }

    /**
     * Halaman utama: tampilkan daftar semua mahasiswa dengan ringkasan tagihan
     */
    public function index(Request $request)
    {
        $programStudiList = ProgramStudi::orderBy('nama')->get();
        $tahunMasukList = Mahasiswa::select('tahun_masuk')
            ->distinct()
            ->orderBy('tahun_masuk', 'desc')
            ->pluck('tahun_masuk');

        // Query mahasiswa aktif dengan agregasi tagihan
        $query = Mahasiswa::with('programStudi')
            ->select('mahasiswa.*')
            ->where('status_mhs', 'aktif');

        // Filter Program Studi
        if ($request->filled('program_studi')) {
            $query->where('jurusan_id', $request->program_studi);
        }

        // Filter Tahun Masuk
        if ($request->filled('tahun_masuk')) {
            $query->where('tahun_masuk', $request->tahun_masuk);
        }

        // Search by name/NIM
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        $mahasiswaList = $query->orderBy('nama', 'asc')->paginate(20)->appends($request->query());

        // Hitung agregasi tagihan per mahasiswa (batch query untuk performa)
        $mahasiswaIds = $mahasiswaList->pluck('mahasiswa_id')->toArray();

        $tagihanAggregates = TagihanMahasiswa::whereIn('mahasiswa_id', $mahasiswaIds)
            ->select(
                'mahasiswa_id',
                DB::raw('SUM(jumlah_tagihan) as total_tagihan'),
                DB::raw('COUNT(*) as jumlah_tagihan_count')
            )
            ->groupBy('mahasiswa_id')
            ->get()
            ->keyBy('mahasiswa_id');

        // Hitung total pembayaran per mahasiswa (hanya yang diterima)
        $pembayaranAggregates = TransaksiPembayaran::whereIn('mahasiswa_id', $mahasiswaIds)
            ->where('status_verifikasi', 'diterima')
            ->select(
                'mahasiswa_id',
                DB::raw('SUM(nominal_bayar) as total_dibayar')
            )
            ->groupBy('mahasiswa_id')
            ->get()
            ->keyBy('mahasiswa_id');

        // Attach aggregates ke setiap mahasiswa
        foreach ($mahasiswaList as $mhs) {
            $tagihan = $tagihanAggregates->get($mhs->mahasiswa_id);
            $pembayaran = $pembayaranAggregates->get($mhs->mahasiswa_id);

            $mhs->total_tagihan = $tagihan ? (int) $tagihan->total_tagihan : 0;
            $mhs->jumlah_tagihan_count = $tagihan ? (int) $tagihan->jumlah_tagihan_count : 0;
            $mhs->total_dibayar = $pembayaran ? (int) $pembayaran->total_dibayar : 0;
            $mhs->sisa_tagihan = max($mhs->total_tagihan - $mhs->total_dibayar, 0);
        }

        // Summary cards data
        $totalMahasiswa = $mahasiswaList->total();
        $totalTagihanAll = $tagihanAggregates->sum('total_tagihan');
        $totalDibayarAll = $pembayaranAggregates->sum('total_dibayar');
        $totalSisaAll = max($totalTagihanAll - $totalDibayarAll, 0);

        return view('admin.keuangan.tagihan-mahasiswa.index', compact(
            'mahasiswaList',
            'programStudiList',
            'tahunMasukList',
            'totalMahasiswa',
            'totalTagihanAll',
            'totalDibayarAll',
            'totalSisaAll'
        ));
    }

    /**
     * API: Detail tagihan per mahasiswa (untuk modal detail)
     */
    public function getDetailTagihan($mahasiswaId)
    {
        try {
            $mahasiswa = Mahasiswa::with('programStudi')->findOrFail($mahasiswaId);

            $tagihan = TagihanMahasiswa::with(['tenorPembayaran', 'transaksi'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->orderBy('semester', 'asc')
                ->orderBy('jatuh_tempo', 'asc')
                ->get();

            // Calculate per-tagihan data
            $tagihanData = $tagihan->map(function ($t) {
                $dibayar = $t->transaksi
                    ->where('status_verifikasi', 'diterima')
                    ->sum('nominal_bayar');
                $sisa = max($t->jumlah_tagihan - $dibayar, 0);

                return [
                    'id' => $t->id,
                    'semester' => $t->semester,
                    'tenor' => $t->tenorPembayaran->tenor ?? '-',
                    'persentase' => $t->tenorPembayaran->persentase ?? 0,
                    'jumlah_tagihan' => $t->jumlah_tagihan,
                    'dibayar' => (int) $dibayar,
                    'sisa' => $sisa,
                    'jatuh_tempo' => $t->jatuh_tempo,
                    'status' => $t->status,
                    'transaksi' => $t->transaksi->map(function ($tr) {
                        return [
                            'tanggal_bayar' => $tr->tanggal_bayar,
                            'nominal_bayar' => $tr->nominal_bayar,
                            'keterangan' => $tr->keterangan,
                            'status_verifikasi' => $tr->status_verifikasi,
                        ];
                    }),
                ];
            });

            $totalTagihan = $tagihan->sum('jumlah_tagihan');
            $totalDibayar = $tagihanData->sum('dibayar');

            return response()->json([
                'mahasiswa' => [
                    'nama' => $mahasiswa->nama,
                    'nim' => $mahasiswa->nim,
                    'prodi' => $mahasiswa->programStudi->nama ?? '-',
                    'semester' => $mahasiswa->semester,
                ],
                'tagihan' => $tagihanData,
                'total_tagihan' => $totalTagihan,
                'total_dibayar' => (int) $totalDibayar,
                'sisa' => max($totalTagihan - $totalDibayar, 0),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getDetailTagihan: '.$e->getMessage());

            return response()->json(['error' => 'Gagal memuat detail tagihan.'], 500);
        }
    }

    /**
     * API: Ambil tagihan belum lunas untuk bayar (modal bayar)
     */
    public function getTagihanForBayar($mahasiswaId)
    {
        try {
            $mahasiswa = Mahasiswa::findOrFail($mahasiswaId);

            $tagihan = TagihanMahasiswa::with(['tenorPembayaran', 'transaksi'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->where('status', 'belum_lunas')
                ->orderBy('jatuh_tempo', 'asc')
                ->get();

            $data = $tagihan->map(function ($t) {
                $dibayar = $t->transaksi
                    ->where('status_verifikasi', 'diterima')
                    ->sum('nominal_bayar');
                $sisa = max($t->jumlah_tagihan - $dibayar, 0);

                return [
                    'id' => $t->id,
                    'tenor' => $t->tenorPembayaran->tenor ?? '-',
                    'semester' => $t->semester,
                    'jumlah_tagihan' => $t->jumlah_tagihan,
                    'dibayar' => (int) $dibayar,
                    'sisa' => $sisa,
                    'jatuh_tempo' => $t->jatuh_tempo,
                ];
            });

            return response()->json([
                'mahasiswa' => [
                    'nama' => $mahasiswa->nama,
                    'nim' => $mahasiswa->nim,
                ],
                'tagihan' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getTagihanForBayar: '.$e->getMessage());

            return response()->json(['error' => 'Gagal memuat data tagihan.'], 500);
        }
    }

    /**
     * API: Ambil mahasiswa berdasarkan filter prodi, tahun masuk, gelombang
     */
    public function getMahasiswa(Request $request)
    {
        try {
            Log::info('getMahasiswa parameters: ', $request->all());

            $query = Mahasiswa::query();

            if ($request->filled('program_studi')) {
                $query->where('jurusan_id', $request->program_studi);
            }
            if ($request->filled('tahun_masuk')) {
                $query->where('tahun_masuk', $request->tahun_masuk);
            }
            if ($request->filled('gelombang_id')) {
                $query->where('gelombang_id', $request->gelombang_id);
            }

            $mahasiswa = $query->orderBy('nama', 'asc')->get();

            Log::info('Found mahasiswa: '.$mahasiswa->count());

            return response()->json($mahasiswa);
        } catch (\Exception $e) {
            Log::error('Error getMahasiswa: '.$e->getMessage());

            return response()->json(['error' => 'Terjadi kesalahan: '.$e->getMessage()], 500);
        }
    }

    /**
     * Simpan tagihan baru — kalkulasi otomatis dari tarif × persentase
     */
    public function store(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,mahasiswa_id',
            'tenor_pembayaran_id' => 'required|exists:tenor_pembayaran,id',
        ]);

        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        if (! $activeTa) {
            return response()->json(['success' => false, 'message' => 'Tidak ada Tahun Ajaran yang aktif.'], 400);
        }

        $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);
        $tenor = TenorPembayaran::findOrFail($request->tenor_pembayaran_id);

        // Cek duplikat
        $sudahAda = TagihanMahasiswa::where([
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'tenor_pembayaran_id' => $tenor->id,
            'semester' => $mahasiswa->semester,
        ])->exists();

        if ($sudahAda) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan untuk mahasiswa ini dengan tenor dan semester yang sama sudah ada.',
            ], 422);
        }

        // Cari tarif
        $tarif = TarifPerSemester::where([
            'jurusan_id' => $mahasiswa->jurusan_id,
            'tahun_masuk' => $mahasiswa->tahun_masuk,
            'gelombang_id' => $mahasiswa->gelombang_id,
        ])->first();

        if (! $tarif) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif belum diatur untuk prodi/angkatan/gelombang mahasiswa ini.',
            ], 404);
        }

        $jumlahTagihan = intval(($tenor->persentase / 100) * $tarif->tarif);

        TagihanMahasiswa::create([
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'semester' => $mahasiswa->semester,
            'ta_id' => $activeTa->ta_id,
            'tenor_pembayaran_id' => $tenor->id,
            'jumlah_tagihan' => $jumlahTagihan,
            'jatuh_tempo' => $tenor->batas_waktu,
            'status' => 'belum_lunas',
        ]);

        activity_log('tambah_tagihan', 'Admin membuat tagihan untuk '.$mahasiswa->nama.' (NIM: '.$mahasiswa->nim.') tenor: '.$tenor->tenor.' Rp '.number_format($jumlahTagihan));

        return response()->json([
            'success' => true,
            'message' => 'Tagihan berhasil dibuat untuk '.$mahasiswa->nama.' sebesar Rp '.number_format($jumlahTagihan),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tenor_pembayaran_id' => 'required|exists:tenor_pembayaran,id',
        ]);

        $tagihan = TagihanMahasiswa::findOrFail($id);
        $tenor = TenorPembayaran::findOrFail($request->tenor_pembayaran_id);

        // Recalculate dari tarif asli
        $mahasiswa = Mahasiswa::find($tagihan->mahasiswa_id);
        $tarif = null;
        if ($mahasiswa) {
            $tarif = TarifPerSemester::where([
                'jurusan_id' => $mahasiswa->jurusan_id,
                'tahun_masuk' => $mahasiswa->tahun_masuk,
                'gelombang_id' => $mahasiswa->gelombang_id,
            ])->first();
        }

        $jumlahTagihan = $tarif
            ? intval(($tenor->persentase / 100) * $tarif->tarif)
            : $tagihan->jumlah_tagihan;

        $tagihan->update([
            'tenor_pembayaran_id' => $request->tenor_pembayaran_id,
            'jumlah_tagihan' => $jumlahTagihan,
            'jatuh_tempo' => $tenor->batas_waktu,
        ]);

        activity_log('update_tagihan', 'Admin memperbarui tagihan ID: '.$tagihan->id);

        Alert::toast('Tagihan Berhasil diperbarui.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.tagihan-mahasiswa.index');
    }

    public function destroy($id): RedirectResponse
    {
        $tagihan = TagihanMahasiswa::findOrFail($id);
        activity_log('hapus_tagihan', 'Admin menghapus tagihan ID: '.$tagihan->id);
        $tagihan->delete();

        Alert::toast('Tagihan berhasil dihapus.', 'info')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.tagihan-mahasiswa.index');
    }

    public function generateTagihan()
    {
        Log::info('Generate tagihan dimulai...');

        $tahunAjaranAktif = TahunAkademik::where('status_ta', 1)->first();
        if (! $tahunAjaranAktif) {
            Log::error('Tidak ada Tahun Ajaran yang aktif.');

            return response()->json(['success' => false, 'message' => 'Tidak ada Tahun Ajaran yang aktif.'], 400);
        }

        $totalTagihanBaru = 0;

        Mahasiswa::where('status_mhs', 'aktif')->chunk(200, function ($mahasiswas) use ($tahunAjaranAktif, &$totalTagihanBaru) {
            $tagihanData = [];

            foreach ($mahasiswas as $mahasiswa) {
                $tarif = TarifPerSemester::where([
                    'jurusan_id' => $mahasiswa->jurusan_id,
                    'tahun_masuk' => $mahasiswa->tahun_masuk,
                    'gelombang_id' => $mahasiswa->gelombang_id,
                ])->first();

                if (! $tarif) {
                    continue;
                }

                $tenorList = TenorPembayaran::where('semester', $mahasiswa->semester)
                    ->where('tahun_masuk', $mahasiswa->tahun_masuk)
                    ->where('gelombang_id', $mahasiswa->gelombang_id)
                    ->get();

                if ($tenorList->isEmpty()) {
                    continue;
                }

                $total_tarif = $tarif->tarif;

                foreach ($tenorList as $tenor) {
                    $cekTagihan = TagihanMahasiswa::where([
                        'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                        'tenor_pembayaran_id' => $tenor->id,
                        'semester' => $mahasiswa->semester,
                    ])->exists();

                    if ($cekTagihan) {
                        continue;
                    }

                    $persentase = $tenor->persentase > 0 ? ($tenor->persentase / 100) : 0;
                    $jumlah_tagihan = $total_tarif * $persentase;

                    $tagihanData[] = [
                        'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                        'ta_id' => $tahunAjaranAktif->ta_id,
                        'semester' => $mahasiswa->semester,
                        'tenor_pembayaran_id' => $tenor->id,
                        'jumlah_tagihan' => intval($jumlah_tagihan),
                        'jatuh_tempo' => $tenor->batas_waktu,
                        'status' => 'belum_lunas',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $totalTagihanBaru++;
                }
            }

            if (! empty($tagihanData)) {
                TagihanMahasiswa::insert($tagihanData);
            }
        });

        if ($totalTagihanBaru > 0) {
            activity_log('generate_tagihan', 'Admin generate '.$totalTagihanBaru.' tagihan otomatis');
            Log::info("$totalTagihanBaru tagihan berhasil dibuat.");

            return response()->json(['success' => true, 'message' => "$totalTagihanBaru tagihan baru berhasil dibuat dan didistribusikan."], 200);
        } else {
            Log::info('Tidak ada tagihan baru yang dibuat.');

            return response()->json(['success' => false, 'message' => 'Semua tagihan sudah up-to-date. Tidak ada tagihan baru yg terbuat.'], 200);
        }
    }

    public function updatePembayaran(Request $request, $id)
    {
        $tagihan = TagihanMahasiswa::findOrFail($id);

        $request->validate([
            'nominal_bayar' => 'required|numeric|min:1',
            'tanggal_bayar' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // 1. Simpan history transaksi
        TransaksiPembayaran::create([
            'mahasiswa_id' => $tagihan->mahasiswa_id,
            'tagihan_mahasiswa_id' => $tagihan->id,
            'nominal_bayar' => $request->nominal_bayar,
            'tanggal_bayar' => $request->tanggal_bayar,
            'keterangan' => $request->keterangan,
            'status_verifikasi' => 'diterima',
        ]);

        // 2. Hitung total yang sudah dibayar
        $totalTelahDibayar = TransaksiPembayaran::where('tagihan_mahasiswa_id', $tagihan->id)
            ->where('status_verifikasi', 'diterima')
            ->sum('nominal_bayar');

        // 3. Update status lunas jika mencukupi
        $tagihan->status = $totalTelahDibayar >= $tagihan->jumlah_tagihan ? 'lunas' : 'belum_lunas';

        try {
            $tagihan->total_pembayaran = $totalTelahDibayar;
        } catch (\Exception $e) {
        }

        $tagihan->save();

        activity_log('update_pembayaran', 'Admin mencatat pembayaran tagihan ID: '.$tagihan->id.' sebesar '.$request->nominal_bayar);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran otomatis tercatat ke histori transaksi.',
            'sisa' => ($tagihan->jumlah_tagihan - $totalTelahDibayar),
        ]);
    }
}
