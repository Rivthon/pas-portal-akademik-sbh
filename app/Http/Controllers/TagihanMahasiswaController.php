<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\View\View;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use App\Models\TenorPembayaran;
use Illuminate\Validation\Rule;
use App\Models\TagihanMahasiswa;
use App\Models\TarifPerSemester;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TagihanMahasiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:tagihan-list|tagihan-create|tagihan-edit|tagihan-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:tagihan-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:tagihan-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:tagihan-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $programStudiId = $request->input('program_studi');
        $tahunMasuk = $request->input('tahun_masuk');
        $mahasiswaId = $request->input('mahasiswa_id');

        $programStudiList = ProgramStudi::orderBy('nama')->get();
        $tahunMasukList = Mahasiswa::select('tahun_masuk')->distinct()->orderBy('tahun_masuk')->pluck('tahun_masuk');

        $mahasiswaList = Mahasiswa::when($programStudiId, function ($query) use ($programStudiId) {
                return $query->where('program_studi_id', $programStudiId);
            })
            ->when($tahunMasuk, function ($query) use ($tahunMasuk) {
                return $query->where('tahun_masuk', $tahunMasuk);
            })
            ->orderBy('nama')
            ->get();
        $query = TagihanMahasiswa::with(['mahasiswa', 'tahunAjaran', 'tenorPembayaran'])
            ->when($mahasiswaId, function ($q) use ($mahasiswaId) {
                return $q->where('mahasiswa_id', $mahasiswaId);
            });
        $tagihan = $query->paginate(50)->appends($request->query());

        return view('tagihan-mahasiswa.index', compact('tagihan', 'programStudiList', 'tahunMasukList', 'mahasiswaList', 'programStudiId', 'tahunMasuk', 'mahasiswaId'));
    }

    public function create(): View
    {
        $programStudi = ProgramStudi::all();
        $tenor = TenorPembayaran::all();
        $mahasiswa = Mahasiswa::all();
        $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();

        return view('tagihan-mahasiswa.form', compact('programStudi', 'tenor', 'mahasiswa', 'tahunAjaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,mahasiswa_id',
            'semester' => 'required|integer|min:1|max:8',
            'tenor_pembayaran_id' => 'required|exists:tenor_pembayaran,id',
        ]);

        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        if (!$activeTa) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
        }

        $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);
        $tarif = $mahasiswa->programStudi->tarifPerSemester()
            ->where('semester', $request->semester)
            ->where('ta_id', $activeTa->ta_id)
            ->first();

        if (!$tarif) {
            return redirect()->back()->with('error', 'Tarif untuk semester ini tidak ditemukan.');
        }

        $tenor = TenorPembayaran::findOrFail($request->id);
        $jumlah_tagihan = ($tenor->persentase / 100) * $tarif->tarif;

        TagihanMahasiswa::create([
            'mahasiswa_id' => $request->mahasiswa_id,
            'semester' => $request->semester,
            'ta_id' => $activeTa->ta_id,
            'tenor_pembayaran_id' => $request->tenor_pembayaran_id,
            'jumlah_tagihan' => $jumlah_tagihan,
            'jatuh_tempo' => $tenor->batas_waktu,
            'status' => 'belum_lunas',
        ]);

        Alert::toast('Tagihan Berhasil dibuat.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('tagihan-mahasiswa.index');
    }

    public function edit($id)
    {
        $tagihan = TagihanMahasiswa::findOrFail($id);
        $programStudi = ProgramStudi::all();
        $tenor = TenorPembayaran::all();
        $mahasiswa = Mahasiswa::all();
        $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();

        return view('tagihan-mahasiswa.form', compact('tagihan', 'programStudi', 'tenor', 'mahasiswa', 'tahunAjaran'));
    }

    public function update(Request $request, TagihanMahasiswa $tagihan)
    {
        $request->validate([
            'tenor_pembayaran_id' => 'required|exists:tenor_pembayaran,id',
        ]);

        $tenor = TenorPembayaran::findOrFail($request->tenor_pembayaran_id);
        $jumlah_tagihan = ($tenor->persentase / 100) * $tagihan->jumlah_tagihan;

        $tagihan->update([
            'tenor_pembayaran_id' => $request->tenor_pembayaran_id,
            'jumlah_tagihan' => $jumlah_tagihan,
            'jatuh_tempo' => $tenor->batas_waktu,
        ]);

        Alert::toast('Tagihan Berhasil dibuat.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('tagihan-mahasiswa.index');
    }

    public function destroy(TagihanMahasiswa $tagihan): RedirectResponse
    {
        $tagihan->delete();

        Alert::toast('Tagihan berhasil delete.', 'info')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.tarif.index');
    }

    public function generateTagihan()
    {
        Log::info('Generate tagihan dimulai...');

        $tahunAjaranAktif = TahunAkademik::where('status_ta', 1)->first();
        if (!$tahunAjaranAktif) {
            Log::error('Tidak ada Tahun Ajaran yang aktif.');
            return response()->json(['success' => false, 'message' => 'Tidak ada Tahun Ajaran yang aktif.'], 400);
        }

        $mahasiswaList = Mahasiswa::where('status_mhs', 'aktif')->get();
        if ($mahasiswaList->isEmpty()) {
            Log::warning('Tidak ada mahasiswa aktif.');
            return response()->json(['success' => false, 'message' => 'Tidak ada mahasiswa aktif.'], 400);
        }

        $tagihanData = [];
        $totalTagihanBaru = 0;

        foreach ($mahasiswaList as $mahasiswa) {
            $tarif = TarifPerSemester::where([
                'jurusan_id' => $mahasiswa->jurusan_id,
                'tahun_masuk' => $mahasiswa->tahun_masuk,
                'gelombang_id' => $mahasiswa->gelombang_id
            ])->first();

            if (!$tarif) {
                Log::warning("Tarif tidak ditemukan untuk mahasiswa {$mahasiswa->mahasiswa_id}");
                continue;
            }

            $tenorList = TenorPembayaran::where('semester', $mahasiswa->semester)->get();
            if ($tenorList->isEmpty()) {
                Log::error("Tidak ada tenor pembayaran untuk semester {$mahasiswa->semester}");
                continue;
            }

            $total_tarif = $tarif->tarif;

            foreach ($tenorList as $tenor) {
                $cekTagihan = TagihanMahasiswa::where([
                    'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                    'tenor_pembayaran_id' => $tenor->id,
                    'semester' => $mahasiswa->semester
                ])->exists();

                if ($cekTagihan) {
                    Log::info("Tagihan sudah ada untuk mahasiswa {$mahasiswa->mahasiswa_id}, tenor {$tenor->id}");
                    continue;
                }

                $jumlah_tagihan =  $total_tarif / $tenorList->count();

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

        if (!empty($tagihanData)) {
            TagihanMahasiswa::insert($tagihanData);
            Log::info("$totalTagihanBaru tagihan berhasil dibuat.");
            return response()->json(['success' => true, 'message' => "$totalTagihanBaru tagihan berhasil dibuat."], 200);
        } else {
            Log::info('Tidak ada tagihan baru yang dibuat.');
            return response()->json(['success' => false, 'message' => 'Tidak ada tagihan baru yang bisa dibuat.'], 200);
        }
    }

   public function searchTagihan(Request $request)
    {
        //  TagihanMahasiswa::with(['mahasiswa', 'tahunAjaran', 'tenorPembayaran'])
        //      ->when($mahasiswaId, function ($q) use ($mahasiswaId) {
        //         return $q->where('mahasiswa_id', $mahasiswaId);
        //    });
        $query = TagihanMahasiswa::query()
            ->with('tenorPembayaran', 'mahasiswa','tenorPembayaran') // Eager load untuk menghindari N+1 Query Problem
            ->whereHas('mahasiswa', function ($q) use ($request) {
                if ($request->program_studi) {
                    $q->where('jurusan_id', $request->program_studi);
                }
                if ($request->tahun_masuk) {
                    $q->where('tahun_masuk', $request->tahun_masuk);
                }
                if ($request->mahasiswa_id) {
                    $q->where('mahasiswa_id', $request->mahasiswa_id);
                }
            });

        $tagihan = $query->orderBy('jatuh_tempo', 'asc')->get();

        return response()->json($tagihan);
    }

   public function getMahasiswa(Request $request)
    {
        $mahasiswa = Mahasiswa::query()
            ->when($request->input('program_studi'), function ($query, $programStudi) {
                return $query->where('jurusan_id', $programStudi);
            })
            ->when($request->input('tahun_masuk'), function ($query, $tahunMasuk) {
                return $query->where('tahun_masuk', $tahunMasuk);
            })
            ->orderBy('nama', 'asc')
            ->get(['mahasiswa_id', 'nama', 'nim']); // Sesuaikan dengan struktur tabel

        return response()->json($mahasiswa);
    }

    public function updatePembayaran(Request $request, $id)
    {
        $tagihan = TagihanMahasiswa::findOrFail($id);

        $request->validate([
            'total_pembayaran' => 'required|numeric|min:0|max:' . $tagihan->jumlah_tagihan,
        ]);

        $tagihan->total_pembayaran = $request->total_pembayaran;
        $tagihan->status = $request->total_pembayaran >= $tagihan->jumlah_tagihan ? "lunas" : "belum_lunas";
        $tagihan->save();

        return response()->json(['message' => 'Pembayaran berhasil diperbarui']);
    }


}