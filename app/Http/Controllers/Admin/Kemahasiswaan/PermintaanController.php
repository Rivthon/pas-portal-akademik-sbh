<?php

namespace App\Http\Controllers\Admin\Kemahasiswaan;

use App\Http\Controllers\Controller;
use App\Models\Permintaan;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class PermintaanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:permintaan-list', ['only' => ['index']]);
        $this->middleware('permission:permintaan-show', ['only' => ['show']]);
        $this->middleware('permission:permintaan-edit', ['only' => ['edit']]);
        $this->middleware('permission:permintaan-status', ['only' => ['updateStatus']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Permintaan::with(['mahasiswa', 'dosen']);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->whereHas('mahasiswa', function ($mahasiswaQuery) use ($search) {
                    $mahasiswaQuery->where('nama', 'like', '%'.$search.'%');
                })->orWhereHas('dosen', function ($dosenQuery) use ($search) {
                    $dosenQuery->where('nama', 'like', '%'.$search.'%');
                })->orWhere('judul', 'like', '%'.$search.'%');
            });
        }

        $permintaan = $query->orderByDesc('created_at')->paginate(10);

        if ($request->ajax()) {
            return view('admin.kemahasiswaan.permintaan.table', compact('permintaan'))->render();
        }

        return view('admin.kemahasiswaan.permintaan.index', compact('permintaan'));
    }

    public function show($id)
    {
        $permintaan = Permintaan::with(['mahasiswa', 'dosen'])->findOrFail($id);

        return view('admin.kemahasiswaan.permintaan.show', compact('permintaan'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:menunggu,disetujui,ditolak,revisi,selesai',
            'komentar_admin' => 'nullable|string|max:2000',
        ]);

        $permintaan = Permintaan::findOrFail($id);
        $permintaan->status = $request->status;
        $permintaan->komentar_admin = $request->komentar_admin;
        $permintaan->save();

        activity_log('update_status_permintaan', 'Admin mengubah status permintaan ID: '.$id.' menjadi '.$request->status);

        Alert::success('Status permintaan berhasil diperbarui.');

        return redirect()->route('admin.helpdesk.index');
    }
}
