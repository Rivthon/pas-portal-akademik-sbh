<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BobotNilaiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_studi_id' => 'required',
            'matakuliah_id' => 'required',
            'persen_tugas' => 'required|numeric',
            'persen_uts' => 'required|numeric',
            'persen_uas' => 'required|numeric',
            'persen_absen' => 'required|numeric',
            'persen_praktik' => 'nullable|numeric',
        ]);

        BobotNilai::updateOrCreate(
            [
                'program_studi_id' => $request->program_studi_id,
                'matakuliah_id' => $request->matakuliah_id
            ],
            $validated
        );

        return back()->with('success', 'Bobot nilai berhasil disimpan.');
    }
}
