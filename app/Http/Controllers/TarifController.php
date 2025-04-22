<?php

namespace App\Http\Controllers;

use App\Models\Gelombang;
use Illuminate\View\View;
use App\Imports\TarifImport;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Validation\Rule;
use App\Imports\MahasiswaImport;
use App\Models\TarifPerSemester;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;

class TarifController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:tarif-list|tarif-create|tarif-edit|tarif-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:tarif-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:tarif-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:tarif-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        // Start building the query
        $query = TarifPerSemester::with(['programStudi', 'gelombangs', 'tahunAjaran' => function ($query) {
            $query->where('status_ta', 1);
        }])->orderBy('id', 'asc');

        // Apply the search filter if provided
        if ($search) {
            $query->where('tarif', 'like', '%' . $search . '%')
                ->orWhereHas('programStudi', function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%');
                })
                ->orWhereHas('gelombangs', function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%');
                });
        }

        // Paginate the results after filtering
        $tarif = $query->paginate(10)->appends($request->query());

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'html' => view('tarif.partial_list', compact('tarif'))->render(),
            ]);
        }

        $pageIndex = ($tarif->currentPage() - 1) * $tarif->perPage();

        return view('tarif.index', compact('tarif', 'pageIndex', 'search'));
    }

    public function create(): View
    {
        $programStudi = ProgramStudi::all();
        $gelombangs = Gelombang::all();

        return view('tarif.form', compact('programStudi','gelombangs'));
    }

    public function edit(TarifPerSemester $tarif): View
    {
        $programStudi = ProgramStudi::all();
        $gelombangs = Gelombang::all();

        return view('tarif.form', compact('tarif', 'programStudi','gelombangs'));
    }

    public function store(Request $request)
    {
        $rules = [
            'jurusan_id' => 'required|string',
            'semester' => 'required',
            'tarif' => 'required|numeric',
            'tahun_masuk' => 'required',
        ];

        $messages = [
            'jurusan_id.required' => 'Jurusan ID wajib diisi.',
            'semester.required' => 'Semester wajib diisi.',
            'tarif.required' => 'Tarif wajib diisi.',
            'tarif.numeric' => 'Tarif harus berupa angka.',
            'tahun_masuk.required' => 'Tahun masuk wajib diisi.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'jurusan_id',
            'semester',
            'tarif',
            'tahun_masuk',
            'gelombang_id'
        ]);


        TarifPerSemester::create($data);

        Alert::toast('Tarif berhasil ditambahkan.', 'success')
        ->position('bottom-end')
        ->autoClose(3000);

        return redirect()->route('admin.tarif.index');
    }
    public function update(Request $request, TarifPerSemester $tarif)
    {
        $rules = [
            'jurusan_id' => 'required|string',
            'semester' => 'required',
            'tarif' => 'required|numeric',
            'tahun_masuk' => 'required',
        ];

        $messages = [
            'jurusan_id.required' => 'Jurusan ID wajib diisi.',
            'semester.required' => 'Semester wajib diisi.',
            'tarif.required' => 'Tarif wajib diisi.',
            'tarif.numeric' => 'Tarif harus berupa angka.',
            'tahun_masuk.required' => 'Tahun masuk wajib diisi.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'jurusan_id',
            'semester',
            'tarif',
            'tahun_masuk',
              'gelombang_id'
        ]);

        $tarif->update($data);

        Alert::toast('Tarif berhasil diperbarui.', 'success')
        ->position('bottom-end')
        ->autoClose(3000);

        return redirect()->route('admin.tarif.index');
    }

    public function destroy(TarifPerSemester $tarif): RedirectResponse
    {
        $tarif->delete();
        Alert::toast('Tarif berhasil delete.', 'info')
        ->position('bottom-end')
        ->autoClose(3000);
        return redirect()->route('admin.tarif.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        $import = new TarifImport;
        Excel::import($import, $request->file('file'));
        $rowCount = $import->getRowCount();

        Alert::toast("Data tarif berhasil diimport. Jumlah data: $rowCount", 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->back();
    }

    public function downloadTemplate()
    {
        $filePath = public_path('templates/template_tarif.xlsx');
        $fileName = 'template_tarif.xlsx';

        \Log::info('File path:', ['filePath' => $filePath]);

        if (file_exists($filePath)) {
            return response()->download($filePath, $fileName);
        }

        return redirect()->back()->with('error', 'File template tidak ditemukan.');
    }

}