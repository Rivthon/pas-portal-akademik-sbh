<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use App\Models\Jadwaluap;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;

class JadwaluapController extends Controller
{

      public function index()
        {
            try {
                // Ambil tahun ajaran yang statusnya aktif
                $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();

                if (!$tahunAjaran) {
                                    return redirect()->back()->with('error', 'Tidak ada tahun ajaran yang aktif.');
                                }

                                // Ambil semua program studi
                                $programStudi = ProgramStudi::all();

                                if ($programStudi->isEmpty()) {
                                    return redirect()->back()->with('error', 'Data program studi tidak tersedia.');
                                }

                                // Ambil semua jadwal UAP (tanpa relasi matakuliah dan ruangan)
                                $jadwalUap = Jadwaluap::where('ta_id', $tahunAjaran->ta_id)->get();

                                return view('jadwal-uap.index', compact('programStudi', 'tahunAjaran', 'jadwalUap'));
                            } catch (\Exception $e) {
                                return redirect()->back()->with('error', 'Terjadi kesalahan pada server.');
                            }

        }


        public function filter(Request $request)
        {
            try {
                $programStudi = $request->query('programStudi');
                $semester = $request->query('semester');

                if (!$programStudi || !$semester) {
                    return response()->json(['message' => 'Program studi dan semester diperlukan.'], 400);
                }

                // Ambil tahun ajaran yang statusnya aktif
                $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();

                if (!$tahunAjaran) {
                    return response()->json(['message' => 'Tidak ada tahun ajaran yang aktif.'], 404);
                }

                // Ambil data jadwal uap dengan filter jurusan_id dan semester dari matakuliah
                $jadwal = Jadwaluap::select(
                    'jadwal_uap.id',
                        'jadwal_uap.ta_id',
                        'jadwal_uap.jurusan_id',
                        'matakuliah.nama as nama_matakuliah',
                        'matakuliah.smt as semester',
                        'jadwal_uap.jam',
                        'jadwal_uap.tanggal',
                        'ruangan.nama as nama_ruangan',
                        'jadwal_uap.jenis_kelas'
                    )
                    ->join('matakuliah', function ($join) use ($semester) {
                        $join->on('jadwal_uap.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                            ->where('matakuliah.smt', '=', $semester);
                    })
                    ->leftJoin('ruangan', 'jadwal_uap.ruangan_id', '=', 'ruangan.ruangan_id')
                    ->where('jadwal_uap.jurusan_id', $programStudi)
                    ->where('jadwal_uap.ta_id', $tahunAjaran->ta_id) // Sesuaikan dengan tahun ajaran aktif
                    ->orderBy('jadwal_uap.tanggal', 'asc')
                    ->orderBy('jadwal_uap.jam', 'asc')
                    ->get();

                if ($jadwal->isEmpty()) {
                    return response()->json(['message' => 'Tidak ada jadwal uap yang ditemukan untuk program studi dan semester ini.'], 404);
                }

                return response()->json($jadwal);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Terjadi kesalahan pada server.', 'error' => $e->getMessage()], 500);
            }
        }

        public function create()
        {
            // Ambil Tahun Akademik dengan status_ta = 1
            $tahunAjaranAktif = TahunAkademik::where('status_ta', 1)->first();
            return view('jadwal-uap.form', compact('tahunAjaranAktif'));
        }

        public function store(Request $request)
        {
            $rules = [
            'nama'        => 'required',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'tanggal'     => 'required',
            ];

            $messages = [
            'nama.required'        => 'Nama harus diisi.',
            'jam_mulai.required'   => 'Jam mulai harus diisi.',
            'jam_selesai.required' => 'Jam selesai harus diisi.',
            'tanggal.required'     => 'Tanggal harus diisi.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
            }

            $data = $request->only([
            'nama',
            'jam_mulai',
            'jam_selesai',
            'tanggal',
            ]);

            // Ambil tahun ajaran aktif
            $tahunAjaranAktif = TahunAkademik::where('status_ta', 1)->first();


            $data['jurusan_id'] = 15401;
            $data['ta_id'] = $tahunAjaranAktif ? $tahunAjaranAktif->ta_id : null;

            Jadwaluap::create($data);

            Alert::toast('Jadwal UTS berhasil ditambahkan.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

            return redirect()->route('admin.jadwal-uap.index');
        }
       public function edit($id)
        {
            // Ambil Tahun Akademik dengan status_ta = 1
            $tahunAjaranAktif = TahunAkademik::where('status_ta', 1)->first();
            $jadwalUap = Jadwaluap::findOrFail($id); // Pastikan data ditemukan
            $matakuliah = Matakuliah::all();
            $ruangan = Ruangan::all();

            return view('jadwal-uap.form', compact('jadwalUap', 'matakuliah', 'ruangan','tahunAjaranAktif'));
        }


        public function update(Request $request, Jadwaluap $jadwalUap)
        {
            $rules = [
            'matakuliah_id' => 'required|exists:matakuliah,matakuliah_id', // matakuliah_id harus ada di tabel 'matakuliahs'
            'jam'           => 'required', // jam harus format HH:mm
            'tanggal'       => 'required', // tanggal harus berupa tanggal dan >= hari ini
            'ruangan_id'    => 'required|exists:ruangan,ruangan_id', // ruangan_id harus ada di tabel 'ruangans'
            'jenis_kelas'   => 'required|in:Reguler,Karyawan', // jenis_kelas harus enum
        ];


        $messages = [
            'ta_id.required'         => 'Tahun ajaran harus dipilih.',
            'ta_id.exists'           => 'Tahun ajaran yang dipilih tidak valid.',
            'matakuliah_id.required' => 'Mata kuliah harus dipilih.',
            'matakuliah_id.exists'   => 'Mata kuliah yang dipilih tidak valid.',
            'jam.required'           => 'Jam UTS harus diisi.',
            'tanggal.required'       => 'Tanggal UTS harus diisi.',
            'tanggal.date'           => 'Tanggal harus berupa format tanggal yang valid.',
            'tanggal.after_or_equal' => 'Tanggal UTS tidak boleh sebelum hari ini.',
            'ruangan_id.required'    => 'Ruangan harus dipilih.',
            'ruangan_id.exists'      => 'Ruangan yang dipilih tidak valid.',
            'jenis_kelas.required'   => 'Jenis kelas harus dipilih.',
            'jenis_kelas.in'         => 'Jenis kelas harus Reguler atau Karyawan.',
        ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Ambil jurusan_id dari matakuliah_id yang dipilih
            $matakuliah = Matakuliah::find($request->matakuliah_id);
            $jurusan_id = $matakuliah ? $matakuliah->jurusan_id : null;

            $data = $request->only([
                'ta_id',
                'matakuliah_id',
                'tanggal',
                'jam',
                'ruangan_id',
                'jenis_kelas',
            ]);

            // Tambahkan jurusan_id ke data
            $data['jurusan_id'] = $jurusan_id;

          $jadwalUap->fill($data);
            $jadwalUap->save();

            Alert::toast('Jadwal UTS berhasil diperbarui.', 'info')
                ->position('bottom-end')
                ->autoClose(3000);

            return redirect()->route('admin.jadwal-uap.index');
        }

        public function destroy($id)
        {
            try {
                $jadwal = Jadwaluap::find($id);

                if ($jadwal) {
                    $jadwal->delete();
                    Alert::toast('Data Jadwal berhasil dihapus.', 'info')
                        ->position('bottom-end')
                        ->autoClose(3000);

                    return redirect()->back(); // Sesuaikan dengan kebutuhan
                } else {
                    Alert::toast('Data tidak ditemukan.', 'error')
                        ->position('bottom-end')
                        ->autoClose(3000);

                    return redirect()->back();
                }
            } catch (\Exception $e) {
                Alert::toast('Gagal menghapus data: ' . $e->getMessage(), 'error')
                    ->position('bottom-end')
                    ->autoClose(3000);
                return redirect()->back();
            }
    }
}
