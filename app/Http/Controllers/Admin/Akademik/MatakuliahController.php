<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
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
        $kategori = $request->input('kategori_mk');

        // Start building the query
        $query = Matakuliah::with('programStudi');

        // Apply the search filter if provided
        if ($search) {
            $query->where(function ($filter) use ($search) {
                $filter->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('matakuliah_id', 'like', '%'.$search.'%')
                    ->orWhereHas('programStudi', function ($q) use ($search) {
                        $q->where('nama', 'like', '%'.$search.'%');
                    });
            });
        }

        if (in_array((string) $kategori, ['0', '1'], true)) {
            $query->where('kategori_mk', (int) $kategori);
        }

        // Paginate the results after filtering
        $matakuliah = $query
            ->orderBy('nama')
            ->paginate(10)
            ->appends($request->query());

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.akademik.matakuliah.partial_list', compact('matakuliah'))->render(),
            ]);
        }

        $pageIndex = ($matakuliah->currentPage() - 1) * $matakuliah->perPage();

        return view('admin.akademik.matakuliah.index', compact('matakuliah', 'pageIndex', 'search', 'kategori'));
    }

    public function create(): View
    {
        $programStudi = ProgramStudi::all();

        return view('admin.akademik.matakuliah.form', compact('programStudi'));
    }

    public function edit(Matakuliah $matakuliah): View
    {
        $programStudi = ProgramStudi::all();

        return view('admin.akademik.matakuliah.form', compact('matakuliah', 'programStudi'));
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
        ];

        $validator = Validator::make($request->all(), $rules);

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
        ]);

        // otomatis isi semester berdasarkan smt
        $data['semester'] = $data['smt'] % 2 == 1 ? 'Ganjil' : 'Genap';

        Matakuliah::create($data);

        activity_log('tambah_matakuliah', 'Admin menambah matakuliah: '.$request->nama.' (Kode: '.$request->matakuliah_id.')');

        Alert::toast('Matakuliah berhasil ditambahkan.', 'success')
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
        $data['semester'] = $data['smt'] % 2 == 1 ? 'Ganjil' : 'Genap';

        $matakuliah->update($data);

        activity_log('update_matakuliah', 'Admin memperbarui matakuliah: '.$matakuliah->nama);

        Alert::toast('Matakuliah berhasil diperbarui.', 'info')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.matakuliah.index');
    }

    public function destroy(Matakuliah $matakuliah): RedirectResponse
    {
        activity_log('hapus_matakuliah', 'Admin menghapus matakuliah: '.$matakuliah->nama);
        $matakuliah->delete();
        Alert::toast('Matakuliah berhasil dihapus.', 'info')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.matakuliah.index');
    }
}
