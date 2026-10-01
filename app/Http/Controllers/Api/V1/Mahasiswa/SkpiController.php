<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\KegiatanTambahan;
use App\Models\Mahasiswa;
use App\Models\P2mwProgram;
use App\Models\PenguasaanBahasa;
use App\Models\PkmProgram;
use App\Models\Ppsm;
use App\Models\Sertifikasi;
use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SkpiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $categories = collect($this->categories())->map(function (array $config, string $key) use ($mahasiswa) {
            $query = $config['model']::query()->where('mahasiswa_id', $mahasiswa->mahasiswa_id);
            $statuses = (clone $query)->selectRaw('status_validasi, COUNT(*) as total')
                ->groupBy('status_validasi')->pluck('total', 'status_validasi');

            return [
                'key' => $key,
                'title' => $config['title'],
                'description' => $config['description'],
                'total' => (int) $statuses->sum(),
                'menunggu' => (int) ($statuses['Menunggu'] ?? 0),
                'ditinjau' => (int) ($statuses['Ditinjau'] ?? 0),
                'disetujui' => (int) ($statuses['Disetujui'] ?? 0),
                'ditolak' => (int) ($statuses['Ditolak'] ?? 0),
                'bobot' => (float) (clone $query)->where('status_validasi', 'Disetujui')->sum('bobot'),
            ];
        })->values();

        $approvedScore = $categories->sum('bobot');
        $academicYear = TahunAkademik::query()->where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);

        activity_log('lihat_skpi_mobile', 'Mahasiswa mengakses Aktivitas dan Prestasi SKPI melalui aplikasi Android');

        return response()->json([
            'mahasiswa' => [
                'id' => (int) $mahasiswa->mahasiswa_id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
                'semester' => (int) $mahasiswa->semester,
            ],
            'tahun_akademik' => $academicYear ? [
                'id' => (int) $academicYear->ta_id,
                'nama' => $academicYear->nama,
                'semester' => $academicYear->semester,
            ] : null,
            'total_pengajuan' => (int) $categories->sum('total'),
            'total_disetujui' => (int) $categories->sum('disetujui'),
            'total_bobot' => (float) $approvedScore,
            'kategori' => $categories,
        ]);
    }

    public function records(Request $request, string $category): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $config = $this->category($category);
        $query = $config['model']::query()->where('mahasiswa_id', $mahasiswa->mahasiswa_id);
        if ($request->filled('status')) {
            $request->validate(['status' => [Rule::in(['Menunggu', 'Ditinjau', 'Disetujui', 'Ditolak'])]]);
            $query->where('status_validasi', (string) $request->string('status'));
        }

        $records = $query->latest()->get()->map(fn (Model $record) => $this->recordPayload($record, $config));

        return response()->json([
            'kategori' => [
                'key' => $category,
                'title' => $config['title'],
                'description' => $config['description'],
            ],
            'data' => $records,
        ]);
    }

    public function store(Request $request, string $category): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $config = $this->category($category);
        $data = $request->validate($config['rules']);
        $data['mahasiswa_id'] = $mahasiswa->mahasiswa_id;
        $data['status_validasi'] = 'Menunggu';
        $data['catatan_validator'] = null;
        $data['bobot'] = 0;
        $record = $config['model']::query()->create($data);

        activity_log('tambah_skpi_mobile', "Mahasiswa menambahkan {$config['title']} melalui aplikasi Android");

        return response()->json([
            'message' => 'Pengajuan berhasil ditambahkan dan menunggu validasi.',
            'data' => $this->recordPayload($record, $config),
        ], 201);
    }

    public function update(Request $request, string $category, int $record): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $config = $this->category($category);
        $item = $this->ownedRecord($config['model'], $record, $mahasiswa);
        $data = $request->validate($config['rules']);
        $data['status_validasi'] = 'Menunggu';
        $data['catatan_validator'] = null;
        $data['bobot'] = 0;
        $item->update($data);

        activity_log('ubah_skpi_mobile', "Mahasiswa mengubah {$config['title']} melalui aplikasi Android");

        return response()->json([
            'message' => 'Pengajuan berhasil diperbarui dan dikirim ulang untuk validasi.',
            'data' => $this->recordPayload($item->fresh(), $config),
        ]);
    }

    public function destroy(Request $request, string $category, int $record): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $config = $this->category($category);
        $this->ownedRecord($config['model'], $record, $mahasiswa)->delete();

        activity_log('hapus_skpi_mobile', "Mahasiswa menghapus {$config['title']} melalui aplikasi Android");

        return response()->json(['message' => 'Pengajuan berhasil dihapus.']);
    }

    private function category(string $category): array
    {
        $config = $this->categories()[$category] ?? null;
        abort_if(! $config, 404, 'Kategori SKPI tidak ditemukan.');

        return $config;
    }

    private function ownedRecord(string $model, int $id, Mahasiswa $mahasiswa): Model
    {
        return $model::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->findOrFail($id);
    }

    private function recordPayload(Model $record, array $config): array
    {
        return [
            'id' => (int) $record->getKey(),
            'status' => $record->status_validasi ?? 'Menunggu',
            'catatan_validator' => $record->catatan_validator,
            'bobot' => (float) ($record->bobot ?? 0),
            'created_at' => optional($record->created_at)->toIso8601String(),
            'updated_at' => optional($record->updated_at)->toIso8601String(),
            'data' => collect($config['fields'])->mapWithKeys(
                fn (string $field) => [$field => $record->getAttribute($field)]
            ),
        ];
    }

    private function categories(): array
    {
        $url = ['required', 'url:http,https', 'max:2048'];

        return [
            'sertifikasi' => [
                'title' => 'Sertifikasi / Kompetensi',
                'description' => 'Sertifikat profesi, kompetensi, pelatihan, atau keahlian.',
                'model' => Sertifikasi::class,
                'fields' => ['nama_kegiatan', 'penyelenggara', 'tingkat_kegiatan', 'prestasi', 'tanggal', 'jenis_sertifikat', 'file_sertifikat', 'dokumen_pendukung'],
                'rules' => [
                    'nama_kegiatan' => ['required', 'string', 'max:255'],
                    'penyelenggara' => ['required', 'string', 'max:255'],
                    'tingkat_kegiatan' => ['required', Rule::in(['Lokal', 'Regional', 'Nasional', 'Internasional'])],
                    'prestasi' => ['nullable', 'string', 'max:255'],
                    'tanggal' => ['required', 'date'],
                    'jenis_sertifikat' => ['required', 'string', 'max:255'],
                    'file_sertifikat' => $url,
                    'dokumen_pendukung' => $url,
                ],
            ],
            'bahasa' => [
                'title' => 'Penguasaan Bahasa Asing',
                'description' => 'Hasil tes atau sertifikasi kemampuan bahasa asing.',
                'model' => PenguasaanBahasa::class,
                'fields' => ['nama_bahasa', 'level', 'penyelenggara', 'tanggal_tes', 'skor', 'file_sertifikat'],
                'rules' => [
                    'nama_bahasa' => ['required', 'string', 'max:255'],
                    'level' => ['required', 'string', 'max:255'],
                    'penyelenggara' => ['required', 'string', 'max:255'],
                    'tanggal_tes' => ['required', 'date'],
                    'skor' => ['nullable', 'integer'],
                    'file_sertifikat' => $url,
                ],
            ],
            'wirausaha' => [
                'title' => 'Program Wirausaha (P2MW)',
                'description' => 'Kegiatan dan capaian Program Pembinaan Mahasiswa Wirausaha.',
                'model' => P2mwProgram::class,
                'fields' => ['nama_usaha', 'jenis_usaha', 'penyelenggara', 'status_pendanaan', 'tanggal', 'file_lampiran', 'file_sertifikat'],
                'rules' => [
                    'nama_usaha' => ['required', 'string', 'max:255'],
                    'jenis_usaha' => ['required', 'string', 'max:255'],
                    'penyelenggara' => ['required', 'string', 'max:255'],
                    'status_pendanaan' => ['required', Rule::in(['Mandiri', 'Didanai'])],
                    'tanggal' => ['required', 'date'],
                    'file_lampiran' => $url,
                    'file_sertifikat' => $url,
                ],
            ],
            'pkm' => [
                'title' => 'Program Kreativitas Mahasiswa',
                'description' => 'Keikutsertaan dan prestasi dalam Program Kreativitas Mahasiswa.',
                'model' => PkmProgram::class,
                'fields' => ['judul_kegiatan', 'jenis_pkm', 'penyelenggara', 'tanggal', 'prestasi', 'file_laporan', 'file_lampiran'],
                'rules' => [
                    'judul_kegiatan' => ['required', 'string', 'max:255'],
                    'jenis_pkm' => ['required', 'string', 'max:255'],
                    'penyelenggara' => ['required', 'string', 'max:255'],
                    'tanggal' => ['required', 'date'],
                    'prestasi' => ['nullable', 'string', 'max:255'],
                    'file_laporan' => $url,
                    'file_lampiran' => $url,
                ],
            ],
            'ppsm' => [
                'title' => 'PPSM',
                'description' => 'Kegiatan Pemahaman Sistem Pembelajaran di Perguruan Tinggi.',
                'model' => Ppsm::class,
                'fields' => ['nama_kegiatan', 'tahun_kegiatan', 'keterangan', 'file_sertifikat', 'file_lampiran'],
                'rules' => [
                    'nama_kegiatan' => ['required', 'string', 'max:255'],
                    'tahun_kegiatan' => ['required', 'integer', 'min:1900', 'max:'.date('Y')],
                    'keterangan' => ['nullable', 'string', 'max:255'],
                    'file_sertifikat' => $url,
                    'file_lampiran' => $url,
                ],
            ],
            'tambahan' => [
                'title' => 'Kegiatan Tambahan',
                'description' => 'Organisasi, kepanitiaan, perlombaan, pengabdian, dan kegiatan lainnya.',
                'model' => KegiatanTambahan::class,
                'fields' => ['kategori', 'nama_kegiatan', 'bentuk_kegiatan', 'tingkat', 'penyelenggara', 'peran', 'tanggal', 'file_sertifikat', 'file_lampiran'],
                'rules' => [
                    'kategori' => ['required', 'string', 'max:255'],
                    'nama_kegiatan' => ['required', 'string', 'max:255'],
                    'bentuk_kegiatan' => ['required', 'string', 'max:255'],
                    'tingkat' => ['required', Rule::in(['Lokal', 'Regional', 'Nasional', 'Internasional'])],
                    'penyelenggara' => ['required', 'string', 'max:255'],
                    'peran' => ['required', 'string', 'max:255'],
                    'tanggal' => ['required', 'date'],
                    'file_sertifikat' => $url,
                    'file_lampiran' => $url,
                ],
            ],
        ];
    }
}
