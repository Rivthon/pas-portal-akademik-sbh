<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\Pertemuan;
use App\Models\PertemuanPraktik;
use App\Models\TahunAkademik;
use App\Models\TeachingHonorPayment;
use App\Support\TeachingHonorPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class TeachingHonorController extends Controller
{
    private const MAKSIMAL_PERTEMUAN = 14;

    public function index(Request $request)
    {
        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        $taId = $request->input('ta_id', $activeTa?->ta_id);
        $period = TeachingHonorPeriod::fromKey($request->input('period'));

        $dosenList = Dosen::query()
            ->whereHas('dosenMatakuliah.kurikulum', function ($query) use ($taId) {
                $query->when($taId, fn ($query) => $query->where('ta_id', $taId));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);
                $query->where(function ($query) use ($search) {
                    $query->where('nama', 'like', '%'.$search.'%')
                        ->orWhere('nidn', 'like', '%'.$search.'%')
                        ->orWhere('kd_dosen', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        $rekap = $this->rekapDosen($dosenList->pluck('dosen_id'), $taId, $period);

        $dosenList->getCollection()->each(function ($dosen) use ($rekap) {
            $data = $rekap->get($dosen->dosen_id, [
                'jumlah_mengajar' => 0,
                'jumlah_kelas' => 0,
                'teori' => 0,
                'praktik' => 0,
                'dibayar' => 0,
                'belum_dibayar' => 0,
            ]);

            foreach ($data as $key => $value) {
                $dosen->{$key} = $value;
            }
            $dosen->jumlah_teori = $data['teori'];
            $dosen->jumlah_praktik = $data['praktik'];
        });

        return view('admin.bap-pengajaran.index', [
            'dosenList' => $dosenList,
            'tahunAkademik' => TahunAkademik::orderByDesc('ta_id')->get(),
            'selectedTaId' => $taId,
            'period' => $period,
            'periodOptions' => TeachingHonorPeriod::options(),
            'maksimalPertemuan' => self::MAKSIMAL_PERTEMUAN,
        ]);
    }

    public function show(Request $request, Dosen $dosen)
    {
        return view('admin.bap-pengajaran.show', $this->detailData($request, $dosen));
    }

    public function downloadPdf(Request $request, Dosen $dosen)
    {
        $data = $this->detailData($request, $dosen);
        $namaDosen = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $dosen->nama);

        activity_log(
            'download_bap_pengajaran_dosen',
            'BAUK/Admin mengunduh BAP pengajaran dosen '.$dosen->nama.' periode '.$data['period']['label']
        );

        return Pdf::loadView('admin.bap-pengajaran.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('BAP-'.$namaDosen.'-'.$data['period']['key'].'.pdf');
    }

    public function bulkDownloadPdf(Request $request)
    {
        $validated = $request->validate([
            'ta_id' => ['required', 'exists:tahun_ajaran,ta_id'],
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'dosen_ids' => ['required', 'array', 'min:1', 'max:50'],
            'dosen_ids.*' => ['required', 'distinct', 'exists:dosen,dosen_id'],
        ], [
            'dosen_ids.required' => 'Pilih minimal satu dosen yang BAP-nya akan diunduh.',
        ]);

        if (! class_exists(ZipArchive::class)) {
            return back()->with('error', 'Ekstensi ZIP belum aktif pada server. Aktifkan php-zip terlebih dahulu.');
        }

        $dosens = Dosen::whereIn('dosen_id', $validated['dosen_ids'])->orderBy('nama')->get();
        $tempDirectory = storage_path('app/temp');
        File::ensureDirectoryExists($tempDirectory);
        $zipPath = tempnam($tempDirectory, 'bap-bulk-');
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            File::delete($zipPath);

            return back()->with('error', 'File ZIP BAP gagal dibuat. Silakan coba kembali.');
        }

        $documentCount = 0;

        try {
            foreach ($dosens as $dosen) {
                $data = $this->detailData($request, $dosen);
                $programNames = $data['jadwalTeori']->concat($data['jadwalPraktik'])
                    ->filter(fn ($jadwal) => $jadwal->pertemuan->isNotEmpty())
                    ->map(fn ($jadwal) => $jadwal->kurikulum?->programStudi?->nama ?: 'Tanpa Program Studi')
                    ->unique()
                    ->values();

                foreach ($programNames as $programName) {
                    $jadwalTeori = new EloquentCollection($data['jadwalTeori']
                        ->filter(fn ($jadwal) => ($jadwal->kurikulum?->programStudi?->nama ?: 'Tanpa Program Studi') === $programName)
                        ->values()->all());
                    $jadwalPraktik = new EloquentCollection($data['jadwalPraktik']
                        ->filter(fn ($jadwal) => ($jadwal->kurikulum?->programStudi?->nama ?: 'Tanpa Program Studi') === $programName)
                        ->values()->all());
                    $jumlahTeori = (int) $jadwalTeori->sum('jumlah_diakui');
                    $jumlahPraktik = (int) $jadwalPraktik->sum('jumlah_diakui');
                    $jumlahDibayar = (int) $jadwalTeori->sum('jumlah_dibayar') + (int) $jadwalPraktik->sum('jumlah_dibayar');
                    $jumlahBelumDibayar = $jumlahTeori + $jumlahPraktik - $jumlahDibayar;
                    $pdfData = array_merge($data, compact(
                        'jadwalTeori', 'jadwalPraktik', 'jumlahTeori', 'jumlahPraktik',
                        'jumlahDibayar', 'jumlahBelumDibayar'
                    ));

                    $folder = $this->safeFileName($programName);
                    $file = $this->safeFileName($dosen->nama).'-'.$dosen->dosen_id.'.pdf';
                    $pdf = Pdf::loadView('admin.bap-pengajaran.pdf', $pdfData)
                        ->setPaper('a4', 'landscape')
                        ->output();
                    $zip->addFromString($folder.'/'.$file, $pdf);
                    $documentCount++;
                }
            }
        } catch (\Throwable $exception) {
            $zip->close();
            File::delete($zipPath);

            throw $exception;
        }

        $zip->close();

        if ($documentCount === 0) {
            File::delete($zipPath);

            return back()->with('info', 'Dosen terpilih belum memiliki sesi BAP pada periode ini.');
        }

        activity_log(
            'download_massal_bap_pengajaran',
            'Mengunduh '.$documentCount.' dokumen BAP dari '.$dosens->count().' dosen periode '.TeachingHonorPeriod::fromKey($request->period)['label']
        );

        return response()->download(
            $zipPath,
            'BAP-Bulk-'.$validated['period'].'.zip',
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(true);
    }

    public function markPaid(Request $request, Dosen $dosen)
    {
        $request->validate([
            'ta_id' => ['required', 'exists:tahun_ajaran,ta_id'],
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $paidCount = $this->storePayments($request, $dosen);

        if ($paidCount === 0) {
            return back()->with('info', 'Tidak ada sesi belum dibayar yang dapat dikonfirmasi pada periode ini.');
        }

        activity_log(
            'konfirmasi_pembayaran_honor_pengajaran',
            'BAUK mengonfirmasi '.$paidCount.' sesi honor '.$dosen->nama.' periode '.TeachingHonorPeriod::fromKey($request->period)['label']
        );

        return redirect()->route('admin.bap-pengajaran.show', [
            'dosen' => $dosen,
            'ta_id' => $request->ta_id,
            'period' => $request->period,
        ])->with('success', $paidCount.' sesi berhasil ditandai Paid.');
    }

    public function bulkMarkPaid(Request $request)
    {
        $validated = $request->validate([
            'ta_id' => ['required', 'exists:tahun_ajaran,ta_id'],
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'dosen_ids' => ['required', 'array', 'min:1', 'max:100'],
            'dosen_ids.*' => ['required', 'distinct', 'exists:dosen,dosen_id'],
        ], [
            'dosen_ids.required' => 'Pilih minimal satu dosen yang akan dikonfirmasi.',
        ]);

        $dosens = Dosen::whereIn('dosen_id', $validated['dosen_ids'])->get();
        $paidSessions = 0;
        $paidLecturers = 0;

        foreach ($dosens as $dosen) {
            $count = $this->storePayments($request, $dosen);
            $paidSessions += $count;
            $paidLecturers += $count > 0 ? 1 : 0;
        }

        if ($paidSessions === 0) {
            return back()->with('info', 'Dosen terpilih tidak memiliki sesi belum dibayar pada periode ini.');
        }

        activity_log(
            'konfirmasi_massal_pembayaran_honor_pengajaran',
            'BAUK mengonfirmasi '.$paidSessions.' sesi dari '.$paidLecturers.' dosen untuk periode '.TeachingHonorPeriod::fromKey($request->period)['label']
        );

        return redirect()->route('admin.bap-pengajaran.index', [
            'ta_id' => $request->ta_id,
            'period' => $request->period,
            'search' => $request->search,
            'page' => $request->page,
        ])->with('success', $paidSessions.' sesi dari '.$paidLecturers.' dosen berhasil ditandai Paid.');
    }

    private function storePayments(Request $request, Dosen $dosen): int
    {
        $data = $this->detailData($request, $dosen);
        $payable = collect([$data['jadwalTeori'], $data['jadwalPraktik']])
            ->flatten(1)
            ->flatMap(fn ($jadwal) => $jadwal->pertemuan)
            ->filter(fn ($meeting) => $meeting->honor_recognized && ! $meeting->honor_payment);

        if ($payable->isEmpty()) {
            return 0;
        }

        $batchId = (string) Str::uuid();
        $inserted = 0;

        DB::transaction(function () use ($payable, $batchId, $dosen, $data, &$inserted) {
            foreach ($payable as $meeting) {
                $jadwal = $meeting->honor_schedule;
                $payment = TeachingHonorPayment::firstOrCreate(
                    ['meeting_type' => $meeting->honor_type, 'meeting_id' => $meeting->getKey()],
                    [
                        'batch_id' => $batchId,
                        'dosen_id' => $dosen->dosen_id,
                        'ta_id' => $data['selectedTa']?->ta_id,
                        'period_start' => $data['period']['start']->toDateString(),
                        'period_end' => $data['period']['end']->toDateString(),
                        'schedule_id' => $jadwal->getKey(),
                        'meeting_date' => $meeting->tanggal_pertemuan,
                        'course_name' => $jadwal->kurikulum?->mataKuliah?->nama,
                        'study_program_name' => $jadwal->kurikulum?->programStudi?->nama,
                        'class_name' => jenis_kelas_label($jadwal->jenis_kelas ?: '-'),
                        'topic' => $meeting->topik,
                        'paid_at' => now(),
                        'paid_by' => auth()->id(),
                    ]
                );
                $inserted += $payment->wasRecentlyCreated ? 1 : 0;
            }
        });

        return $inserted;
    }

    private function detailData(Request $request, Dosen $dosen): array
    {
        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        $taId = $request->input('ta_id', $activeTa?->ta_id);
        $selectedTa = TahunAkademik::find($taId);
        $period = TeachingHonorPeriod::fromKey($request->input('period'));

        $jadwalTeori = $this->loadSchedules(Jadwal::query(), 'teori', $dosen, $taId, $period);
        $jadwalPraktik = $this->loadSchedules(JadwalPraktik::query(), 'praktik', $dosen, $taId, $period);

        $jumlahTeori = (int) $jadwalTeori->sum('jumlah_diakui');
        $jumlahPraktik = (int) $jadwalPraktik->sum('jumlah_diakui');
        $jumlahDibayar = (int) $jadwalTeori->sum('jumlah_dibayar') + (int) $jadwalPraktik->sum('jumlah_dibayar');
        $jumlahBelumDibayar = $jumlahTeori + $jumlahPraktik - $jumlahDibayar;

        $paymentBatches = TeachingHonorPayment::query()
            ->where('dosen_id', $dosen->dosen_id)
            ->where('ta_id', $taId)
            ->whereDate('period_start', $period['start'])
            ->whereDate('period_end', $period['end'])
            ->with('paidBy')
            ->orderByDesc('paid_at')
            ->get()
            ->groupBy('batch_id')
            ->map(fn ($items) => (object) [
                'batch_id' => $items->first()->batch_id,
                'paid_at' => $items->first()->paid_at,
                'paid_by' => $items->first()->paidBy?->name ?? $items->first()->paidBy?->username ?? '-',
                'total' => $items->count(),
                'teori' => $items->where('meeting_type', 'teori')->count(),
                'praktik' => $items->where('meeting_type', 'praktik')->count(),
            ])->values();

        return compact(
            'dosen', 'selectedTa', 'period', 'jadwalTeori', 'jadwalPraktik',
            'jumlahTeori', 'jumlahPraktik', 'jumlahDibayar', 'jumlahBelumDibayar', 'paymentBatches'
        ) + ['maksimalPertemuan' => self::MAKSIMAL_PERTEMUAN];
    }

    private function loadSchedules($query, string $type, Dosen $dosen, $taId, array $period): EloquentCollection
    {
        $effectiveEnd = $this->effectivePeriodEnd($period);

        $schedules = $query
            ->where('ta_id', $taId)
            ->whereHas('kurikulum.dosenToMatakuliah', function ($assignment) use ($dosen, $type) {
                $assignment->where('dosen_id', $dosen->dosen_id)
                    ->whereRaw('LOWER(jenis_dosen) = ?', [$type]);
            })
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
                'ruangan',
                'pertemuan' => fn ($meeting) => $meeting
                    ->where('dosen_id', $dosen->dosen_id)
                    ->whereDate('tanggal_pertemuan', '<=', $effectiveEnd)
                    ->orderBy('tanggal_pertemuan')
                    ->orderBy('jam_mulai'),
            ])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $meetingIds = $schedules->flatMap(fn ($schedule) => $schedule->pertemuan->map->getKey());
        $payments = TeachingHonorPayment::query()
            ->where('meeting_type', $type)
            ->whereIn('meeting_id', $meetingIds)
            ->get()
            ->keyBy('meeting_id');

        return $schedules->each(function ($schedule) use ($type, $period, $effectiveEnd, $payments) {
            $allMeetings = $schedule->pertemuan->values();
            $recognizedIds = $allMeetings->take(self::MAKSIMAL_PERTEMUAN)
                ->mapWithKeys(fn ($meeting) => [(string) $meeting->getKey() => true]);

            $periodMeetings = $allMeetings
                ->filter(function ($meeting) use ($period, $effectiveEnd) {
                    $date = $meeting->tanggal_pertemuan instanceof CarbonInterface
                        ? $meeting->tanggal_pertemuan->toDateString()
                        : substr((string) $meeting->tanggal_pertemuan, 0, 10);

                    return $date >= $period['start']->toDateString() && $date <= $effectiveEnd;
                })
                ->values()
                ->each(function ($meeting) use ($schedule, $type, $recognizedIds, $payments) {
                    $meeting->honor_type = $type;
                    $meeting->honor_schedule = $schedule;
                    $meeting->honor_recognized = isset($recognizedIds[(string) $meeting->getKey()]);
                    $meeting->honor_payment = $payments->get($meeting->getKey());
                });

            $schedule->setRelation('pertemuan', $periodMeetings);
            $schedule->jumlah_pertemuan = $periodMeetings->count();
            $schedule->jumlah_diakui = $periodMeetings->where('honor_recognized', true)->count();
            $schedule->jumlah_dibayar = $periodMeetings
                ->filter(fn ($meeting) => $meeting->honor_recognized && $meeting->honor_payment)->count();
            $schedule->jumlah_belum_dibayar = $schedule->jumlah_diakui - $schedule->jumlah_dibayar;
            $schedule->terakhir_mengajar = $periodMeetings->max('tanggal_pertemuan');
        });
    }

    private function rekapDosen(Collection $dosenIds, $taId, array $period): Collection
    {
        $effectiveEnd = $this->effectivePeriodEnd($period);
        $rekap = $dosenIds->mapWithKeys(fn ($id) => [$id => [
            'jumlah_mengajar' => 0, 'jumlah_kelas' => 0, 'teori' => 0,
            'praktik' => 0, 'dibayar' => 0, 'belum_dibayar' => 0,
        ]]);

        $sets = [
            'teori' => Pertemuan::query()
                ->whereIn('dosen_id', $dosenIds)
                ->whereHas('jadwal', fn ($query) => $query->where('ta_id', $taId))
                ->whereDate('tanggal_pertemuan', '<=', $effectiveEnd)
                ->orderBy('tanggal_pertemuan')->orderBy('pertemuan_id')->get(),
            'praktik' => PertemuanPraktik::query()
                ->whereIn('dosen_id', $dosenIds)
                ->whereHas('jadwal', fn ($query) => $query->where('ta_id', $taId))
                ->whereDate('tanggal_pertemuan', '<=', $effectiveEnd)
                ->orderBy('tanggal_pertemuan')->orderBy('pertemuan_praktik_id')->get(),
        ];

        $payments = TeachingHonorPayment::query()
            ->whereIn('dosen_id', $dosenIds)
            ->where('ta_id', $taId)
            ->get()
            ->mapWithKeys(fn ($payment) => [$payment->meeting_type.':'.$payment->meeting_id => true]);

        foreach ($sets as $type => $meetings) {
            $scheduleKey = $type === 'teori' ? 'jadwal_id' : 'jadwal_praktik_id';

            $meetings->groupBy(fn ($meeting) => $meeting->dosen_id.':'.$meeting->{$scheduleKey})
                ->each(function ($scheduleMeetings) use (&$rekap, $type, $period, $effectiveEnd, $payments) {
                    $dosenId = $scheduleMeetings->first()->dosen_id;
                    $recognized = $scheduleMeetings->take(self::MAKSIMAL_PERTEMUAN)
                        ->filter(function ($meeting) use ($period, $effectiveEnd) {
                            $date = $meeting->tanggal_pertemuan instanceof CarbonInterface
                                ? $meeting->tanggal_pertemuan->toDateString()
                                : substr((string) $meeting->tanggal_pertemuan, 0, 10);

                            return $date >= $period['start']->toDateString() && $date <= $effectiveEnd;
                        });

                    if ($recognized->isEmpty()) {
                        return;
                    }

                    $paid = $recognized->filter(fn ($meeting) => $payments->has($type.':'.$meeting->getKey()))->count();
                    $row = $rekap->get($dosenId);
                    $row[$type] += $recognized->count();
                    $row['jumlah_mengajar'] += $recognized->count();
                    $row['jumlah_kelas']++;
                    $row['dibayar'] += $paid;
                    $row['belum_dibayar'] += $recognized->count() - $paid;
                    $rekap->put($dosenId, $row);
                });
        }

        return $rekap;
    }

    private function effectivePeriodEnd(array $period): string
    {
        $today = now()->toDateString();

        return min($period['end']->toDateString(), $today);
    }

    private function safeFileName(string $value): string
    {
        $value = Str::ascii($value);
        $value = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value);

        return trim((string) $value, '-_.') ?: 'Tanpa-Nama';
    }
}
