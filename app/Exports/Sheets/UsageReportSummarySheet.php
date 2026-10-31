<?php

namespace App\Exports\Sheets;

use App\Models\ActivityLog;
use App\Models\ProgramStudi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsageReportSummarySheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    private Carbon $startDate;

    private Carbon $endDate;

    public function __construct(private readonly array $filters)
    {
        $this->startDate = Carbon::parse($filters['start_date'] ?? now()->subDays(29)->toDateString())->startOfDay();
        $this->endDate = Carbon::parse($filters['end_date'] ?? now()->toDateString())->endOfDay();
    }

    public function array(): array
    {
        $logs = $this->filteredLogs();

        $rows = [
            ['LAPORAN PENGGUNAAN PAS'],
            ['Periode', $this->startDate->format('d/m/Y').' - '.$this->endDate->format('d/m/Y')],
            ['Jenis pengguna', ucfirst($this->filters['user_type'] ?? 'Dosen & Mahasiswa')],
            ['Program studi', $this->programStudiName()],
            ['Pencarian', $this->filters['search'] ?? '-'],
            [],
            ['RINGKASAN', 'JUMLAH'],
            ['Total aktivitas', (clone $logs)->count()],
            ['Pengguna aktif', (clone $logs)->selectRaw("COUNT(DISTINCT CONCAT(user_type, ':', user_id)) as aggregate")->value('aggregate') ?: 0],
            ['Dosen aktif', (clone $logs)->where('user_type', 'dosen')->distinct('user_id')->count('user_id')],
            ['Mahasiswa aktif', (clone $logs)->where('user_type', 'mahasiswa')->distinct('user_id')->count('user_id')],
            ['Total login', (clone $logs)->where('aktivitas', 'login')->count()],
            [],
            ['JENIS PENGGUNA', 'AKTIVITAS', 'JUMLAH'],
        ];

        (clone $logs)
            ->select('user_type', 'aktivitas', DB::raw('COUNT(*) as total'))
            ->groupBy('user_type', 'aktivitas')
            ->orderByDesc('total')
            ->limit(25)
            ->get()
            ->each(fn ($item) => $rows[] = [
                ucfirst($item->user_type),
                str_replace('_', ' ', ucfirst($item->aktivitas)),
                $item->total,
            ]);

        return $rows;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            7 => ['font' => ['bold' => true]],
            14 => ['font' => ['bold' => true]],
        ];
    }

    private function programStudiName(): string
    {
        $id = $this->filters['program_studi_id'] ?? null;

        return $id ? (ProgramStudi::query()->whereKey($id)->value('nama') ?: '-') : 'Semua Program Studi';
    }

    private function filteredLogs()
    {
        $query = ActivityLog::query()
            ->whereBetween('activity_logs.created_at', [$this->startDate, $this->endDate]);
        $types = ($this->filters['user_type'] ?? null)
            ? [$this->filters['user_type']]
            : ['dosen', 'mahasiswa'];
        $programStudiId = $this->filters['program_studi_id'] ?? null;
        $search = trim((string) ($this->filters['search'] ?? ''));

        $query->whereIn('activity_logs.user_type', $types);

        if ($programStudiId || $search !== '') {
            $query->where(function ($users) use ($types, $programStudiId, $search) {
                foreach ($types as $type) {
                    $table = $type === 'dosen' ? 'dosen' : 'mahasiswa';
                    $primaryKey = $type === 'dosen' ? 'dosen_id' : 'mahasiswa_id';

                    $users->orWhere(function ($typed) use ($type, $table, $primaryKey, $programStudiId, $search) {
                        $typed->where('activity_logs.user_type', $type)
                            ->whereExists(function ($account) use ($table, $primaryKey, $programStudiId, $search) {
                                $account->selectRaw('1')
                                    ->from($table)
                                    ->whereColumn($table.'.'.$primaryKey, 'activity_logs.user_id')
                                    ->when($programStudiId, fn ($filter) => $filter->where($table.'.jurusan_id', $programStudiId))
                                    ->when($search !== '', function ($filter) use ($table, $search) {
                                        $filter->where(function ($identity) use ($table, $search) {
                                            $identity->where($table.'.nama', 'like', '%'.$search.'%')
                                                ->orWhere($table.'.email', 'like', '%'.$search.'%');
                                            if ($table === 'dosen') {
                                                $identity->orWhere('dosen.nidn', 'like', '%'.$search.'%');
                                            } else {
                                                $identity->orWhere('mahasiswa.nim', 'like', '%'.$search.'%');
                                            }
                                        });
                                    });
                            });
                    });
                }
            });
        }

        return $query;
    }
}
