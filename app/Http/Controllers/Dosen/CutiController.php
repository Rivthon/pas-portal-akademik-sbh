<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\PengajuanCuti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CutiController extends Controller
{
    public function index(Request $request)
    {
        $dosen = auth('dosen')->user();
        $status = $request->input('status', 'menunggu');
        $pengajuan = PengajuanCuti::with(['mahasiswa.programStudi', 'tahunAkademik'])
            ->where('dospem_id', $dosen->dosen_id)
            ->when($status === 'menunggu', fn ($query) => $query->where('status', PengajuanCuti::MENUNGGU_DOSPEM))
            ->when($status === 'selesai', fn ($query) => $query->where('status', '!=', PengajuanCuti::MENUNGGU_DOSPEM))
            ->latest('diajukan_pada')
            ->paginate(20)
            ->withQueryString();

        return view('dosen.cuti.index', compact('pengajuan', 'status'));
    }

    public function decide(Request $request, PengajuanCuti $cuti)
    {
        $dosen = auth('dosen')->user();
        abort_unless((int) $cuti->dospem_id === (int) $dosen->dosen_id, 403);
        $validated = $request->validate([
            'keputusan' => ['required', 'in:setujui,tolak'],
            'catatan' => [$request->input('keputusan') === 'tolak' ? 'required' : 'nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($cuti, $validated) {
            $locked = PengajuanCuti::lockForUpdate()->findOrFail($cuti->id);
            abort_unless($locked->status === PengajuanCuti::MENUNGGU_DOSPEM, 422, 'Pengajuan ini sudah diproses.');
            $locked->update([
                'status' => $validated['keputusan'] === 'setujui'
                    ? PengajuanCuti::MENUNGGU_KAPRODI
                    : PengajuanCuti::DITOLAK_DOSPEM,
                'catatan_dospem' => $validated['catatan'] ?? null,
                'diproses_dospem_pada' => now(),
            ]);
        });

        activity_log('verifikasi_cuti_dospem', 'Dospem '.$validated['keputusan'].' pengajuan cuti ID '.$cuti->id);

        return back()->with('success', $validated['keputusan'] === 'setujui'
            ? 'Pengajuan disetujui dan diteruskan kepada Kaprodi.'
            : 'Pengajuan cuti ditolak.');
    }

    public function attachment(PengajuanCuti $cuti)
    {
        abort_unless((int) $cuti->dospem_id === (int) auth('dosen')->id(), 403);
        abort_unless($cuti->lampiran && Storage::disk('private')->exists($cuti->lampiran), 404);

        return Storage::disk('private')->response($cuti->lampiran, basename($cuti->lampiran));
    }
}
