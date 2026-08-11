<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Gelombang;
use App\Models\ProgramStudi;
use App\Models\TenorPembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

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
        $gelombangsList = Gelombang::all();

        // Generate list 5 tahun terakhir untuk tahun masuk
        $tahunMasukList = [];
        $currentYear = date('Y') + 1;
        for ($i = $currentYear; $i >= 2015; $i--) {
            $tahunMasukList[] = $i;
        }

        $tahun_masuk = $request->input('tahun_masuk');
        $gelombang_id = $request->input('gelombang_id');

        if (! $request->has('tahun_masuk') && ! $request->has('gelombang_id')) {
            $tenor = collect(); // Kosong
            if ($request->ajax()) {
                return response()->json([
                    'html' => view('admin.keuangan.tenor-pembayaran.partial_list', compact('tenor'))->render(),
                ]);
            }

            return view('admin.keuangan.tenor-pembayaran.index', compact('tenor', 'gelombangsList', 'tahunMasukList'));
        }

        // Start building the query
        $query = TenorPembayaran::query()->with('gelombang');

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

        $newTenor = TenorPembayaran::create($request->only([
            'semester',
            'tenor',
            'persentase',
            'batas_waktu',
            'tahun_masuk',
            'gelombang_id',
        ]));

        activity_log('tambah_tenor', 'Admin menambah tenor pembayaran: '.$request->tenor.' (Semester '.$request->semester.')');

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
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

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

        activity_log('update_tenor', 'Admin memperbarui tenor pembayaran ID: '.$tenor->id);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tenor berhasil diperbarui.',
            ]);
        }

        Alert::toast('Tenor berhasil diperbarui.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.tenor-pembayaran.index');
    }

    public function destroy($id)
    {
        $tenor = TenorPembayaran::findOrFail($id);
        activity_log('hapus_tenor', 'Admin menghapus tenor pembayaran ID: '.$id);
        $tenor->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tenor berhasil dihapus.',
            ]);
        }

        Alert::toast('Tenor berhasil dihapus.', 'info')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.tenor-pembayaran.index');
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'tenor_data' => 'required|array',
            'tenor_data.*.semester' => 'required|integer',
            'tenor_data.*.tenor' => 'required|string',
            'tenor_data.*.persentase' => 'required|numeric|min:0|max:100',
            'tenor_data.*.batas_waktu' => 'required|date',
            'tenor_data.*.tahun_masuk' => 'required',
            'tenor_data.*.gelombang_id' => 'required|exists:gelombang,id',
        ], [
            'tenor_data.required' => 'Data tenor tidak boleh kosong.',
            'tenor_data.*.semester.required' => 'Semester wajib diisi pada setiap baris.',
            'tenor_data.*.tenor.required' => 'Nama Cicilan wajib diisi.',
            'tenor_data.*.persentase.required' => 'Persentase wajib diisi.',
            'tenor_data.*.batas_waktu.required' => 'Batas waktu wajib diisi.',
            'tenor_data.*.tahun_masuk.required' => 'Tahun masuk wajib diisi.',
            'tenor_data.*.gelombang_id.required' => 'Gelombang wajib dipilih.',
        ]);

        $count = 0;
        foreach ($request->tenor_data as $data) {
            TenorPembayaran::create([
                'semester' => $data['semester'],
                'tenor' => $data['tenor'],
                'persentase' => $data['persentase'],
                'batas_waktu' => $data['batas_waktu'],
                'tahun_masuk' => $data['tahun_masuk'],
                'gelombang_id' => $data['gelombang_id'],
            ]);
            $count++;
        }

        activity_log('tambah_bulk_tenor', 'Admin menambah '.$count.' data tenor pembayaran secara masal.');

        Alert::toast($count.' data Tenor berhasil ditambahkan.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.tenor-pembayaran.index');
    }
}
