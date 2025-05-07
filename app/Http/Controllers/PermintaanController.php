<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use Illuminate\Http\Request;
use App\Http\Requests\StorePermintaanRequest;
use App\Http\Requests\UpdatePermintaanRequest;

class PermintaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        $query = Permintaan::with('mahasiswa');

        if ($request->has('search')) {
            $query->whereHas('mahasiswa', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        $permintaan = $query->orderByDesc('created_at')->paginate(10);

        if ($request->ajax()) {
            return view('permintaan.table', compact('permintaan'))->render();
        }

        return view('permintaan.index', compact('permintaan'));
    }


    public function show($id)
    {
        $permintaan = Permintaan::with('mahasiswa')->findOrFail($id);
        return view('permintaan.show', compact('permintaan'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,diproses,selesai,ditolak'
        ]);

        $permintaan = Permintaan::findOrFail($id);
        $permintaan->status = $request->status;
        $permintaan->komentar_admin = $request->komentar_admin;
        $permintaan->save();

        Allert::success('Status permintaan berhasil diperbarui.');
        return redirect()->route('admin.helpdesk.index');
    }
}
