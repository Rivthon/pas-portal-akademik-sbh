<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\PengajuanCuti;
use App\Models\Permintaan;
use App\Models\TagihanMahasiswa;
use App\Models\TahunAkademik;
use App\Support\StoredUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentServiceController extends Controller
{
    public function helpdesk(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $items = Permintaan::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->latest()
            ->get()
            ->map(fn (Permintaan $item) => [
                'id' => (int) $item->id,
                'jenis' => $item->jenis_permintaan,
                'judul' => $item->judul,
                'deskripsi' => $item->deskripsi,
                'prioritas' => $item->prioritas,
                'status' => $item->status,
                'tanggapan_admin' => $item->komentar_admin,
                'memiliki_lampiran' => StoredUpload::exists($item->file_lampiran),
                'nama_lampiran' => $item->file_lampiran ? basename($item->file_lampiran) : null,
                'dibuat_pada' => optional($item->created_at)->toIso8601String(),
                'diperbarui_pada' => optional($item->updated_at)->toIso8601String(),
            ]);

        activity_log('kelola_permintaan_mobile', 'Mahasiswa melihat Helpdesk melalui aplikasi Android');

        return response()->json(['permintaan' => $items]);
    }

    public function storeHelpdesk(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $validated = $request->validate([
            'jenis_permintaan' => ['required', Rule::in(['bug', 'fitur', 'akses', 'lainnya'])],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'min:10', 'max:5000'],
            'prioritas' => ['required', Rule::in(['rendah', 'sedang', 'tinggi', 'urgen'])],
            'file_lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,zip', 'extensions:pdf,jpg,jpeg,png,doc,docx,zip', 'max:2048'],
        ]);
        if ($request->hasFile('file_lampiran')) {
            $validated['file_lampiran'] = $request->file('file_lampiran')
                ->store('lampiran_permintaan', 'private');
        }
        $validated['mahasiswa_id'] = $mahasiswa->mahasiswa_id;
        $validated['dosen_id'] = null;
        $validated['status'] = 'menunggu';
        $item = Permintaan::query()->create($validated);

        activity_log('buat_permintaan_mobile', 'Mahasiswa membuat permintaan Helpdesk: '.$item->judul);

        return response()->json([
            'message' => 'Permintaan berhasil dikirim langsung ke Helpdesk Admin.',
            'id' => (int) $item->id,
        ], 201);
    }

    public function destroyHelpdesk(Request $request, Permintaan $permintaan): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        abort_unless((int) $permintaan->mahasiswa_id === (int) $mahasiswa->mahasiswa_id, 404);
        StoredUpload::delete($permintaan->file_lampiran);
        $permintaan->delete();

        activity_log('hapus_permintaan_mobile', 'Mahasiswa menghapus permintaan Helpdesk');

        return response()->json(['message' => 'Permintaan berhasil dihapus.']);
    }

    public function helpdeskAttachment(Request $request, Permintaan $permintaan)
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        abort_unless((int) $permintaan->mahasiswa_id === (int) $mahasiswa->mahasiswa_id, 404);
        abort_unless(StoredUpload::exists($permintaan->file_lampiran), 404);

        return StoredUpload::disk($permintaan->file_lampiran)->response(
            $permintaan->file_lampiran,
            basename((string) $permintaan->file_lampiran),
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']
        );
    }

    public function finance(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $bills = TagihanMahasiswa::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->with(['tahunAjaran', 'tenorPembayaran', 'transaksi'])
            ->orderByDesc('semester')
            ->orderByDesc('jatuh_tempo')
            ->get();
        $total = (float) $bills->sum('jumlah_tagihan');
        $paid = (float) $bills->sum(fn (TagihanMahasiswa $bill) => $bill->transaksi
            ->where('status_verifikasi', 'diterima')->sum('nominal_bayar'));

        activity_log('lihat_administrasi_mobile', 'Mahasiswa melihat administrasi melalui aplikasi Android');

        return response()->json([
            'ringkasan' => [
                'total_tagihan' => $total,
                'total_dibayar' => $paid,
                'sisa' => max(0, $total - $paid),
            ],
            'tagihan' => $bills->map(function (TagihanMahasiswa $bill) {
                $paid = (float) $bill->transaksi->where('status_verifikasi', 'diterima')->sum('nominal_bayar');
                $amount = (float) $bill->jumlah_tagihan;

                return [
                    'id' => (int) $bill->id,
                    'semester' => (int) $bill->semester,
                    'tahun_akademik' => $bill->tahunAjaran?->nama,
                    'tenor' => $bill->tenorPembayaran?->tenor,
                    'jumlah_tagihan' => $amount,
                    'telah_dibayar' => $paid,
                    'sisa' => max(0, $amount - $paid),
                    'jatuh_tempo' => $bill->jatuh_tempo,
                    'status' => $amount - $paid <= 0 ? 'lunas' : 'belum_lunas',
                    'transaksi' => $bill->transaksi->map(fn ($transaction) => [
                        'id' => (int) $transaction->transaksi_id,
                        'nominal' => (float) $transaction->nominal_bayar,
                        'tanggal' => $transaction->tanggal_bayar,
                        'keterangan' => $transaction->keterangan,
                        'status' => $transaction->status_verifikasi,
                    ])->values(),
                ];
            })->values(),
        ]);
    }

    public function leave(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $activeTa = TahunAkademik::query()->where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        $submissions = PengajuanCuti::query()
            ->with(['tahunAkademik', 'dospem', 'kaprodi', 'baak'])
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->latest('diajukan_pada')
            ->get();
        $activeStatuses = [
            PengajuanCuti::MENUNGGU_DOSPEM,
            PengajuanCuti::MENUNGGU_KAPRODI,
            PengajuanCuti::MENUNGGU_BAAK,
            PengajuanCuti::DISETUJUI,
        ];
        $hasActive = $submissions->contains(fn (PengajuanCuti $item) => (int) $item->ta_id === (int) $activeTa?->ta_id && in_array($item->status, $activeStatuses, true));
        $canSubmit = $activeTa
            && strtolower(trim((string) $mahasiswa->status_mhs)) === 'aktif'
            && filled($mahasiswa->dosen_id)
            && filled($mahasiswa->programStudi?->kaprodi_dosen_id)
            && ! $hasActive;

        return response()->json([
            'tahun_akademik' => $activeTa ? [
                'id' => (int) $activeTa->ta_id,
                'nama' => $activeTa->nama,
                'periode' => $activeTa->semester,
            ] : null,
            'dapat_mengajukan' => (bool) $canSubmit,
            'alasan_tidak_dapat_mengajukan' => $this->leaveUnavailableReason($mahasiswa, $activeTa, $hasActive),
            'pengajuan' => $submissions->map(fn (PengajuanCuti $item) => [
                'id' => (int) $item->id,
                'tahun_akademik' => $item->tahunAkademik?->nama,
                'periode' => $item->tahunAkademik?->semester,
                'alasan' => $item->alasan,
                'status' => $item->status,
                'status_label' => $item->status_label,
                'dapat_dibatalkan' => $item->status === PengajuanCuti::MENUNGGU_DOSPEM,
                'memiliki_lampiran' => filled($item->lampiran) && Storage::disk('private')->exists($item->lampiran),
                'dospem' => $item->dospem?->nama,
                'kaprodi' => $item->kaprodi?->nama,
                'catatan_dospem' => $item->catatan_dospem,
                'catatan_kaprodi' => $item->catatan_kaprodi,
                'catatan_baak' => $item->catatan_baak,
                'diajukan_pada' => optional($item->diajukan_pada)->toIso8601String(),
                'diproses_dospem_pada' => optional($item->diproses_dospem_pada)->toIso8601String(),
                'diproses_kaprodi_pada' => optional($item->diproses_kaprodi_pada)->toIso8601String(),
                'diproses_baak_pada' => optional($item->diproses_baak_pada)->toIso8601String(),
            ])->values(),
        ]);
    }

    public function storeLeave(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $activeTa = TahunAkademik::query()->where('status_ta', 1)->first();
        if (! $activeTa) {
            throw ValidationException::withMessages(['tahun_akademik' => 'Tidak ada tahun akademik aktif.']);
        }
        if (strtolower(trim((string) $mahasiswa->status_mhs)) !== 'aktif') {
            throw ValidationException::withMessages(['status' => 'Pengajuan cuti hanya dapat dilakukan oleh mahasiswa berstatus aktif.']);
        }
        if (! $mahasiswa->dosen_id) {
            throw ValidationException::withMessages(['dospem' => 'Dosen pembimbing akademik belum ditentukan. Hubungi BAAK.']);
        }
        if (! $mahasiswa->programStudi?->kaprodi_dosen_id) {
            throw ValidationException::withMessages(['kaprodi' => 'Kaprodi program studi belum ditentukan. Hubungi BAAK.']);
        }
        $activeStatuses = [
            PengajuanCuti::MENUNGGU_DOSPEM,
            PengajuanCuti::MENUNGGU_KAPRODI,
            PengajuanCuti::MENUNGGU_BAAK,
            PengajuanCuti::DISETUJUI,
        ];
        if (PengajuanCuti::query()->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $activeTa->ta_id)->whereIn('status', $activeStatuses)->exists()) {
            throw ValidationException::withMessages(['pengajuan' => 'Anda sudah memiliki pengajuan cuti aktif pada tahun akademik ini.']);
        }
        $validated = $request->validate([
            'alasan' => ['required', 'string', 'min:10', 'max:5000'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'extensions:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
        $path = $request->file('lampiran')?->store('cuti/'.$mahasiswa->mahasiswa_id, 'private');
        try {
            $submission = DB::transaction(fn () => PengajuanCuti::query()->create([
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

        activity_log('pengajuan_cuti_mobile', 'Mahasiswa mengajukan cuti melalui aplikasi Android ID '.$submission->id);

        return response()->json([
            'message' => 'Pengajuan cuti berhasil dikirim kepada dosen pembimbing.',
            'id' => (int) $submission->id,
        ], 201);
    }

    public function cancelLeave(Request $request, PengajuanCuti $cuti): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        abort_unless((int) $cuti->mahasiswa_id === (int) $mahasiswa->mahasiswa_id, 404);
        abort_unless($cuti->status === PengajuanCuti::MENUNGGU_DOSPEM, 422, 'Pengajuan yang sudah diproses tidak dapat dibatalkan.');
        $cuti->update(['status' => PengajuanCuti::DIBATALKAN]);
        activity_log('batalkan_cuti_mobile', 'Mahasiswa membatalkan pengajuan cuti ID '.$cuti->id);

        return response()->json(['message' => 'Pengajuan cuti berhasil dibatalkan.']);
    }

    public function leaveAttachment(Request $request, PengajuanCuti $cuti)
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        abort_unless((int) $cuti->mahasiswa_id === (int) $mahasiswa->mahasiswa_id, 404);
        abort_unless($cuti->lampiran && Storage::disk('private')->exists($cuti->lampiran), 404);

        return Storage::disk('private')->response(
            $cuti->lampiran,
            basename($cuti->lampiran),
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']
        );
    }

    public function profile(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();

        return response()->json(['profil' => $this->profilePayload($mahasiswa)]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('mahasiswa', 'email')->ignore($mahasiswa->mahasiswa_id, 'mahasiswa_id')],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'nik' => ['nullable', 'string', 'max:20'],
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-Laki', 'Perempuan'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'kota' => ['nullable', 'string', 'max:100'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'no_telp_ortu' => ['nullable', 'string', 'max:20'],
            'pendapatan_ortu' => ['nullable', 'string', 'max:255'],
            'alamat_ortu' => ['nullable', 'string', 'max:2000'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
        ]);
        $profileFields = [
            'nama', 'email', 'no_telp', 'nisn', 'nik', 'jenis_kelamin', 'tempat_lahir',
            'tanggal_lahir', 'agama', 'alamat', 'kota', 'nama_ayah', 'nama_ibu',
            'no_telp_ortu', 'pendapatan_ortu', 'alamat_ortu',
        ];
        $mahasiswa->fill(collect($validated)->only($profileFields)->all());
        if (filled($validated['password'] ?? null)) {
            $mahasiswa->password = Hash::make($validated['password']);
        }
        $oldAvatar = null;
        if ($request->hasFile('avatar')) {
            $newAvatar = $request->file('avatar')->store('avatars', 'public');
            $oldAvatar = $mahasiswa->avatar;
            $mahasiswa->avatar = $newAvatar;
        }
        $mahasiswa->save();
        if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
            Storage::disk('public')->delete($oldAvatar);
        }
        activity_log('update_profil_mobile', 'Mahasiswa memperbarui profil melalui aplikasi Android');

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'profil' => $this->profilePayload($mahasiswa->fresh()),
        ]);
    }

    private function leaveUnavailableReason(Mahasiswa $mahasiswa, ?TahunAkademik $activeTa, bool $hasActive): ?string
    {
        return match (true) {
            ! $activeTa => 'Tahun akademik aktif belum ditentukan.',
            strtolower(trim((string) $mahasiswa->status_mhs)) !== 'aktif' => 'Pengajuan cuti hanya tersedia bagi mahasiswa aktif.',
            ! filled($mahasiswa->dosen_id) => 'Dosen pembimbing akademik belum ditentukan. Hubungi BAAK.',
            ! filled($mahasiswa->programStudi?->kaprodi_dosen_id) => 'Kaprodi program studi belum ditentukan. Hubungi BAAK.',
            $hasActive => 'Anda sudah memiliki pengajuan cuti aktif pada tahun akademik ini.',
            default => null,
        };
    }

    private function profilePayload(Mahasiswa $mahasiswa): array
    {
        $mahasiswa->loadMissing(['programStudi', 'dosen']);

        return [
            'id' => (int) $mahasiswa->mahasiswa_id,
            'avatar_url' => $mahasiswa->avatar_url,
            'nama' => $mahasiswa->nama,
            'email' => $mahasiswa->email,
            'no_telp' => $mahasiswa->no_telp,
            'nisn' => $mahasiswa->nisn,
            'nik' => $mahasiswa->nik,
            'jenis_kelamin' => $mahasiswa->jenis_kelamin,
            'tempat_lahir' => $mahasiswa->tempat_lahir,
            'tanggal_lahir' => $mahasiswa->tanggal_lahir,
            'agama' => $mahasiswa->agama,
            'alamat' => $mahasiswa->alamat,
            'kota' => $mahasiswa->kota,
            'nama_ayah' => $mahasiswa->nama_ayah,
            'nama_ibu' => $mahasiswa->nama_ibu,
            'no_telp_ortu' => $mahasiswa->no_telp_ortu,
            'pendapatan_ortu' => $mahasiswa->pendapatan_ortu,
            'alamat_ortu' => $mahasiswa->alamat_ortu,
            'akademik' => [
                'nim' => $mahasiswa->nim,
                'program_studi' => $mahasiswa->programStudi?->nama,
                'semester' => (int) $mahasiswa->semester,
                'kelas' => $mahasiswa->label_kelas,
                'status' => $mahasiswa->status_mhs,
                'tahun_masuk' => $mahasiswa->tahun_masuk,
                'dosen_pembimbing' => $mahasiswa->dosen?->nama,
            ],
        ];
    }
}
