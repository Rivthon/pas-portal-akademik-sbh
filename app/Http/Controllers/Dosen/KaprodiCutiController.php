<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\PengajuanCuti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KaprodiCutiController extends Controller
{
    private function prodiIds()
    {
        return auth('dosen')->user()->programStudiDipimpin()->pluck('jurusan_id');
    }

    public function index(Request $request)
    {
        $prodiIds = $this->prodiIds();
        abort_if($prodiIds->isEmpty(), 403, 'Akun Anda belum ditetapkan sebagai Kaprodi.');
        $status = $request->input('status', 'menunggu');
        $pengajuan = PengajuanCuti::with(['mahasiswa', 'tahunAkademik', 'dospem'])
            ->whereIn('program_studi_id', $prodiIds)
            ->when($status === 'menunggu', fn ($query) => $query->where('status', PengajuanCuti::MENUNGGU_KAPRODI))
            ->when($status === 'selesai', fn ($query) => $query->whereNotIn('status', [PengajuanCuti::MENUNGGU_DOSPEM, PengajuanCuti::MENUNGGU_KAPRODI]))
            ->latest('diajukan_pada')
            ->paginate(20)
            ->withQueryString();

        return view('dosen.kaprodi.cuti', compact('pengajuan', 'status'));
    }

    public function decide(Request $request, PengajuanCuti $cuti)
    {
        abort_unless($this->prodiIds()->contains((string) $cuti->program_studi_id), 403);
        $validated = $request->validate([
            'keputusan' => ['required', 'in:setujui,tolak'],
            'catatan' => [$request->input('keputusan') === 'tolak' ? 'required' : 'nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($cuti, $validated) {
            $locked = PengajuanCuti::lockForUpdate()->findOrFail($cuti->id);
            abort_unless($locked->status === PengajuanCuti::MENUNGGU_KAPRODI, 422, 'Pengajuan belum disetujui dospem atau sudah diproses.');
            $locked->update([
                'status' => $validated['keputusan'] === 'setujui'
                    ? PengajuanCuti::MENUNGGU_BAAK
                    : PengajuanCuti::DITOLAK_KAPRODI,
                'kaprodi_id' => auth('dosen')->id(),
                'catatan_kaprodi' => $validated['catatan'] ?? null,
                'diproses_kaprodi_pada' => now(),
            ]);
        });

        activity_log('verifikasi_cuti_kaprodi', 'Kaprodi '.$validated['keputusan'].' pengajuan cuti ID '.$cuti->id);

        return back()->with('success', $validated['keputusan'] === 'setujui'
            ? 'Pengajuan disetujui dan diteruskan kepada BAAK.'
            : 'Pengajuan cuti ditolak.');
    }

    public function attachment(PengajuanCuti $cuti)
    {
        abort_unless($this->prodiIds()->contains((string) $cuti->program_studi_id), 403);
        abort_unless($cuti->lampiran && Storage::disk('private')->exists($cuti->lampiran), 404);

        return Storage::disk('private')->response($cuti->lampiran, basename($cuti->lampiran));
    }
}
