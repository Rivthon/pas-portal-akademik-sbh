<?php

namespace App\Exports\Sheets;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsageReportUserSheet implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private Carbon $startDate;

    private Carbon $endDate;

    private int $number = 0;

    public function __construct(private readonly string $userType, private readonly array $filters)
    {
        $this->startDate = Carbon::parse($filters['start_date'] ?? now()->subDays(29)->toDateString())->startOfDay();
        $this->endDate = Carbon::parse($filters['end_date'] ?? now()->toDateString())->endOfDay();
    }

    public function query()
    {
        $usage = DB::table('activity_logs')
            ->where('user_type', $this->userType)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->select('user_id')
            ->selectRaw('COUNT(*) as total_aktivitas')
            ->selectRaw("SUM(CASE WHEN aktivitas = 'login' THEN 1 ELSE 0 END) as total_login")
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as hari_aktif')
            ->selectRaw('MAX(created_at) as aktivitas_terakhir')
            ->groupBy('user_id');

        return $this->userType === 'dosen'
            ? $this->dosenQuery($usage)
            : $this->mahasiswaQuery($usage);
    }

    public function headings(): array
    {
        if ($this->userType === 'dosen') {
            return ['No', 'Nama Dosen', 'NIDN', 'Email', 'Program Studi', 'Status', 'Jumlah Login', 'Total Aktivitas', 'Hari Aktif', 'Terakhir Aktif'];
        }

        return ['No', 'NIM', 'Nama Mahasiswa', 'Email', 'Program Studi', 'Semester', 'Kelas', 'Status', 'Jumlah Login', 'Total Aktivitas', 'Hari Aktif', 'Terakhir Aktif'];
    }

    public function map($row): array
    {
        $lastActivity = $row->aktivitas_terakhir
            ? Carbon::parse($row->aktivitas_terakhir)->format('d/m/Y H:i')
            : 'Tidak ada aktivitas';

        if ($this->userType === 'dosen') {
            return [++$this->number, $row->nama, $row->nidn, $row->email, $row->program_studi_nama,
                $row->status_dosen, $row->total_login, $row->total_aktivitas, $row->hari_aktif, $lastActivity];
        }

        return [++$this->number, $row->nim, $row->nama, $row->email, $row->program_studi_nama,
            $row->semester, $row->kelas, $row->status_mhs, $row->total_login, $row->total_aktivitas,
            $row->hari_aktif, $lastActivity];
    }

    public function title(): string
    {
        return $this->userType === 'dosen' ? 'Dosen' : 'Mahasiswa';
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    private function dosenQuery($usage)
    {
        return Dosen::query()
            ->leftJoinSub($usage, 'usage', fn ($join) => $join->on('usage.user_id', '=', 'dosen.dosen_id'))
            ->leftJoin('program_studi', 'program_studi.jurusan_id', '=', 'dosen.jurusan_id')
            ->select(['dosen.dosen_id', 'dosen.nama', 'dosen.nidn', 'dosen.email', 'dosen.status_dosen', 'program_studi.nama as program_studi_nama'])
            ->selectRaw('COALESCE(usage.total_aktivitas, 0) as total_aktivitas, COALESCE(usage.total_login, 0) as total_login, COALESCE(usage.hari_aktif, 0) as hari_aktif')
            ->addSelect('usage.aktivitas_terakhir')
            ->when($this->filters['program_studi_id'] ?? null, fn ($query, $id) => $query->where('dosen.jurusan_id', $id))
            ->when(trim((string) ($this->filters['search'] ?? '')) !== '', function ($query) {
                $search = trim((string) $this->filters['search']);
                $query->where(fn ($filter) => $filter->where('dosen.nama', 'like', '%'.$search.'%')->orWhere('dosen.nidn', 'like', '%'.$search.'%')->orWhere('dosen.email', 'like', '%'.$search.'%'));
            })
            ->orderByRaw('usage.aktivitas_terakhir IS NULL')
            ->orderByDesc('usage.aktivitas_terakhir')
            ->orderBy('dosen.nama');
    }

    private function mahasiswaQuery($usage)
    {
        return Mahasiswa::query()
            ->leftJoinSub($usage, 'usage', fn ($join) => $join->on('usage.user_id', '=', 'mahasiswa.mahasiswa_id'))
            ->leftJoin('program_studi', 'program_studi.jurusan_id', '=', 'mahasiswa.jurusan_id')
            ->select(['mahasiswa.mahasiswa_id', 'mahasiswa.nim', 'mahasiswa.nama', 'mahasiswa.email', 'mahasiswa.status_mhs', 'mahasiswa.semester', 'mahasiswa.kelas', 'program_studi.nama as program_studi_nama'])
            ->selectRaw('COALESCE(usage.total_aktivitas, 0) as total_aktivitas, COALESCE(usage.total_login, 0) as total_login, COALESCE(usage.hari_aktif, 0) as hari_aktif')
            ->addSelect('usage.aktivitas_terakhir')
            ->when($this->filters['program_studi_id'] ?? null, fn ($query, $id) => $query->where('mahasiswa.jurusan_id', $id))
            ->when(trim((string) ($this->filters['search'] ?? '')) !== '', function ($query) {
                $search = trim((string) $this->filters['search']);
                $query->where(fn ($filter) => $filter->where('mahasiswa.nama', 'like', '%'.$search.'%')->orWhere('mahasiswa.nim', 'like', '%'.$search.'%')->orWhere('mahasiswa.email', 'like', '%'.$search.'%'));
            })
            ->orderByRaw('usage.aktivitas_terakhir IS NULL')
            ->orderByDesc('usage.aktivitas_terakhir')
            ->orderBy('mahasiswa.nama');
    }
}
