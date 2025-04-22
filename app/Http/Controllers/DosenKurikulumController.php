<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Kurikulum;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use App\Models\DosenMatakuliah;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class DosenKurikulumController extends Controller
{

  public function index(Request $request)
{
    $dosens = Dosen::all();
    $programStudi = ProgramStudi::all();
    $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();

    // Query kurikulums
    $kurikulums = Kurikulum::with(['matakuliah', 'tahunAjaran'])
        ->whereHas('tahunAjaran', function ($query) {
            $query->where('status_ta', 1);
        })
        ->select('kurikulum_id', 'matakuliah_id', 'ta_id')
        ->get();

    $search = $request->input('search');

    // Query assigned data dengan filter pencarian
    $assignedData = DosenMataKuliah::with(['dosen', 'kurikulum.matakuliah.programStudi'])
        ->when($search, function ($query) use ($search) {
            $query->whereHas('kurikulum.matakuliah', function ($q) use ($search) {
                $q->where('matakuliah_id', 'like', "%{$search}%")
                  ->orWhereHas('programStudi', function ($p) use ($search) {
                      $p->where('nama', 'like', "%{$search}%");
                  });
            });
        })
        ->paginate(10);

    return view('kurikulum.assign-dosen', compact('dosens', 'kurikulums', 'assignedData', 'tahunAjaran','programStudi'))
        ->with('i', (request()->input('page', 1) - 1) * 10);
}

public function filter(Request $request)
{
    $programStudi = $request->input('programStudi');
    $semester = $request->input('semester');
    $jenisKelas = $request->input('jenis_kelas');

    $assignedData = DosenMataKuliah::with(['dosen', 'kurikulum.matakuliah'])
        ->whereHas('kurikulum.matakuliah', function ($query) use ($programStudi, $semester, $jenisKelas) {
            if ($programStudi) {
                $query->where('jurusan_id', $programStudi);
            }
            if ($semester) {
                $query->where('smt', $semester);
            }
            if ($jenisKelas) {
                $query->where('jenis_kelas', $jenisKelas);
            }
        })
        ->get();

    if ($assignedData->isEmpty()) {
        return response()->json(['message' => 'Tidak ada data yang ditemukan.'], 200);
    }

    return response()->json($assignedData);
}
public function store(Request $request)
{
    $request->validate([
        'dosen_id' => 'required',
        'kurikulum_id' => 'required',
        'jenis_dosen' => 'required',
        'jenis_kelas' => 'required',
    ]);

    DosenMataKuliah::create([
        'dosen_id' => $request->dosen_id,
        'kurikulum_id' => $request->kurikulum_id,
        'jenis_dosen' => $request->jenis_dosen,
        'jenis_kelas' => $request->jenis_kelas,
    ]);

    return response()->json(['message' => 'Data berhasil ditambahkan!']);
}

    public function assign(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'dosen_id' => 'required|exists:dosen,dosen_id',
            'kurikulum_id' => 'required|exists:kurikulum,kurikulum_id',
        ]);

        try {
            // Mencari dosen berdasarkan ID
            $dosen = Dosen::findOrFail($validated['dosen_id']);

            // Assign dosen ke kurikulum
            $dosen->kurikulums()->attach($validated['kurikulum_id']);

            // Menampilkan notifikasi sukses
            Alert::toast('Dosen berhasil diassign ke kurikulum.', 'success')
                ->position('bottom-end')
                ->autoClose(3000);

            // Redirect kembali ke halaman sebelumnya
            return redirect()->back();
        } catch (\Exception $e) {
            // Jika terjadi error atau kegagalan input
            Alert::toast('Gagal menambahkan dosen ke kurikulum. Error: ' . $e->getMessage(), 'error')
                ->position('bottom-end')
                ->autoClose(3000);

            // Kembali ke halaman sebelumnya
            return redirect()->back();
        }
    }


    public function getDosens($kurikulumId)
    {
        $kurikulum = Kurikulum::findOrFail($kurikulumId);
        $dosens = $kurikulum->dosens;

        return view('kurikulum.dosens', compact('dosens', 'kurikulum'));
    }
    public function destroy($id)
    {
        try {
            $dosenKurikulum = DosenMataKuliah::find($id);

            // Cek apakah data ada
            if (!$dosenKurikulum) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Hapus data
            $dosenKurikulum->delete();

            return response()->json([
                'success' => true,
                'message' => 'Dosen berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

}
