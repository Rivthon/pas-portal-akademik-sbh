<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Permintaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PermintaanController extends Controller
{
    public function index(): View
    {
        $dosen = Auth::guard('dosen')->user();
        $permintaan = Permintaan::query()
            ->where('dosen_id', $dosen->dosen_id)
            ->latest()
            ->paginate(10);

        return view('dosen.permintaan.index', compact('dosen', 'permintaan'));
    }

    public function create(): View
    {
        return view('dosen.permintaan.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_permintaan' => ['required', 'in:bug,fitur,akses,lainnya'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'prioritas' => ['required', 'in:rendah,sedang,tinggi,urgen'],
            'file_lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,zip', 'max:2048'],
        ]);

        $validated['dosen_id'] = Auth::guard('dosen')->id();
        $validated['mahasiswa_id'] = null;
        $validated['status'] = 'menunggu';

        if ($request->hasFile('file_lampiran')) {
            $validated['file_lampiran'] = $request->file('file_lampiran')->store('lampiran_permintaan', 'public');
        }

        Permintaan::create($validated);
        activity_log('buat_permintaan', 'Dosen membuat permintaan Helpdesk: '.$validated['judul']);

        return redirect()->route('dosen.permintaan.index')
            ->with('success', 'Permintaan berhasil dikirim ke Helpdesk.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $permintaan = Permintaan::query()
            ->where('dosen_id', Auth::guard('dosen')->id())
            ->findOrFail($id);

        if (! in_array($permintaan->status, ['menunggu', 'revisi'], true)) {
            return back()->with('error', 'Permintaan yang sedang atau sudah diproses tidak dapat dihapus.');
        }

        if ($permintaan->file_lampiran) {
            Storage::disk('public')->delete($permintaan->file_lampiran);
        }

        $permintaan->delete();

        return back()->with('success', 'Permintaan berhasil dihapus.');
    }
}
