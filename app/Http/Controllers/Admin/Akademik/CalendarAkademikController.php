<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\CalendarAkademik;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class CalendarAkademikController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:kalender-list', ['only' => ['index']]);
        $this->middleware('permission:kalender-edit', ['only' => ['edit', 'update']]);
    }

    /**
     * Menampilkan daftar kalender akademik berdasarkan ProgramStudi.
     */
    public function index()
    {
        $programStudi = ProgramStudi::all();
        $kalender = CalendarAkademik::with('programStudi')->get();

        return view('admin.akademik.calendar-akademik.index', compact('kalender', 'programStudi'));
    }

    /**
     * Menampilkan form edit kalender akademik.
     */
    public function edit($id)
    {
        $kalender = CalendarAkademik::findOrFail($id);
        $programStudi = ProgramStudi::all();

        return view('admin.akademik.calendar-akademik.edit', compact('kalender', 'programStudi'));
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
            'file' => 'nullable|file|mimes:pdf|max:2048', // PDF maksimal 2MB
        ]);

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // Ambil data berdasarkan ID
            $kalender = CalendarAkademik::findOrFail($id);
            $jurusanSebelumnya = $kalender->jurusan_id;

            // Jika ada file baru yang diupload, hapus file lama dan simpan yang baru
            if ($request->hasFile('file')) {
                // Hapus file lama jika ada
                if ($kalender->fileExists()) {
                    Storage::disk('public')->delete($kalender->storagePath());
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
                CalendarAkademik::where('jurusan_id', $request->jurusan_id)
                    ->where('id', '!=', $kalender->id)
                    ->update(['status' => 0]);
            }

            $kalender->save();

            // Commit transaksi jika tidak ada error
            DB::commit();

            Cache::forget("kalender_akademik_{$jurusanSebelumnya}");
            Cache::forget("kalender_akademik_{$kalender->jurusan_id}");

            // Tampilkan notifikasi sukses
            Alert::toast('Kalender Akademik berhasil diperbarui.', 'success')
                ->position('bottom-end')
                ->autoClose(3000);

            return redirect()->route('admin.calender.index');
        } catch (\Exception $e) {
            // Rollback transaksi jika ada kesalahan
            DB::rollBack();

            // Tampilkan notifikasi error
            Alert::error('Gagal memperbarui Kalender Akademik', 'Terjadi kesalahan: '.$e->getMessage())
                ->position('bottom-end')
                ->autoClose(5000);

            return back()->withInput(); // Kembali ke halaman sebelumnya dengan data input
        }
    }
}
