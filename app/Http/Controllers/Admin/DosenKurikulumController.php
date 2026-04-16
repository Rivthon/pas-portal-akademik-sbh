<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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
    $tahunAjaranList = TahunAkademik::orderBy('ta_id', 'desc')->get();
    $tahunAjaranAktif = TahunAkademik::where('status_ta', 1)->first();

    $search = $request->input('search');

    // Query assigned data dengan filter pencarian
    $assignedData = DosenMataKuliah::with(['dosen', 'kurikulum.mataKuliah.programStudi'])
        ->when($search, function ($query) use ($search) {
            $query->whereHas('kurikulum.mataKuliah', function ($q) use ($search) {
                $q->where('matakuliah_id', 'like', "%{$search}%")
                  ->orWhereHas('programStudi', function ($p) use ($search) {
                      $p->where('nama', 'like', "%{$search}%");
                  });
            });
        })
        ->paginate(10);

    return view('kurikulum.assign-dosen', compact('dosens', 'assignedData', 'tahunAjaranList', 'tahunAjaranAktif', 'programStudi'))
        ->with('i', (request()->input('page', 1) - 1) * 10);
}

  public function getKurikulumByTA($taId)
  {
      $kurikulums = Kurikulum::with(['mataKuliah'])
          ->where('ta_id', $taId)
          ->whereHas('mataKuliah')
          ->select('kurikulum_id', 'matakuliah_id', 'ta_id')
          ->get()
          ->map(function ($k) {
              return [
                  'kurikulum_id' => $k->kurikulum_id,
                  'matakuliah_id' => $k->matakuliah_id,
                  'nama' => $k->mataKuliah->nama ?? '-',
                  'smt' => $k->mataKuliah->smt ?? '-',
                  'semester' => $k->mataKuliah->semester ?? '-',
              ];
          });

      return response()->json($kurikulums);
  }

public function filter(Request $request)
{
    $programStudi = $request->input('programStudi');
    $semester = $request->input('semester');
    $tahunAjaran = $request->input('tahunAjaran');

    $assignedData = DosenMataKuliah::with(['dosen', 'kurikulum.mataKuliah'])
        ->whereHas('kurikulum', function ($query) use ($programStudi, $semester, $tahunAjaran) {
            // Filter berdasarkan tahun ajaran
            if ($tahunAjaran) {
                $query->where('ta_id', $tahunAjaran);
            }
            // Filter berdasarkan program studi dan semester via matakuliah
            $query->whereHas('mataKuliah', function ($q) use ($programStudi, $semester) {
                if ($programStudi) {
                    $q->where('jurusan_id', $programStudi);
                }
                if ($semester) {
                    $q->where('smt', $semester);
                }
            });
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
