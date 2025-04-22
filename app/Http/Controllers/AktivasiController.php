<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AktivasiController extends Controller
{
    public function index(Request $request)
    {
        // Ambil input pencarian (jika ada)
        $search = $request->input('search');

        // Query data mahasiswa dengan relasi program studi
        $query = Mahasiswa::with('programStudi')->where('status_mhs', 'aktif');

        // Filter pencarian jika input ada
        if ($search) {
            $query->where('nama', 'like', '%' . $search . '%')
                ->orWhere('nim', 'like', '%' . $search . '%')
                ->orWhereHas('programStudi', function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%');
                });
        }

        // Paginate hasil query
        $mahasiswa = $query->paginate(15)->appends($request->query());

        // Periksa apakah request melalui AJAX
        if ($request->ajax()) {
            return response()->json([
                'html' => view('aktivasi-mhs.partials_list', compact('mahasiswa'))->render(),
            ]);
        }

        // Hitung nomor indeks untuk paginasi
        $pageIndex = ($mahasiswa->currentPage() - 1) * $mahasiswa->perPage();

        // Kirim data ke view utama
        return view('aktivasi-mhs.index', compact('mahasiswa', 'pageIndex', 'search'));
    }


public function updateStatus(Request $request)
{

      try {
        // Log data yang diterima
        \Log::info('Request Data:', $request->all());

        $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);
        $field = 'status_' . $request->type; // Menentukan field yang akan diupdate

        if (in_array($field, ['status_krs', 'status_uts', 'status_uas', 'status_nilai_uts', 'status_nilai_uas', 'status_khs'])) {
            $mahasiswa->$field = $request->status;
            $mahasiswa->save();

            return response()->json([
                'success' => true,
                'message' => ucfirst($request->type) . " berhasil diperbarui.",
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Field tidak valid.']);
    } catch (\Exception $e) {
        \Log::error('Error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Terjadi kesalahan.']);
    }
}

public function resetAllStatus()
{
    try {
        // Reset semua status menjadi 0
        Mahasiswa::query()->update([
            'status_krs' => 0,
            'status_uts' => 0,
            'status_uas' => 0,
            'status_nilai_uts' => 0,
            'status_nilai_uas' => 0,
            'status_khs' => 0,
            'status_uap' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Semua status berhasil direset.',
        ]);
    } catch (\Exception $e) {
        \Log::error('Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat mereset status.',
        ]);
    }
}

}
