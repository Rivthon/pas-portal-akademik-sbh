<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Models\UapNilai;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UapController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        $nilaiUap = UapNilai::with(['tahunAjaran'])
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)

            ->get();

        return view('students.uap.index', compact('nilaiUap'));
    }
}
