<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\CalenderAkademik;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class CalenderAkademikController extends Controller
{
    /**
     * Menampilkan daftar kalender akademik berdasarkan ProgramStudi.
     */
    public function index()
    {
        $programStudi = ProgramStudi::all();
        $kalender = CalenderAkademik::with('programStudi')->get();
        return view('calender-akademik.index', compact('kalender','programStudi'));
    }

    /**
     * Menampilkan form edit kalender akademik.
     */
    public function edit($id)
    {
        $kalender = CalenderAkademik::findOrFail($id);
        $programStudi = ProgramStudi::all();
        return view('calender-akademik.edit', compact('kalender', 'programStudi'));
    }

    /**
     * Mengupdate data kalender akademik.
     */
  public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'jurusan_id' => 'required|exists:program_studi,jurusan_id',
            'status' => 'required|boolean',
            'file' => 'nul  lable|mimes:pdf|max:2048', // PDF maksimal 2MB
        ]);

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // Ambil data berdasarkan ID
            $kalender = CalenderAkademik::findOrFail($id);

            // Jika ada file baru yang diupload, hapus file lama dan simpan yang baru
            if ($request->hasFile('file')) {
                // Hapus file lama jika ada
                if ($kalender->path && Storage::disk('public')->exists($kalender->path)) {
                    Storage::disk('public')->delete($kalender->path);
                }

                // Simpan file baru di public storage
                $file = $request->file('file');
                $filePath = $file->store('calender_akademik', 'public'); // Simpan ke storage/app/public/calender_akademik
                $kalender->nama_file = $file->getClientOriginalName();
                $kalender->path = $filePath;
            }

            // Update data kalender akademik
            $kalender->jurusan_id = $request->jurusan_id;
            $kalender->status = $request->status;

            // Jika status aktif, pastikan hanya satu yang aktif
            if ($request->status == 1) {
                CalenderAkademik::where('jurusan_id', $request->jurusan_id)
                    ->where('id', '!=', $kalender->id)
                    ->update(['status' => 0]);
            }

            $kalender->save();

            // Commit transaksi jika tidak ada error
            DB::commit();

            // Tampilkan notifikasi sukses
            Alert::toast('Kalender Akademik berhasil diperbarui.', 'success')
                ->position('bottom-end')
                ->autoClose(3000);

            return redirect()->route('admin.calender.index');
        } catch (\Exception $e) {
            // Rollback transaksi jika ada kesalahan
            DB::rollBack();

            // Tampilkan notifikasi error
            Alert::error('Gagal memperbarui Kalender Akademik', 'Terjadi kesalahan: ' . $e->getMessage())
                ->position('bottom-end')
                ->autoClose(5000);

            return back()->withInput(); // Kembali ke halaman sebelumnya dengan data input
        }
    }

}
