<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\Setting;
use App\Models\TahunAkademik;
use App\Support\ZipArchiveWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KrsArchiveController extends Controller
{
    private array $base64FileCache = [];

    private ?Setting $settings = null;

    private bool $settingsLoaded = false;

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $archives = $this->archiveQuery($filters)
            ->orderByDesc('mahasiswa.tahun_masuk')
            ->orderBy('mahasiswa.nama')
            ->orderByDesc('krs.ta_id')
            ->orderBy('matakuliah.smt')
            ->paginate(20)
            ->withQueryString();

        $programStudiList = ProgramStudi::orderBy('nama')->get();
        $tahunAkademikList = TahunAkademik::orderByDesc('ta_id')->get();
        $angkatanList = Mahasiswa::whereHas('krs')
            ->whereNotNull('tahun_masuk')
            ->distinct()
            ->orderByDesc('tahun_masuk')
            ->pluck('tahun_masuk');

        $stats = [
            'arsip' => $archives->total(),
            'mahasiswa' => DB::query()
                ->fromSub($this->archiveQuery($filters), 'arsip_krs')
                ->distinct()
                ->count('mahasiswa_id'),
            'total_sks' => (int) $archives->getCollection()->sum('total_sks'),
        ];

        activity_log('lihat_arsip_krs', 'Admin/BAAK melihat arsip KRS mahasiswa');

        return view('admin.akademik.krs-archive.index', compact(
            'archives',
            'programStudiList',
            'tahunAkademikList',
            'angkatanList',
            'filters',
            'stats'
        ));
    }

    public function download(Mahasiswa $mahasiswa, int $tahunAkademik, int $semester)
    {
        abort_unless($semester >= 1 && $semester <= 14, 404);
        $ta = TahunAkademik::findOrFail($tahunAkademik);
        $pdf = $this->renderPdf($mahasiswa, $ta, $semester);
        abort_if($pdf === null, 404, 'Arsip KRS tidak ditemukan.');

        activity_log(
            'download_arsip_krs',
            "Admin/BAAK mengunduh arsip KRS mahasiswa ID {$mahasiswa->mahasiswa_id}, semester {$semester}"
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->pdfFilename($mahasiswa, $ta, $semester).'"',
        ]);
    }

    public function downloadSemester(Request $request): BinaryFileResponse
    {
        @set_time_limit(0);
        DB::disableQueryLog();

        $filters = $this->filters($request, true);
        $rows = $this->archiveQuery($filters)
            ->orderBy('mahasiswa.nama')
            ->orderBy('krs.ta_id')
            ->get();

        if ($rows->isEmpty()) {
            abort(404, 'Tidak ada arsip KRS sesuai filter yang dipilih.');
        }

        $mahasiswaMap = Mahasiswa::with(['programStudi', 'dosen'])
            ->whereIn('mahasiswa_id', $rows->pluck('mahasiswa_id')->unique())
            ->get()
            ->keyBy('mahasiswa_id');
        $tahunAkademikMap = TahunAkademik::whereIn('ta_id', $rows->pluck('ta_id')->unique())
            ->get()
            ->keyBy('ta_id');

        $zip = new ZipArchiveWriter;
        $added = 0;
        foreach ($rows as $row) {
            $mahasiswa = $mahasiswaMap->get($row->mahasiswa_id);
            $ta = $tahunAkademikMap->get($row->ta_id);
            if (! $mahasiswa || ! $ta) {
                continue;
            }

            $pdf = $this->renderPdf($mahasiswa, $ta, (int) $row->semester_krs);
            if ($pdf === null) {
                continue;
            }

            $zip->addFile($this->pdfFilename($mahasiswa, $ta, (int) $row->semester_krs), $pdf);
            $added++;
        }

        abort_if($added === 0, 404, 'Tidak ada arsip KRS yang dapat dibuat.');
        $path = $zip->finish();
        $filename = 'Arsip-KRS-Semester-'.(int) $filters['semester'].'-'.now()->format('Ymd-His').'.zip';

        activity_log(
            'download_zip_arsip_krs',
            "Admin/BAAK mengunduh {$added} arsip KRS semester {$filters['semester']} dalam ZIP"
        );

        return response()->download($path, $filename, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    private function archiveQuery(array $filters): QueryBuilder
    {
        return DB::table('krs')
            ->join('mahasiswa', 'mahasiswa.mahasiswa_id', '=', 'krs.mahasiswa_id')
            ->join('kurikulum', 'kurikulum.kurikulum_id', '=', 'krs.kurikulum_id')
            ->join('matakuliah', 'matakuliah.matakuliah_id', '=', 'kurikulum.matakuliah_id')
            ->join('tahun_ajaran', 'tahun_ajaran.ta_id', '=', 'krs.ta_id')
            ->leftJoin('program_studi', 'program_studi.jurusan_id', '=', 'mahasiswa.jurusan_id')
            ->select([
                'mahasiswa.mahasiswa_id',
                'mahasiswa.nama',
                'mahasiswa.nim',
                'mahasiswa.tahun_masuk',
                'mahasiswa.kelas',
                'mahasiswa.jurusan_id',
                'program_studi.nama as prodi_nama',
                'krs.ta_id',
                'tahun_ajaran.nama as tahun_akademik',
                'tahun_ajaran.semester as periode_akademik',
                'matakuliah.smt as semester_krs',
                DB::raw('COUNT(krs.krs_id) as total_mk'),
                DB::raw('COALESCE(SUM(matakuliah.sks), 0) as total_sks'),
                DB::raw('SUM(CASE WHEN krs.disetujui_pada IS NULL THEN 1 ELSE 0 END) as menunggu_acc'),
            ])
            ->when($filters['search'] !== '', function (QueryBuilder $query) use ($filters) {
                $search = $filters['search'];
                $query->where(function (QueryBuilder $searchQuery) use ($search) {
                    $searchQuery->where('mahasiswa.nama', 'like', '%'.$search.'%')
                        ->orWhere('mahasiswa.nim', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['jurusan_id'] !== null, fn (QueryBuilder $query) => $query
                ->where('mahasiswa.jurusan_id', $filters['jurusan_id']))
            ->when($filters['angkatan'] !== null, fn (QueryBuilder $query) => $query
                ->where('mahasiswa.tahun_masuk', $filters['angkatan']))
            ->when($filters['ta_id'] !== null, fn (QueryBuilder $query) => $query
                ->where('krs.ta_id', $filters['ta_id']))
            ->when($filters['semester'] !== null, fn (QueryBuilder $query) => $query
                ->where('matakuliah.smt', $filters['semester']))
            ->groupBy([
                'mahasiswa.mahasiswa_id',
                'mahasiswa.nama',
                'mahasiswa.nim',
                'mahasiswa.tahun_masuk',
                'mahasiswa.kelas',
                'mahasiswa.jurusan_id',
                'program_studi.nama',
                'krs.ta_id',
                'tahun_ajaran.nama',
                'tahun_ajaran.semester',
                'matakuliah.smt',
            ]);
    }

    private function filters(Request $request, bool $semesterRequired = false): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'jurusan_id' => ['nullable', 'integer', 'exists:program_studi,jurusan_id'],
            'angkatan' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'ta_id' => ['nullable', 'integer', 'exists:tahun_ajaran,ta_id'],
            'semester' => [$semesterRequired ? 'required' : 'nullable', 'integer', 'min:1', 'max:14'],
        ], [
            'semester.required' => 'Pilih semester terlebih dahulu untuk mengunduh ZIP.',
        ]);

        return [
            'search' => trim((string) ($validated['search'] ?? '')),
            'jurusan_id' => isset($validated['jurusan_id']) ? (int) $validated['jurusan_id'] : null,
            'angkatan' => isset($validated['angkatan']) ? (int) $validated['angkatan'] : null,
            'ta_id' => isset($validated['ta_id']) ? (int) $validated['ta_id'] : null,
            'semester' => isset($validated['semester']) ? (int) $validated['semester'] : null,
        ];
    }

    private function renderPdf(Mahasiswa $mahasiswa, TahunAkademik $ta, int $semester): ?string
    {
        $mahasiswa->loadMissing(['programStudi', 'dosen']);
        $krs = Krs::with(['kurikulum.mataKuliah', 'disetujuiOleh'])
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->whereHas('kurikulum.mataKuliah', fn (Builder $query) => $query
                ->where('smt', $semester))
            ->get()
            ->sortBy(fn (Krs $item) => (string) ($item->kurikulum?->mataKuliah?->nama ?? ''))
            ->values();

        if ($krs->isEmpty()) {
            return null;
        }

        $settings = $this->settings();
        $headerKrs = $this->base64PublicFile($mahasiswa->programStudi?->header_baak);
        $logo = $this->base64PublicFile($settings?->logo);
        $sudahDisetujui = $krs->every(fn (Krs $item) => $item->disetujui_pada !== null);

        return Pdf::loadView('admin.akademik.krs-archive.pdf', compact(
            'mahasiswa',
            'ta',
            'semester',
            'krs',
            'settings',
            'headerKrs',
            'logo',
            'sudahDisetujui'
        ))
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->output();
    }

    private function base64PublicFile(?string $relativePath): ?string
    {
        if (! filled($relativePath)) {
            return null;
        }

        if (array_key_exists($relativePath, $this->base64FileCache)) {
            return $this->base64FileCache[$relativePath];
        }

        $path = storage_path('app/public/'.$relativePath);

        return $this->base64FileCache[$relativePath] = is_file($path)
            ? base64_encode((string) file_get_contents($path))
            : null;
    }

    private function settings(): ?Setting
    {
        if (! $this->settingsLoaded) {
            $this->settings = Setting::first();
            $this->settingsLoaded = true;
        }

        return $this->settings;
    }

    private function pdfFilename(Mahasiswa $mahasiswa, TahunAkademik $ta, int $semester): string
    {
        $nim = Str::slug((string) $mahasiswa->nim, '-');
        $nama = Str::slug((string) $mahasiswa->nama, '-');
        $tahun = Str::slug((string) $ta->nama, '-');

        return "KRS-{$nim}-{$nama}-Semester-{$semester}-{$tahun}.pdf";
    }
}
