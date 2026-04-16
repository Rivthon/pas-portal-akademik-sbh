<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;

use App\Models\Gelombang;
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
        $gelombangsList = \App\Models\Gelombang::all();
        
        // Generate list 5 tahun terakhir untuk tahun masuk
        $tahunMasukList = [];
        $currentYear = date('Y') + 1;
        for ($i = $currentYear; $i >= 2015; $i--) {
            $tahunMasukList[] = $i;
        }

        $tahun_masuk = $request->input('tahun_masuk');
        $gelombang_id = $request->input('gelombang_id');

        if (!$request->has('tahun_masuk') && !$request->has('gelombang_id')) {
            $tenor = collect(); // Kosong
            if ($request->ajax()) {
                return response()->json([
                    'html' => view('admin.keuangan.tenor-pembayaran.partial_list', compact('tenor'))->render(),
                ]);
            }
            return view('admin.keuangan.tenor-pembayaran.index', compact('tenor', 'gelombangsList', 'tahunMasukList'));
        }

        // Start building the query
        $query = \App\Models\TenorPembayaran::query()->with('gelombang');

        if ($tahun_masuk) {
            $query->where('tahun_masuk', $tahun_masuk);
        }
        if ($gelombang_id) {
            $query->where('gelombang_id', $gelombang_id);
        }

        // Paginate the results after filtering
        $tenor = $query->paginate(50)->appends($request->query());

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.keuangan.tenor-pembayaran.partial_list', compact('tenor'))->render(),
            ]);
        }

        // Hitung index halaman
        $pageIndex = ($tenor->currentPage() - 1) * $tenor->perPage();

        return view('admin.keuangan.tenor-pembayaran.index', compact('tenor', 'pageIndex', 'gelombangsList', 'tahunMasukList'));
    }

    public function create(): View
    {
        $programStudi = ProgramStudi::all();
        $gelombang = Gelombang::all();

        return view('admin.keuangan.tenor-pembayaran.form', compact('programStudi', 'gelombang'));
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

        $gelombang = Gelombang::all();

        return view('admin.keuangan.tenor-pembayaran.form', compact('tenor', 'gelombang'));
    }

   public function store(Request $request)
    {
        $rules = [
            'semester' => 'required|integer',
            'tenor' => 'required|string|max:255',
            'persentase' => 'required|numeric|min:0|max:100',
            'batas_waktu' => 'required|date',
            'tahun_masuk' => 'required|string|max:4',
            'gelombang_id' => 'required|exists:gelombang,id',
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
            'tahun_masuk',
            'gelombang_id',
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
            'tahun_masuk' => 'required|string|max:4',
            'gelombang_id' => 'required|exists:gelombang,id',
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

        $tenor->update($request->only([
            'semester',
            'tenor',
            'persentase',
            'batas_waktu',
            'tahun_masuk',
            'gelombang_id',
        ]));

        Alert::toast('Tenor berhasil diperbarui.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.tenor-pembayaran.index');
    }


    public function destroy($id): RedirectResponse
    {
        $tenor = TenorPembayaran::findOrFail($id);
        $tenor->delete();
        Alert::toast('Tenor berhasil dihapus.', 'info')
        ->position('bottom-end')
        ->autoClose(3000);
        return redirect()->route('admin.tenor-pembayaran.index');
    }
}