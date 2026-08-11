<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Pertemuan;
use Illuminate\Http\Request;

class PertemuanController extends Controller
{
    public function index($jadwalId)
    {
        // Ambil data jadwal dengan pertemuan
        $jadwal = Jadwal::with('pertemuan')->find($jadwalId);

        if (! $jadwal) {
            return redirect()->route('admin.jadwal.index')->withErrors('Jadwal tidak ditemukan.');
        }

        return view('admin.akademik.pertemuan.index', [
            'jadwal' => $jadwal,
            'pertemuan' => $jadwal->pertemuan ?? [],
        ]);
    }

    /**
     * Simpan data pertemuan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,jadwal_id',
            'tanggal_pertemuan' => 'required|date',
            'topik' => 'nullable|string|max:255',
            // 'waktu_mulai' => 'required|date_format:H:i',
            // 'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
        ]);

        // Buat data pertemuan baru
        Pertemuan::create([
            'jadwal_id' => $request->jadwal_id,
            'tanggal_pertemuan' => $request->tanggal_pertemuan,
            'topik' => $request->topik,
            // 'waktu_mulai' => $request->waktu_mulai,
            // 'waktu_selesai' => $request->waktu_selesai,
        ]);

        return redirect()->route('admin.pertemuan.index', ['jadwalId' => $request->jadwal_id])
            ->with('success', 'Pertemuan berhasil dibuat!');
    }

    public function toggleStatus($pertemuan_id)
    {
        // Find the pertemuan record by ID
        $pertemuan = Pertemuan::findOrFail($pertemuan_id);

        // Toggle the status between 1 (Aktif) and 0 (Tidak Aktif)
        $pertemuan->status = ($pertemuan->status == 1) ? 0 : 1;
        $pertemuan->save();

        // Retrieve the jadwalId from the pertemuan
        $jadwalId = $pertemuan->jadwal_id;

        // Redirect back to the pertemuan index with the jadwalId parameter
        return redirect()->route('admin.pertemuan.index', ['jadwalId' => $jadwalId])
            ->with('success', 'Status pertemuan berhasil diperbarui.');
    }

    public function edit($jadwal_id, $id)
    {
        $pertemuan = Pertemuan::findOrFail($id);

        return view('admin.akademik.pertemuan.edit', compact('jadwal_id', 'pertemuan'));
    }

    public function update(Request $request, $jadwal_id, $id)
    {
        $pertemuan = Pertemuan::findOrFail($id);

        $validatedData = $request->validate([
            'topik' => 'required|string|max:255',
            'tanggal_pertemuan' => 'required|date',
            'status' => 'required|in:1,0',
        ]);

        $pertemuan->update($validatedData);

        return redirect()->route('admin.pertemuan.index', $jadwal_id)->with('success', 'Pertemuan berhasil diperbarui.');
    }

    public function destroy($jadwal_id, $id)
    {
        $pertemuan = Pertemuan::findOrFail($id);
        $pertemuan->delete();

        return redirect()->route('admin.pertemuan.index', $jadwal_id)->with('success', 'Pertemuan berhasil dihapus.');
    }
}
