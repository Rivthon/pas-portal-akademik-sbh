<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AsprakAssignment;
use Illuminate\Http\Request;

class AsprakController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = $request->user('mahasiswa');
        $assignments = AsprakAssignment::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->with([
                'jadwal.kurikulum.mataKuliah',
                'jadwal.programStudi',
                'jadwal.tahunAjaran',
                'absensi' => fn ($query) => $query
                    ->with(['pertemuan.dosen', 'diabsenOleh'])
                    ->orderByDesc('pertemuan_praktik_id'),
            ])
            ->where(function ($query) {
                $query->where('aktif', true)->orWhereHas('absensi');
            })
            ->latest('updated_at')
            ->get();

        abort_if($assignments->isEmpty(), 403, 'Anda belum terdaftar sebagai Asisten Praktikum.');

        $statistics = [
            'penugasan' => $assignments->count(),
            'pertemuan' => $assignments->sum(fn ($assignment) => $assignment->absensi->count()),
            'hadir' => $assignments->sum(fn ($assignment) => $assignment->absensi->where('status', 'hadir')->count()),
        ];

        activity_log('lihat_rekap_asprak', 'Mahasiswa melihat rekap kehadiran Asprak');

        return view('mahasiswa.asprak.index', compact('assignments', 'statistics'));
    }
}
