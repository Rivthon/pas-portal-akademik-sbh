<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;

use App\Models\Ruangan;
use App\Models\Kurikulum;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;

class KurikulumController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:kurikulum-list|kurikulum-create|kurikulum-edit|kurikulum-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:kurikulum-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:kurikulum-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:kurikulum-delete', ['only' => ['destroy']]);
    }
   public function index()
    {
        try {
            // Ambil tahun ajaran yang statusnya aktif
            $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();

            if (!$tahunAjaran) {
                return redirect()->back()->with('error', 'Tidak ada tahun ajaran yang aktif.');
            }

            // Ambil semua program studi
            $programStudi = ProgramStudi::all();
            $mataKuliah = Matakuliah::orderBy('smt')->orderBy('nama')->get();

            if ($programStudi->isEmpty()) {
                return redirect()->back()->with('error', 'Data program studi tidak tersedia.');
            }

            return view('admin.akademik.kurikulum.index', compact('programStudi', 'tahunAjaran', 'mataKuliah'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan pada server.');
        }
    }

    public function filter(Request $request)
    {
        try {
            $programStudi = $request->query('programStudi');
            $semester = $request->query('semester');

            if (!$programStudi || !$semester) {
                return response()->json(['message' => 'Program studi dan semester diperlukan.'], 400);
            }

            // Ambil tahun ajaran yang statusnya aktif
            $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();

            if (!$tahunAjaran) {
                return response()->json(['message' => 'Tidak ada tahun ajaran yang aktif.'], 404);
            }

            // Ambil data Kurikulum berdasarkan program studi & semester
            $kurikulums = Kurikulum::select(
                    'kurikulum.*',
                    'matakuliah.nama as nama_matakuliah',
                    'matakuliah.smt as semester',
                    // 'ruangan.nama as nama_ruangan'
                )
                ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                ->where('kurikulum.jurusan_id', $programStudi)
                ->where('kurikulum.ta_id', $tahunAjaran->ta_id) // Tahun ajaran aktif
                ->where('matakuliah.smt', $semester) // Filter berdasarkan semester dari matakuliah
                ->get();

            if ($kurikulums->isEmpty()) {
                return response()->json(['message' => 'Tidak ada data kurikulum yang ditemukan untuk program studi dan semester ini.'], 404);
            }

            return response()->json($kurikulums);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan pada server.', 'error' => $e->getMessage()], 500);
        }
    }


    // public function index(Request $request)
    // {
    //     $search = $request->input('search');
    //     $activeTA = TahunAkademik::where('status_ta', 1)->first(); // Ambil Tahun Ajaran Aktif

    //     // Pastikan ada tahun ajaran aktif
    //     if (!$activeTA) {
    //         if ($request->ajax()) {
    //             return response()->json([
    //                 'error' => 'Tidak ada Tahun Ajaran yang aktif.'
    //             ], 422);
    //         }
    //         return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran yang aktif.');
    //     }

    //     // Query awal dengan eager loading
    //     $query = Kurikulum::with('programStudi', 'mataKuliah')
    //     ->where('ta_id', $activeTA->ta_id);

    //     // Filter berdasarkan pencarian
    //     if ($search) {
    //         $query->where(function ($q) use ($search) {
    //             $q->whereHas('programStudi', function ($q) use ($search) {
    //                 $q->where('nama', 'like', '%' . $search . '%'); // Filter nama program studi
    //             })
    //                 ->orWhereHas('mataKuliah', function ($q) use ($search) {
    //                     $q->where('nama', 'like', '%' . $search . '%'); // Filter nama mata kuliah
    //                 })
    //                 ->orWhere('matakuliah_id', 'like', '%' . $search . '%'); // Filter berdasarkan matakuliah_id
    //         });
    //     }

    //     // Pagination
    //     $kurikulums = $query->latest()->paginate(10)->appends($request->query());

    //     // Jika request adalah AJAX, kirim response JSON
    //     if ($request->ajax()) {
    //         return response()->json([
    //             'html' => view('kurikulum.partials_list', compact('kurikulums'))->render()
    //         ]);
    //     }

    //     // Jika bukan AJAX, kirim ke view utama
    //     return view('kurikulum.index', compact('kurikulums'))
    //     ->with('i', (request()->input('page', 1) - 1) * 10);
    // }



    public function create(Request $request)
    {
        $programStudi = ProgramStudi::all();
        $mataKuliah = Matakuliah::all();
        $ruangan = Ruangan::all();
         // Ambil Tahun Akademik dengan status_ta = 1
        $tahunAjaranAktif = TahunAkademik::where('status_ta', 1)->first();

        if (!$tahunAjaranAktif) {
            return redirect()->route('admin.kurikulum.index')->with('error', 'Tahun Ajaran aktif tidak ditemukan.');
        }
        $kurikulum = new Kurikulum(); // Untuk form create
        return view('admin.akademik.kurikulum.form', compact('kurikulum', 'programStudi', 'mataKuliah', 'ruangan', 'tahunAjaranAktif'));
    }



    public function edit(Request $request, $id)
    {
        // Ambil data Program Studi, Matakuliah, dan Ruangan
        $programStudi = ProgramStudi::all();
        $mataKuliah = Matakuliah::all();
        // $ruangan = Ruangan::all();

        // Ambil Tahun Akademik dengan status_ta = 1
        $tahunAjaranAktif = TahunAkademik::where('status_ta', 1)->first();

        if (!$tahunAjaranAktif) {
            return redirect()->route('admin.kurikulum.index')
                ->with('error', 'Tahun Ajaran aktif tidak ditemukan.');
        }

        // Cari data kurikulum berdasarkan ID
        $kurikulum = Kurikulum::findOrFail($id);

        // Kembalikan view dengan data yang dibutuhkan
        return view('admin.akademik.kurikulum.form', compact(
            'kurikulum',
            'programStudi',
            'mataKuliah',
            // 'ruangan',
            'tahunAjaranAktif'
        ));
    }



    /**
     * Simpan multiple mata kuliah sekaligus via AJAX
     */
    public function storeMultiple(Request $request)
    {
        $request->validate([
            'matakuliah_ids'   => 'required|array|min:1',
            'matakuliah_ids.*' => 'required|string',
            'ta_id'            => 'required|string',
        ]);

        $taId = $request->ta_id;
        $matakuliahIds = $request->matakuliah_ids;
        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($matakuliahIds as $mkId) {
            try {
                $matakuliah = Matakuliah::find($mkId);
                if (!$matakuliah) {
                    $errors[] = "Matakuliah {$mkId} tidak ditemukan.";
                    continue;
                }

                // Cek apakah sudah ada di kurikulum (hindari duplikat)
                $exists = Kurikulum::where('matakuliah_id', $mkId)
                    ->where('ta_id', $taId)
                    ->where('jurusan_id', $matakuliah->jurusan_id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                Kurikulum::create([
                    'matakuliah_id' => $mkId,
                    'ta_id'         => $taId,
                    'jurusan_id'    => $matakuliah->jurusan_id,
                ]);

                $created++;
            } catch (\Exception $e) {
                $errors[] = "Gagal menyimpan {$mkId}: " . $e->getMessage();
            }
        }

        $message = "{$created} mata kuliah berhasil ditambahkan.";
        if ($skipped > 0) {
            $message .= " {$skipped} dilewati (sudah ada).";
        }
        if (count($errors) > 0) {
            $message .= " " . count($errors) . " gagal.";
        }

        return response()->json([
            'success' => $created > 0 || $skipped > 0,
            'message' => $message,
            'created' => $created,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'matakuliah_id' => 'required|string',
            'ta_id'         => 'required|string',
        ]);

        try {
            // Ambil jurusan_id berdasarkan matakuliah_id yang dipilih
            $matakuliah = Matakuliah::find($request->matakuliah_id);
            if (!$matakuliah) {
                return back()->withErrors(['error' => 'Matakuliah tidak ditemukan.'])->withInput();
            }

            // Ambil jurusan_id dari matakuliah yang dipilih
            $jurusan_id = $matakuliah->jurusan_id;

            // Menambahkan jurusan_id ke data yang akan disimpan
            $validated['jurusan_id'] = $jurusan_id;

            // Simpan data kurikulum ke database
            Kurikulum::create($validated);

            // Berikan toast alert sukses di tengah layar
            Alert::toast('Kurikulum berhasil ditambahkan.', 'success')
                ->position('center')
                ->autoClose(3000);

            return redirect()->route('admin.kurikulum.index');
        } catch (\Exception $e) {
            Alert::toast('Gagal menyimpan kurikulum. Silakan coba lagi.', 'error')
                ->position('center')
                ->autoClose(3000);

            return back()->withErrors(['error' => 'Gagal menyimpan kurikulum. Silakan coba lagi.'])->withInput();
        }
    }




    public function update(Request $request, $id)
    {
        // Validasi input
        $validated = $request->validate([
            'matakuliah_id' => 'required|string',
            'ta_id'         => 'required|string',
            // 'ruangan_id'    => 'required|string',
            // 'jam_mulai'     => 'required|date_format:H:i',
            // 'jam_selesai'   => 'required|date_format:H:i|after:jam_mulai',
        ]);

        try {
            // Cari kurikulum berdasarkan ID
            $kurikulum = Kurikulum::findOrFail($id);

            // Ambil jurusan_id berdasarkan matakuliah_id yang dipilih
            $matakuliah = Matakuliah::find($request->matakuliah_id);
            if (!$matakuliah) {
                return back()->withErrors(['error' => 'Matakuliah tidak ditemukan.'])->withInput();
            }

            // Ambil jurusan_id dari matakuliah yang dipilih
            $jurusan_id = $matakuliah->jurusan_id;

            // Menambahkan jurusan_id ke data yang akan diperbarui
            $validated['jurusan_id'] = $jurusan_id;

            // Update data kurikulum di database
            $kurikulum->update($validated);

            // Berikan toast alert sukses di tengah layar
            Alert::toast('Kurikulum berhasil diperbarui.', 'success')
                ->position('center')  // Menampilkan alert di tengah layar
                ->autoClose(3000);

            return redirect()->route('admin.kurikulum.index'); // Redirect ke route admin.kurikulum.index
        } catch (\Exception $e) {
            // Tangani kesalahan dan tampilkan pesan error dengan alert
            Alert::toast('Gagal memperbarui kurikulum. Silakan coba lagi.', 'error')
                ->position('center')  // Menampilkan alert di tengah layar
                ->autoClose(3000);

            // Kembalikan ke halaman sebelumnya dengan input yang sudah diisi
            return back()->withErrors(['error' => 'Gagal memperbarui kurikulum. Silakan coba lagi.'])->withInput();
        }
    }

    public function destroy(Kurikulum $kurikulum): RedirectResponse
    {
        $kurikulum->delete();

        Alert::toast('kurikulum berhasil dihapus.', 'info')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        return redirect()->route('admin.kurikulum.index');
    }
}