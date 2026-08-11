<?php

namespace App\Http\Controllers\Admin\Kemahasiswaan;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\PengajuanTranskrip;
use App\Models\Setting;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;
use RealRashid\SweetAlert\Facades\Alert;

class PengajuanTranskripController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:pengajuan-transkrip-list', ['only' => ['indexTransrkip']]);
        $this->middleware('permission:pengajuan-transkrip-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:pengajuan-transkrip-status', ['only' => ['updateStatus']]);
        $this->middleware('permission:pengajuan-transkrip-delete', ['only' => ['destroy']]);
    }

    // Menampilkan daftar pengajuan (untuk admin & mahasiswa)
    public function index()
    {
        $pengajuanTerakhir = PengajuanTranskrip::where('mahasiswa_id', auth()->id())
            ->latest()
            ->first();

        $pengajuan = PengajuanTranskrip::with('mahasiswa')->latest()->get();

        return view('mahasiswa.pengajuan.index', compact('pengajuan', 'pengajuanTerakhir'));
    }

    public function indexTransrkip(Request $request)
    {
        $query = PengajuanTranskrip::with('mahasiswa')->latest();

        // Filter pencarian jika ada
        if ($request->ajax()) {
            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->whereHas('mahasiswa', function ($q) use ($search) {
                    $q->where('nama', 'like', '%'.$search.'%');
                });
            }

            $pengajuan = $query->paginate(10);

            return view('admin.kemahasiswaan.pengajuan-transkrip.table', compact('pengajuan'))->render(); // partial view
        }

        // Initial load
        $pengajuan = $query->paginate(10);

        return view('admin.kemahasiswaan.pengajuan-transkrip.index', compact('pengajuan'));
    }

    // Menampilkan form pengajuan transkrip
    public function create()
    {
        return view('mahasiswa.pengajuan.form');
    }

    // Menyimpan pengajuan baru
    public function store(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (! $mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $request->validate([
            'jenis' => 'required|in:sementara,akhir',
            'keperluan' => 'nullable|string|max:255',
            'bukti' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $data = [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'jenis' => $request->jenis,
            'keperluan' => $request->keperluan,
            'status' => 'pending',
        ];

        if ($request->hasFile('bukti')) {
            $data['bukti'] = $request->file('bukti')->store('bukti_pengajuan', 'public');
        }

        PengajuanTranskrip::create($data);

        Alert::success('Pengajuan berhasil dikirim', 'Pengajuan Anda telah berhasil disimpan.');

        return redirect()->route('mahasiswa.pengajuan.index')->with('success', 'Pengajuan berhasil dikirim.');
    }

    // Menampilkan detail pengajuan
    public function show(PengajuanTranskrip $pengajuan)
    {
        return redirect()->route('mahasiswa.pengajuan.index');
    }

    // Menampilkan form edit status (untuk admin)
    public function edit(PengajuanTranskrip $pengajuan)
    {
        return view('admin.kemahasiswaan.pengajuan-transkrip.edit', compact('pengajuan'));
    }

    // Memproses perubahan status pengajuan
    public function update(Request $request, PengajuanTranskrip $pengajuan)
    {
        $request->validate([
            'status' => 'required|in:pending,disetujui,diproses,selesai,ditolak',
            'catatan' => 'nullable|string|max:255',
        ]);

        $pengajuan->update($request->only(['status', 'catatan']));

        activity_log('update_pengajuan_transkrip', 'Admin memperbarui pengajuan transkrip ID: '.$pengajuan->id.' status: '.$request->status);

        Alert::success('Status Diperbarui', 'Status pengajuan berhasil diperbarui.');

        return redirect()->route('admin.transkrip.index')->with('success', 'Status pengajuan berhasil diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,disetujui,diproses,selesai,ditolak',
        ]);

        $pengajuan = PengajuanTranskrip::findOrFail($id);

        if ($pengajuan->status === $request->status) {
            Alert::info('info', 'Status pengajuan sudah dalam kondisi yang sama');

            return back()->with('info', 'Status pengajuan sudah dalam kondisi yang sama.');
        }

        $pengajuan->update(['status' => $request->status]);

        activity_log('update_status_pengajuan', 'Admin mengubah status pengajuan transkrip ID: '.$id.' menjadi '.$request->status);

        Alert::success('Status Diperbarui', 'Status pengajuan berhasil diperbarui.');

        return back()->with('success', 'Status pengajuan berhasil diperbarui.');
    }

    // Menghapus pengajuan (opsional)
    public function destroy(PengajuanTranskrip $pengajuan)
    {
        try {
            activity_log('hapus_pengajuan_transkrip', 'Admin menghapus pengajuan transkrip ID: '.$pengajuan->id);
            $pengajuan->delete();
            Alert::success('Pengajuan Dihapus', 'Pengajuan berhasil dihapus.');

            return redirect()->route('admin.transkrip.index')->with('success', 'Pengajuan berhasil dihapus.');
        } catch (\Exception $e) {
            Alert::error('Gagal Menghapus', 'Terjadi kesalahan saat menghapus pengajuan.');

            return redirect()->route('admin.transkrip.index')->with('error', 'Terjadi kesalahan saat menghapus pengajuan.');
        }
    }

    public function downloadTranskrip()
    {
        $settings = Setting::first();
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (! $mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $mahasiswaId = $mahasiswa->mahasiswa_id;
        $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);

        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/'.$settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }

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
            // Ambil KHS Semester Ini
            $khs = Krs::with(['kurikulum.mataKuliah'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->get()
                ->filter(fn ($item) => $item->khs !== null); // Filter hanya KHS yang tidak kosong

            // Cek apakah ada KHS yang belum diisi
            if ($khs->isEmpty() || $khs->contains(fn ($item) => $item->khs === null)) {
                // Jika ada KHS yang belum diisi, kembalikan respons atau lakukan tindakan lain
                return response()->json(['message' => 'Ada KHS yang belum diisi.'], 400);
            }

            // Generate PDF
            $pdf = PDF::loadView('mahasiswa.pengajuan.pdf', compact(
                'khs', 'mahasiswa', 'ta', 'logoBase64', 'headerColor', 'textColor'
            ))->setPaper('a4', 'portrait');

            return $pdf->stream('khs-'.$mahasiswa->nama.'.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data KHS: '.$e->getMessage());
        }
    }
}
