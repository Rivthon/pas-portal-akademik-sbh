<?php

namespace App\Http\Controllers;

use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;


class MatakuliahController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:matakuliah-list|matakuliah-create|matakuliah-edit|matakuliah-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:matakuliah-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:matakuliah-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:matakuliah-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        // Start building the query
        $query = Matakuliah::with('programStudi');

        // Apply the search filter if provided
        if ($search) {
            $query->where('nama', 'like', '%' . $search . '%')
                ->orWhereHas('programStudi', function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%');
                });
        }

        // Paginate the results after filtering
        $matakuliah = $query->paginate(10)->appends($request->query());

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'html' => view('matakuliah.partial_list', compact('matakuliah'))->render(),
            ]);
        }

        $pageIndex = ($matakuliah->currentPage() - 1) * $matakuliah->perPage();

        return view('matakuliah.index', compact('matakuliah', 'pageIndex', 'search'));
    }

    public function create(): View
    {
        $programStudi = ProgramStudi::all();

        return view('matakuliah.form', compact('programStudi'));
    }

    public function edit(Matakuliah $matakuliah): View
    {
        $programStudi = ProgramStudi::all();

        return view('matakuliah.form', compact('matakuliah', 'programStudi'));
    }

    public function store(Request $request)
    {
        $rules = [
            'matakuliah_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('matakuliah', 'matakuliah_id'),
            ],
            'jurusan_id' => 'required|string',
            'nama' => 'required|string|max:255',
            'kategori_mk' => 'required|integer|in:0,1',
            'sks' => 'required|integer|min:1|max:10',
            'smt' => 'required|integer|min:1|max:8',
            'semester' => 'required|string|in:ganjil,genap',
        ];

        $messages = [
            'matakuliah_id.required' => 'ID Mata Kuliah wajib diisi.',
            'nama.required' => 'Nama mata kuliah tidak boleh kosong.',
            'sks.integer' => 'SKS harus berupa angka.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'matakuliah_id',
            'jurusan_id',
            'nama',
            'kategori_mk',
            'sks',
            'smt',
            'semester',
        ]);

        Matakuliah::create($data);

        Alert::toast('Kurikulum berhasil ditambahkan.', 'success')
        ->position('bottom-end')
        ->autoClose(3000);

        return redirect()->route('admin.matakuliah.index');
    }

    public function update(Request $request, Matakuliah $matakuliah)
    {
        $rules = [
            'jurusan_id' => 'required|string',
            'nama' => 'required|string|max:255',
            'kategori_mk' => 'required|integer|in:0,1',
            'sks' => 'required|integer|min:1|max:10',
            'smt' => 'required|integer|min:1|max:8',
            'semester' => 'required|string|in:ganjil,genap',
        ];

        $messages = [
            'nama.required' => 'Nama mata kuliah tidak boleh kosong.',
            'sks.integer' => 'SKS harus berupa angka.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'jurusan_id',
            'nama',
            'kategori_mk',
            'sks',
            'smt',
            'semester',
        ]);

        $matakuliah->update($data);

        Alert::toast('Kurikulum berhasil diperbarui.', 'info')
        ->position('bottom-end')
        ->autoClose(3000);

        return redirect()->route('admin.matakuliah.index');
    }

    public function destroy(Matakuliah $matakuliah): RedirectResponse
    {
        $matakuliah->delete();
        Alert::toast('Kurikulum berhasil delete.', 'info')
        ->position('bottom-end')
        ->autoClose(3000);
        return redirect()->route('admin.matakuliah.index');
    }
}