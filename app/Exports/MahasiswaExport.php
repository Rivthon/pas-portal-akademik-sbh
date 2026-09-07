<?php

namespace App\Exports;

use App\Models\Mahasiswa;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MahasiswaExport implements FromView
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = Mahasiswa::with('programStudi');

        if ($this->filters['search']) {
            $keywords = explode(' ', $this->filters['search']);
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('nama', 'like', '%'.$keyword.'%')
                        ->orWhere('nim', 'like', '%'.$keyword.'%')
                        ->orWhere('tahun_masuk', 'like', '%'.$keyword.'%')
                        ->orWhere('status_mhs', 'like', '%'.$keyword.'%')
                        ->orWhereHas('programStudi', function ($subQuery) use ($keyword) {
                            $subQuery->where('nama', 'like', '%'.$keyword.'%');
                        });
                }
            });
        }

        if (! empty($this->filters['programStudi'])) {
            $query->where('jurusan_id', $this->filters['programStudi']);
        }

        if (! empty($this->filters['tahunMasuk'])) {
            $query->where('tahun_masuk', $this->filters['tahunMasuk']);
        }

        if (! empty($this->filters['status'])) {
            $query->where('status_mhs', $this->filters['status']);
        }

        if (! empty($this->filters['kelas'])) {
            $this->filters['kelas'] === 'pagi'
                ? $query->whereIn('kelas', ['pagi', 'reguler', 'regular'])
                : $query->where('kelas', 'karyawan');
        }

        $mahasiswa = $query->get();

        return view('exports.mahasiswa', compact('mahasiswa'));
    }
}
