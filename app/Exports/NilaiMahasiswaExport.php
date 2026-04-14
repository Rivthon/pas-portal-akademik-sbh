<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NilaiMahasiswaExport implements FromView, ShouldAutoSize
{
    private $mahasiswa;
    private $konfigurasi;
    private $mataKuliah;
    private $tahunAjaran;

    public function __construct($mahasiswa, $konfigurasi, $mataKuliah, $tahunAjaran)
    {
        $this->mahasiswa = $mahasiswa;
        $this->konfigurasi = $konfigurasi;
        $this->mataKuliah = $mataKuliah;
        $this->tahunAjaran = $tahunAjaran;
    }

    public function view(): View
    {
        return view('input-nilai.export', [
            'mahasiswa' => $this->mahasiswa,
            'konfigurasi' => $this->konfigurasi,
            'mataKuliah' => $this->mataKuliah,
            'tahunAjaran' => $this->tahunAjaran,
            'isExcel' => true
        ]);
    }
}
