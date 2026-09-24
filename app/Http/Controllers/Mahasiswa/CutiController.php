<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanCuti;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CutiController extends Controller
{
    public function index()
    {
        $mahasiswa = auth('mahasiswa')->user();
        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        $pengajuan = PengajuanCuti::with(['tahunAkademik', 'dospem', 'kaprodi', 'baak'])
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->latest('diajukan_pada')
            ->get();
        $pengajuanAktif = $pengajuan->first(fn ($item) => in_array($item->status, [
            PengajuanCuti::MENUNGGU_DOSPEM,
            PengajuanCuti::MENUNGGU_KAPRODI,
            PengajuanCuti::MENUNGGU_BAAK,
            PengajuanCuti::DISETUJUI,
        ], true) && (int) $item->ta_id === (int) $activeTa?->ta_id);

        return view('mahasiswa.cuti.index', compact('mahasiswa', 'activeTa', 'pengajuan', 'pengajuanAktif'));
    }

    public function store(Request $request)
    {
        $mahasiswa = auth('mahasiswa')->user();
        $activeTa = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTa) {
            throw ValidationException::withMessages(['tahun_akademik' => 'Tidak ada tahun akademik aktif.']);
        }
        if (strtolower((string) $mahasiswa->status_mhs) !== 'aktif') {
            throw ValidationException::withMessages(['status' => 'Pengajuan cuti hanya dapat dilakukan oleh mahasiswa berstatus aktif.']);
        }
        if (! $mahasiswa->dosen_id) {
            throw ValidationException::withMessages(['dospem' => 'Dosen pembimbing akademik belum ditentukan. Hubungi BAAK.']);
        }
        if (! $mahasiswa->programStudi?->kaprodi_dosen_id) {
            throw ValidationException::withMessages(['kaprodi' => 'Kaprodi program studi belum ditentukan. Hubungi BAAK.']);
        }

        $sudahAda = PengajuanCuti::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $activeTa->ta_id)
            ->whereIn('status', [
                PengajuanCuti::MENUNGGU_DOSPEM,
                PengajuanCuti::MENUNGGU_KAPRODI,
                PengajuanCuti::MENUNGGU_BAAK,
                PengajuanCuti::DISETUJUI,
            ])->exists();
        if ($sudahAda) {
            throw ValidationException::withMessages(['pengajuan' => 'Anda sudah memiliki pengajuan cuti aktif pada tahun akademik ini.']);
        }

        $validated = $request->validate([
            'alasan' => ['required', 'string', 'min:10', 'max:5000'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
        $path = $request->file('lampiran')?->store('cuti/'.$mahasiswa->mahasiswa_id, 'private');

        try {
            $cuti = DB::transaction(fn () => PengajuanCuti::create([
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'ta_id' => $activeTa->ta_id,
                'program_studi_id' => $mahasiswa->jurusan_id,
                'dospem_id' => $mahasiswa->dosen_id,
                'alasan' => $validated['alasan'],
                'lampiran' => $path,
                'status' => PengajuanCuti::MENUNGGU_DOSPEM,
                'status_mahasiswa_sebelumnya' => $mahasiswa->status_mhs,
                'diajukan_pada' => now(),
            ]));
        } catch (\Throwable $exception) {
            if ($path) {
                Storage::disk('private')->delete($path);
            }
            throw $exception;
        }

        activity_log('pengajuan_cuti', 'Mahasiswa mengajukan cuti ID '.$cuti->id);

        return back()->with('success', 'Pengajuan cuti berhasil dikirim kepada dosen pembimbing.');
    }

    public function cancel(PengajuanCuti $cuti)
    {
        $mahasiswa = auth('mahasiswa')->user();
        abort_unless((int) $cuti->mahasiswa_id === (int) $mahasiswa->mahasiswa_id, 403);
        abort_unless($cuti->status === PengajuanCuti::MENUNGGU_DOSPEM, 422, 'Pengajuan yang sudah diproses tidak dapat dibatalkan.');

        $cuti->update(['status' => PengajuanCuti::DIBATALKAN]);
        activity_log('batalkan_cuti', 'Mahasiswa membatalkan pengajuan cuti ID '.$cuti->id);

        return back()->with('success', 'Pengajuan cuti berhasil dibatalkan.');
    }

    public function attachment(PengajuanCuti $cuti)
    {
        abort_unless((int) $cuti->mahasiswa_id === (int) auth('mahasiswa')->id(), 403);

        return $this->attachmentResponse($cuti);
    }

    private function attachmentResponse(PengajuanCuti $cuti)
    {
        abort_unless($cuti->lampiran && Storage::disk('private')->exists($cuti->lampiran), 404);

        return Storage::disk('private')->response($cuti->lampiran, basename($cuti->lampiran));
    }
}
