<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Permintaan;
use App\Models\Setting;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class PermintaanController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $semester = $mahasiswa->semester;
        $prodi = $mahasiswa->jurusan_id;
        $settings = Setting::first();
        $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });

        $permintaanTerakhir = Permintaan::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->latest()
            ->first();
        $permintaan = Permintaan::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->latest()
            ->get();

        activity_log('kelola_permintaan', 'Mahasiswa melihat daftar permintaan');

        return view('mahasiswa.permintaan.index', compact(
            'permintaan',
            'permintaanTerakhir',
            'settings',
            'mahasiswa',
            'semester',
            'prodi',
            'ta'
        ));
    }

    public function create()
    {
        return view('mahasiswa.permintaan.form');
    }

    public function store(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (! $mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $validated = $request->validate([
            'jenis_permintaan' => 'required|in:bug,fitur,akses,lainnya',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'prioritas' => 'required|in:rendah,sedang,tinggi,urgen',
            'file_lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,zip|max:2048',
        ]);

        if ($request->hasFile('file_lampiran')) {
            $validated['file_lampiran'] = $request->file('file_lampiran')
                ->store('lampiran_permintaan', 'public');
        }

        $validated['mahasiswa_id'] = $mahasiswa->mahasiswa_id;
        $validated['dosen_id'] = null;
        $validated['status'] = 'menunggu';

        Permintaan::create($validated);

        activity_log('buat_permintaan', 'Mahasiswa membuat permintaan baru: '.$validated['judul']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Permintaan berhasil dikirim langsung ke Helpdesk Admin.',
                'redirect_url' => route('mahasiswa.permintaan.index'),
            ]);
        }

        return redirect()->route('mahasiswa.permintaan.index')
            ->with('success', 'Permintaan berhasil dikirim langsung ke Helpdesk Admin.');
    }

    public function show(Permintaan $permintaan)
    {
        abort_unless(
            (int) $permintaan->mahasiswa_id === (int) Auth::guard('mahasiswa')->id(),
            403
        );

        return view('permintaan.show', compact('permintaan'));
    }

    public function edit(Permintaan $permintaan)
    {
        return view('permintaan.edit', compact('permintaan'));
    }

    public function update(Request $request, Permintaan $permintaan)
    {
        $request->validate([
            'status' => 'required|in:menunggu,disetujui,ditolak,revisi,selesai',
            'komentar_admin' => 'nullable|string|max:255',
        ]);

        $permintaan->update($request->only(['status', 'komentar_admin']));

        Alert::success('Status Diperbarui', 'Permintaan berhasil diperbarui.');

        return redirect()->route('admin.permintaan.index');
    }

    public function destroy($id)
    {
        $permintaan = Permintaan::where('mahasiswa_id', Auth::guard('mahasiswa')->id())
            ->findOrFail($id);

        if ($permintaan->file_lampiran) {
            Storage::disk('public')->delete($permintaan->file_lampiran);
        }

        $permintaan->delete();
        Alert::success('Berhasil', 'Permintaan berhasil dihapus');

        return redirect()->back();
    }
}
