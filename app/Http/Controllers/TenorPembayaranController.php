<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use App\Models\TenorPembayaran;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;
use Carbon\Carbon;


class TenorPembayaranController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:tenor-list|tenor-create|tenor-edit|tenor-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:tenor-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:tenor-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:tenor-delete', ['only' => ['destroy']]);
    }

   public function index(Request $request)
{
    $search = $request->input('search');

    // Start building the query
    $query = TenorPembayaran::query();

    // Apply the search filter if provided
    if ($search) {
        $query->where('tenor', 'like', '%' . $search . '%')
            ->orWhereHas('programStudi', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%');
            });
    }

    // Paginate the results after filtering
    $tenor = $query->paginate(10)->appends($request->query());

    // Check if request is AJAX
    if ($request->ajax()) {
        return response()->json([
            'html' => view('tenor-pembayaran.partial_list', compact('tenor'))->render(),
        ]);
    }

    // Hitung index halaman
    $pageIndex = ($tenor->currentPage() - 1) * $tenor->perPage();

    return view('tenor-pembayaran.index', compact('tenor', 'pageIndex', 'search'));
    }

    public function create(): View
    {
        $programStudi = ProgramStudi::all();

        return view('tenor-pembayaran.form', compact('programStudi'));
    }


   public function edit($id)
    {
        $tenor = TenorPembayaran::findOrFail($id);

        // Format tanggal agar sesuai dengan input date HTML
        $tenor->batas_waktu = $tenor->batas_waktu ? Carbon::parse($tenor->batas_waktu)->format('Y-m-d') : null;

        // Ubah persentase jika dalam bentuk desimal
        if ($tenor->persentase !== null && $tenor->persentase < 1) {
            $tenor->persentase *= 100;
        }

        return view('tenor-pembayaran.form', compact('tenor'));
    }

   public function store(Request $request)
    {
        $rules = [
            'semester' => 'required|integer',
            'tenor' => 'required|string|max:255',
            'persentase' => 'required|numeric|min:0|max:100',
            'batas_waktu' => 'required|date',
        ];

        $messages = [
            'semester.required' => 'Semester wajib diisi.',
            'semester.integer' => 'Semester harus berupa angka.',
            'tenor.required' => 'Tenor wajib diisi.',
            'persentase.required' => 'Persentase wajib diisi.',
            'persentase.numeric' => 'Persentase harus berupa angka.',
            'persentase.min' => 'Persentase tidak boleh kurang dari 0%.',
            'persentase.max' => 'Persentase tidak boleh lebih dari 100%.',
            'batas_waktu.required' => 'Batas waktu wajib diisi.',
            'batas_waktu.date' => 'Batas waktu harus berupa tanggal yang valid.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        TenorPembayaran::create($request->only([
            'semester',
            'tenor',
            'persentase',
            'batas_waktu',
        ]));

        Alert::toast('Tenor berhasil ditambahkan.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.tenor-pembayaran.index');
    }

   public function update(Request $request, $id)
    {
        // Temukan data berdasarkan ID
        $tenor = TenorPembayaran::findOrFail($id);

        // Validasi input
        $rules = [
            'semester' => 'required|integer',
            'tenor' => 'required|string|max:255',
            'persentase' => 'required|numeric|min:0|max:100',
            'batas_waktu' => 'required|date',
        ];

        $messages = [
            'semester.required' => 'Semester wajib diisi.',
            'semester.integer' => 'Semester harus berupa angka.',
            'tenor.required' => 'Tenor wajib diisi.',
            'persentase.required' => 'Persentase wajib diisi.',
            'persentase.numeric' => 'Persentase harus berupa angka.',
            'persentase.min' => 'Persentase tidak boleh kurang dari 0%.',
            'persentase.max' => 'Persentase tidak boleh lebih dari 100%.',
            'batas_waktu.required' => 'Batas waktu wajib diisi.',
            'batas_waktu.date' => 'Batas waktu harus berupa tanggal yang valid.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update data
        $tenor->update($request->only([
            'semester',
            'tenor',
            'persentase',
            'batas_waktu',
        ]));

        Alert::toast('Tenor berhasil diperbarui.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.tenor-pembayaran.index');
    }


    public function destroy(TenorPembayaran $tarif): RedirectResponse
    {
        $tarif->delete();
        Alert::toast('Tarif berhasil delete.', 'info')
        ->position('bottom-end')
        ->autoClose(3000);
        return redirect()->route('admin.tarif.index');
    }
}