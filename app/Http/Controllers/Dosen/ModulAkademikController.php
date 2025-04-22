<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ModulAkademikController extends Controller
{
    public function index()
    {
        return view('pages-dosen.nilai.index');
    }

    public function inputNilai()
    {
        return view('pages-dosen.nilai.input-nilai');
    }
     public function indexRps()
    {
        return view('pages-dosen.materi.index');
    }

    public function tambahRps()
    {
        return view('pages-dosen.materi.create');
    }
}