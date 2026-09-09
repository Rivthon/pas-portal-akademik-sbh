<?php

namespace App\Http\Controllers\Admin\Kemahasiswaan;

use App\Exports\MahasiswaExport;
use App\Exports\MahasiswaTemplateCepatExport;
use App\Http\Controllers\Controller;
use App\Imports\MahasiswaImport;
use App\Imports\MahasiswaImportCepat;
use App\Models\Dosen;
use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\DataTables;

class MahasiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:mahasiswa-list|mahasiswa-create|mahasiswa-edit|mahasiswa-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:mahasiswa-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:mahasiswa-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:mahasiswa-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        // Jika request adalah AJAX, maka jalankan pencarian
        if ($request->ajax()) {
            return $this->searchMahasiswa($request);
        }
        $dosen = Dosen::all();
        // Jika bukan AJAX, tampilkan halaman utama dengan data awal
        $programStudi = ProgramStudi::all();

        return view('admin.kemahasiswaan.mahasiswa.index', compact('programStudi', 'dosen'));
    }

    private function searchMahasiswa(Request $request)
    {
        // Ambil input pencarian
        $search = $request->input('search');
        $programStudi = $request->input('jurusan_id');
        $tahunMasuk = $request->input('tahun_masuk');
        $status = $request->input('status');
        $kelas = $request->input('kelas');
        $dosen = Dosen::all();
        // Query mahasiswa dengan relasi program studi
        $query = Mahasiswa::with('programStudi');

        // Jika ada input pencarian umum
        if ($search) {
            $keywords = explode(' ', $search);
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('nama', 'like', '%'.$keyword.'%')
                        ->orWhere('nim', 'like', '%'.$keyword.'%')
                        ->orWhere('tahun_masuk', 'like', '%'.$keyword.'%')
                        ->orWhere('status_mhs', 'like', '%'.$keyword.'%')
                        ->orWhereHas('programStudi', function ($subQuery) use ($keyword) {
                            $subQuery->where('nama', 'like', '%'.$keyword.'%');
                        });
                }
            });
        }

        // Jika ada filter program studi
        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $programStudi);
        }

        // Jika ada filter tahun masuk
        if ($request->filled('tahun_masuk')) {
            $query->where('tahun_masuk', $tahunMasuk);
        }

        // Jika ada filter status mahasiswa
        if ($request->filled('status')) {
            $query->where('status_mhs', $status);
        }

        if ($request->filled('kelas')) {
            $kelas === 'pagi'
                ? $query->whereIn('kelas', ['pagi', 'reguler', 'regular'])
                : $query->where('kelas', 'karyawan');
        }

        // Mahasiswa lulus ditempatkan setelah mahasiswa aktif/non-lulus.
        $mahasiswa = $query->orderByRaw("CASE WHEN status_mhs = 'lulus' THEN 1 WHEN status_mhs = 'nonaktif' THEN 2 ELSE 0 END")->orderBy('nama')->paginate(15)->withQueryString();

        // Kembalikan tabel hasil pencarian tanpa pagination
        return response()->json([
            'html' => view('admin.kemahasiswaan.mahasiswa.partials_list', compact('mahasiswa', 'dosen'))->render(),
            'pagination' => $mahasiswa->links('pagination::bootstrap-4')->render(),
        ]);
    }

    public function exportExcel(Request $request)
    {
        // Ambil filter dari request (sama dengan searchMahasiswa)
        $search = $request->input('search');
        $programStudi = $request->input('jurusan_id');
        $tahunMasuk = $request->input('tahun_masuk');
        $status = $request->input('status');
        $kelas = $request->input('kelas');

        // Simpan semua filter dalam array
        $filters = compact('search', 'programStudi', 'tahunMasuk', 'status', 'kelas');

        try {
            // Export ke Excel
            return Excel::download(new MahasiswaExport($filters), 'data_mahasiswa.xlsx');
        } catch (\Exception $e) {
            Log::error('Gagal export data mahasiswa: '.$e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Gagal mengekspor data mahasiswa. Silakan coba lagi.');
        }
    }

    public function create(): View
    {
        $programStudi = ProgramStudi::all();
        $dosen = Dosen::orderBy('nama')->get(['dosen_id', 'nama']);

        return view('admin.kemahasiswaan.mahasiswa.create', compact('programStudi', 'dosen'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|unique:mahasiswa,email',
            'jurusan_id' => 'required|exists:program_studi,jurusan_id',
            'nim' => 'required|unique:mahasiswa,nim',
            'kelas' => 'required|in:pagi,karyawan',
            'dosen_id' => 'nullable|exists:dosen,dosen_id',
        ]);

        $nim = trim($request->nim);

        Mahasiswa::create([
            'nama' => $request->nama,
            'email' => $request->filled('email') ? $request->email : $nim.'@mahasiswa.sbh.ac.id',
            'password' => bcrypt($nim),
            'jurusan_id' => $request->jurusan_id,
            'nim' => $request->nim,
            'nama_ibu' => '', 'nama_ayah' => '', 'jenis_kelamin' => '', 'agama' => '', 'alamat' => '',
            'no_telp' => '', 'no_telp_ortu' => '', 'no_telp_ibu' => '', 'alamat_ortu' => '',
            'asal_sekolah' => '', 'jurusan_sekolah' => '', 'tahun_lulus' => '', 'tahun_masuk' => 0,
            'gelombang_id' => 0, 'pendapatan_ortu' => '', 'semester' => 1, 'status_mhs' => 'aktif',

            'kelas' => $request->kelas,
            'dosen_id' => $request->dosen_id,
        ]);

        activity_log('tambah_mahasiswa', 'Admin menambah mahasiswa baru: '.$request->nama.' (NIM: '.$request->nim.')');

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Mahasiswa created successfully.');
    }

    public function show(Mahasiswa $mahasiswa): View
    {
        return view('admin.kemahasiswaan.mahasiswa.show', compact('mahasiswa'));
    }

    public function edit(Mahasiswa $mahasiswa): View
    {
        $gelombang = Gelombang::all();
        $programStudi = ProgramStudi::all();
        $dosen = Dosen::all();

        return view('admin.kemahasiswaan.mahasiswa.edit', compact('mahasiswa', 'programStudi', 'gelombang', 'dosen'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        try {
            // Validasi data
            $request->validate([
                'nama' => 'required|string|max:255',
                'email' => 'required|email|unique:mahasiswa,email,'.$mahasiswa->mahasiswa_id.',mahasiswa_id',
                'jurusan_id' => 'required|exists:program_studi,jurusan_id',
                // 'nisn' => 'nullable|numeric|digits:10',
                // 'nik' => 'nullable|numeric|digits:16',
                'nama_ibu' => 'nullable|string|max:255',
                'nama_ayah' => 'nullable|string|max:255',
                'jenis_kelamin' => 'required|in:Laki-Laki,Perempuan', // Pastikan ada aturan in: untuk validasi pilihan
                'tanggal_lahir' => 'nullable|date',
                'tempat_lahir' => 'nullable|string|max:255',
                'agama' => 'nullable|string|max:50',
                'alamat' => 'required|string|max:500',
                'kota' => 'nullable|string|max:255',
                'no_telp' => 'nullable|numeric|digits_between:10,15',
                'no_telp_ortu' => 'nullable|numeric|digits_between:10,15',
                'alamat_ortu' => 'nullable|string|max:500',
                'semester' => 'required|integer|min:1|max:8',
                'asal_sekolah' => 'nullable|string|max:255',
                'tahun_masuk' => 'required|integer|min:2000|max:'.date('Y'),
                'kelas' => 'required|in:pagi,karyawan',
                'status_mhs' => 'required|string|in:aktif,nonaktif,lulus,dropout,cuti',
                'pendapatan_ortu' => 'nullable|string',
                'password' => 'nullable|string|min:8',
                'dosen_id' => 'required|exists:dosen,dosen_id',

            ]);

            // Data untuk pembaruan
            $data = $request->only([
                'nama',
                'email',
                'jurusan_id',
                'nim',
                'nisn',
                'nik',
                'nama_ibu',
                'nama_ayah',
                'jenis_kelamin',
                'tanggal_lahir',
                'tempat_lahir',
                'agama',
                'alamat',
                'kota',
                'no_telp',
                'no_telp_ortu',
                'alamat_ortu',
                'semester',
                'asal_sekolah',
                'tahun_masuk',
                'gelombang_id',
                'dosen_id',
                'kelas',
                'status_mhs',
            ]);

            // Periksa apakah password diisi
            // if ($request->filled('password')) {
            //     $data['password'] = Hash::make($request->password);
            // }

            // Update data mahasiswa
            $mahasiswa->update($data);

            activity_log('update_mahasiswa', 'Admin memperbarui data mahasiswa: '.$mahasiswa->nama.' (NIM: '.$mahasiswa->nim.')');

            // Notifikasi sukses
            Alert::toast('Mahasiswa berhasil ditambahkan.', 'success')
                ->position('bottom-end')
                ->autoClose(3000);
        } catch (ValidationException $e) {
            // Log error untuk debugging
            Log::error('Validasi gagal: '.$e->getMessage());

            // Notifikasi error
            Alert::error('Gagal', 'Terjadi kesalahan saat memperbarui data mahasiswa');

            // Redirect dengan error input
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Log error lainnya
            Log::error('Error saat memperbarui data mahasiswa: '.$e->getMessage());

            // Notifikasi error
            Alert::error('Gagal', 'Terjadi kesalahan tak terduga'.$e->getMessage());

            // Redirect kembali
            return redirect()->back()->withInput();
        }

        // Redirect ke halaman indeks mahasiswa
        return redirect()->route('admin.mahasiswa.index');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        activity_log('hapus_mahasiswa', 'Admin menghapus mahasiswa: '.$mahasiswa->nama.' (NIM: '.$mahasiswa->nim.')');
        $mahasiswa->delete();
        Alert::toast('Mahasiswa berhasil dihapus.', 'info')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.mahasiswa.index');
    }

    public function resetPassword($id)
    {
        // Cari mahasiswa berdasarkan ID
        $mahasiswa = Mahasiswa::findOrFail($id);

        // Gunakan NIM sebagai password baru
        $newPassword = $mahasiswa->nim; // Gunakan NIM sebagai password baru (atau logika lain yang Anda inginkan)

        // Update password mahasiswa
        $mahasiswa->password = Hash::make($newPassword);
        $mahasiswa->save();

        // Menampilkan alert sukses
        activity_log('reset_password_mahasiswa', 'Admin mereset password mahasiswa: '.$mahasiswa->nama.' (NIM: '.$mahasiswa->nim.')');
        Alert::toast('Password Mahasiswa berhasil direset menjadi NIM.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        // Redirect ke halaman daftar mahasiswa
        return redirect()->route('admin.mahasiswa.index');
    }

    public function updateStatus($id, Request $request)
    {

        try {
            $request->validate([
                'status_mhs' => 'required|in:aktif,nonaktif,lulus,dropout,cuti',
            ]);

            $mahasiswa = Mahasiswa::findOrFail($id);
            $mahasiswa->update(['status_mhs' => $request->status_mhs]);

            activity_log('update_status_mahasiswa', 'Admin mengubah status mahasiswa ID: '.$id.' menjadi '.$request->status_mhs);

            return response()->json([
                'message' => 'Status mahasiswa berhasil diperbarui!',
                'status_mhs' => $request->status_mhs,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error Update Status Mahasiswa: '.$e->getMessage());

            return response()->json([
                'message' => 'Gagal memperbarui status. '.$e->getMessage(),
            ], 500);
        }

    }

    public function updateDosen($id, Request $request)
    {
        try {
            $request->validate([
                'dosen_id' => 'nullable|exists:dosen,dosen_id',
            ]);

            $mahasiswa = Mahasiswa::findOrFail($id);
            $mahasiswa->update(['dosen_id' => $request->dosen_id]);

            return response()->json([
                'message' => 'Dosen pembimbing berhasil diperbarui!',
                'dosen_id' => $request->dosen_id,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error Update Dosen Mahasiswa: '.$e->getMessage());

            return response()->json([
                'message' => 'Gagal memperbarui dosen. '.$e->getMessage(),
            ], 500);
        }
    }

    public function updateKelas(Mahasiswa $mahasiswa, Request $request)
    {
        $validated = $request->validate([
            'kelas' => 'required|in:pagi,karyawan',
        ]);

        $mahasiswa->update(['kelas' => $validated['kelas']]);
        activity_log('update_kelas_mahasiswa', 'Admin mengubah kelas mahasiswa '.$mahasiswa->nim.' menjadi '.$mahasiswa->label_kelas);

        return response()->json([
            'message' => 'Kelas '.$mahasiswa->nama.' berhasil diperbarui menjadi '.$mahasiswa->label_kelas.'.',
            'kelas' => $mahasiswa->kelas,
            'label' => $mahasiswa->label_kelas,
        ]);
    }

    public function bulkUpdateKelas(Request $request)
    {
        $validated = $request->validate([
            'mahasiswa_ids' => 'required|array|min:1|max:500',
            'mahasiswa_ids.*' => 'required|integer|distinct|exists:mahasiswa,mahasiswa_id',
            'kelas' => 'required|in:pagi,karyawan',
        ]);

        $updated = Mahasiswa::whereIn('mahasiswa_id', $validated['mahasiswa_ids'])
            ->update(['kelas' => $validated['kelas'], 'updated_at' => now()]);
        $label = $validated['kelas'] === 'karyawan' ? 'Reguler B' : 'Reguler A';

        activity_log('bulk_update_kelas_mahasiswa', 'Admin mengubah kelas '.$updated.' mahasiswa menjadi '.$label);

        return response()->json([
            'message' => $updated.' mahasiswa berhasil diklasifikasikan sebagai '.$label.'.',
            'updated' => $updated,
        ]);
    }

    public function importExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048', // Validasi file Excel
        ]);

        try {
            Excel::import(new MahasiswaImport, $request->file('file'));
            activity_log('import_mahasiswa', 'Admin mengimport data mahasiswa dari file Excel');
            Alert::success('Berhasil', 'Data Mahasiswa berhasil dimport');

            return redirect()->route('admin.mahasiswa.index');
        } catch (\Exception $e) {
            Alert::info('Gagal', 'Gagal Import'.$e->getMessage());

            return redirect()->route('admin.mahasiswa.index');
        }
    }

    public function importExcelCepat(Request $request): RedirectResponse
    {
        $request->validate(['file_cepat' => 'required|mimes:xlsx,xls,csv|max:2048']);

        try {
            Excel::import(new MahasiswaImportCepat, $request->file('file_cepat'));
            return redirect()->route('admin.mahasiswa.index')->with('success', 'Template Cepat berhasil diimport.');
        } catch (\Throwable $e) {
            Log::error('Gagal import template cepat mahasiswa: '.$e->getMessage());
            return redirect()->back()->with('error', 'Import gagal: '.$e->getMessage());
        }
    }

    public function downloadTemplateNew()
    {
        $filePath = public_path('templates/contoh_import_mahasiswa.xlsx');
        $fileName = 'contoh_import_mahasiswa.xlsx'; // Pakai nama asli dulu

        \Log::info('Mengecek file:', ['filePath' => $filePath, 'exists' => file_exists($filePath)]);

        if (file_exists($filePath)) {
            return response()->download($filePath, $fileName);
        }

        return redirect()->back()->with('error', 'File template tidak ditemukan.');
    }

    public function downloadTemplateCepat()
    {
        $filePath = public_path('templates/template-cepat.xlsx');

        if (! file_exists($filePath)) {
            return redirect()->back()->with('error', 'File Template Cepat tidak ditemukan.');
        }

        return response()->download($filePath, 'template-cepat.xlsx');
    }

    public function getMahasiswa()
    {
        $mahasiswa = Mahasiswa::with('programStudi')->select('mahasiswa.*');

        return DataTables::of($mahasiswa)
            ->addIndexColumn()
            ->addColumn('program_studi', fn ($row) => $row->programStudi->name ?? '-')
            ->addColumn('action', fn ($row) => $this->getActionButtons($row))
            ->rawColumns(['action'])
            ->make(true);
    }

    private function getActionButtons($row)
    {
        $editButton = '<a href="'.route('admin.mahasiswa.edit', $row->id).'" class="btn btn-sm btn-primary">Edit</a>';
        $deleteButton = $this->getDeleteButton($row->id);

        return $editButton.' '.$deleteButton;
    }

    private function getDeleteButton($id)
    {
        return '<form action="'.route('admin.mahasiswa.destroy', $id).'" method="POST" style="display:inline;">
                '.csrf_field().method_field('DELETE').'
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</button>
            </form>';
    }

    public function searchDosen(Request $request)
    {
        $term = $request->get('q', '');

        $dosen = Dosen::where('nama', 'LIKE', '%'.$term.'%')
            ->select('dosen_id', 'nama')
            ->limit(20)
            ->get();

        return response()->json($dosen);
    }
}
