<?php

namespace App\Http\Controllers\Admin\Kemahasiswaan;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\PengajuanCuti;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CutiController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'menunggu');
        $taId = $request->integer('ta_id');
        $search = trim((string) $request->input('search'));
        $pengajuan = PengajuanCuti::with(['mahasiswa', 'tahunAkademik', 'programStudi', 'dospem', 'kaprodi', 'baak'])
            ->when($status === 'menunggu', fn ($query) => $query->where('status', PengajuanCuti::MENUNGGU_BAAK))
            ->when($status === 'selesai', fn ($query) => $query->whereIn('status', [PengajuanCuti::DISETUJUI, PengajuanCuti::DITOLAK_BAAK]))
            ->when($status === 'proses', fn ($query) => $query->whereIn('status', [PengajuanCuti::MENUNGGU_DOSPEM, PengajuanCuti::MENUNGGU_KAPRODI]))
            ->when($taId, fn ($query) => $query->where('ta_id', $taId))
            ->when($search !== '', fn ($query) => $query->whereHas('mahasiswa', fn ($mahasiswa) => $mahasiswa
                ->where('nama', 'like', '%'.$search.'%')->orWhere('nim', 'like', '%'.$search.'%')))
            ->latest('diajukan_pada')
            ->paginate(20)
            ->withQueryString();
        $tahunAkademik = TahunAkademik::orderByDesc('ta_id')->get();

        return view('admin.kemahasiswaan.cuti.index', compact('pengajuan', 'tahunAkademik', 'status', 'taId', 'search'));
    }

    public function decide(Request $request, PengajuanCuti $cuti)
    {
        $validated = $request->validate([
            'keputusan' => ['required', 'in:setujui,tolak'],
            'catatan' => [$request->input('keputusan') === 'tolak' ? 'required' : 'nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($cuti, $validated) {
            $locked = PengajuanCuti::lockForUpdate()->findOrFail($cuti->id);
            abort_unless($locked->status === PengajuanCuti::MENUNGGU_BAAK, 422, 'Pengajuan belum disetujui Kaprodi atau sudah diproses.');
            $mahasiswa = Mahasiswa::lockForUpdate()->findOrFail($locked->mahasiswa_id);

            if ($validated['keputusan'] === 'setujui') {
                abort_unless(strtolower((string) $mahasiswa->status_mhs) === 'aktif', 422, 'Status mahasiswa sudah tidak aktif sehingga cuti tidak dapat diterbitkan.');
                $mahasiswa->update(['status_mhs' => 'cuti']);
            }

            $locked->update([
                'status' => $validated['keputusan'] === 'setujui'
                    ? PengajuanCuti::DISETUJUI
                    : PengajuanCuti::DITOLAK_BAAK,
                'baak_id' => auth()->id(),
                'catatan_baak' => $validated['catatan'] ?? null,
                'diproses_baak_pada' => now(),
            ]);
        });

        activity_log('validasi_cuti_baak', 'BAAK '.$validated['keputusan'].' pengajuan cuti ID '.$cuti->id);

        return back()->with('success', $validated['keputusan'] === 'setujui'
            ? 'Cuti disahkan. Status mahasiswa otomatis berubah menjadi Cuti.'
            : 'Pengajuan cuti ditolak oleh BAAK.');
    }

    public function attachment(PengajuanCuti $cuti)
    {
        abort_unless($cuti->lampiran && Storage::disk('private')->exists($cuti->lampiran), 404);

        return Storage::disk('private')->response($cuti->lampiran, basename($cuti->lampiran));
    }
}
