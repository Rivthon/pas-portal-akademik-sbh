<?php

namespace App\Exports;

use App\Exports\Sheets\UsageReportSummarySheet;
use App\Exports\Sheets\UsageReportUserSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UsageReportExport implements WithMultipleSheets
{
    public function __construct(private readonly array $filters)
    {
    }

    public function sheets(): array
    {
        $sheets = [new UsageReportSummarySheet($this->filters)];
        $userType = $this->filters['user_type'] ?? null;

        if (! $userType || $userType === 'dosen') {
            $sheets[] = new UsageReportUserSheet('dosen', $this->filters);
        }

        if (! $userType || $userType === 'mahasiswa') {
            $sheets[] = new UsageReportUserSheet('mahasiswa', $this->filters);
        }

        return $sheets;
    }
}
