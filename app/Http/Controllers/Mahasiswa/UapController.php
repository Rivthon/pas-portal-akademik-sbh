<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\UapNilai;
use Illuminate\Support\Facades\Auth;

class UapController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        $nilaiUap = UapNilai::with(['tahunAjaran'])
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)

            ->get();

        activity_log('lihat_nilai_uap', 'Mahasiswa melihat nilai UAP');

        return view('mahasiswa.uap.index', compact('nilaiUap'));
    }
}
