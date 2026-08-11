<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GradebookExport implements FromView, ShouldAutoSize
{
    public function __construct(private array $data) {}

    public function view(): View
    {
        return view('dosen.lms.gradebook-excel', $this->data);
    }
}
