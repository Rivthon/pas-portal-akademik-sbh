<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Models\Gelombang;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class ProfileUserController extends Controller
{
    public function index()
        {
            $mahasiswa = Auth::guard('mahasiswa')->user(); // ambil user dari guard mahasiswa
            $gelombang = Gelombang::all();
            $programStudi = ProgramStudi::all();

            return view('mahasiswa.profile.edit', compact('mahasiswa', 'gelombang', 'programStudi'));
        }

        public function update(Request $request)
        {
            $user = Auth::guard('mahasiswa')->user(); // gunakan guard mahasiswa

            $request->validate([
                'nama' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('mahasiswa', 'email')->ignore($user->mahasiswa_id, 'mahasiswa_id'),
                ],
                'password' => 'nullable|string|min:8|confirmed',
                'avatar' => 'nullable|image|mimes:png,jpg,jpeg|max:512',
                'nisn' => 'nullable|string|max:20',
                'nik' => 'nullable|string|max:20',
                'jenis_kelamin' => 'required|in:Laki-Laki,Perempuan',
                'tanggal_lahir' => 'required|date',
                'alamat' => 'required|string',
                'jurusan_id' => 'required|integer',
                'nim' => 'required|string|max:20',
                'semester' => 'required|integer|min:1|max:8',
                'tahun_masuk' => 'required|integer|min:2000|max:' . date('Y'),
                'status_mhs' => 'required|in:aktif,nonaktif,lulus',
                'kelas' => 'required|in:karyawan,pagi',
                // 'gelombang_id' => 'required|integer',
                'nama_ayah' => 'required|string|max:255',
                'nama_ibu' => 'required|string|max:255',
                'no_telp_ortu' => 'required|string|max:15',
                'pendapatan_ortu' => 'required|string|max:255',
                'alamat_ortu' => 'required|string',
            ]);

            try {
                // update field utama
                $user->nama = $request->nama;
                $user->email = $request->email;

                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }

                // upload avatar
                if ($request->hasFile('avatar')) {
                    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                        Storage::disk('public')->delete($user->avatar);
                    }
                    $user->avatar = $request->file('avatar')->store('avatars', 'public');
                }

                // update field biodata & akademik
                $user->nisn = $request->nisn;
                $user->nik = $request->nik;
                $user->jenis_kelamin = $request->jenis_kelamin;
                $user->tanggal_lahir = $request->tanggal_lahir;
                $user->alamat = $request->alamat;
                $user->jurusan_id = $request->jurusan_id;
                $user->nim = $request->nim;
                $user->semester = $request->semester;
                $user->tahun_masuk = $request->tahun_masuk;
                $user->status_mhs = $request->status_mhs;
                $user->kelas = $request->kelas;
                // $user->gelombang_id = $request->gelombang_id;
                $user->nama_ayah = $request->nama_ayah;
                $user->nama_ibu = $request->nama_ibu;
                $user->no_telp_ortu = $request->no_telp_ortu;
                $user->pendapatan_ortu = $request->pendapatan_ortu;
                $user->alamat_ortu = $request->alamat_ortu;

                $user->save();

                Alert::toast('Profile berhasil diperbaharui.', 'success')
                    ->position('bottom-end')
                    ->autoClose(3000);

                return redirect()->route('mahasiswa.profile.index');
            } catch (\Exception $e) {
                \Log::error('Failed to update profile', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);

                Alert::toast('Terjadi kesalahan saat memperbaharui profil. Silakan coba lagi.', 'error')
                    ->position('bottom-end')
                    ->autoClose(5000);

                return redirect()->back()->withInput();
            }
        }
    public function smtUpdate(Request $request)
    {
        // Validasi input
        $request->validate([
            'semester' => 'required|string',
        ]);

        try {
            // Update semester mahasiswa di database
            $mahasiswa = Auth::user();
            $mahasiswa->semester = $request->semester;
            $mahasiswa->save();

            // Berikan notifikasi sukses
            Alert::toast('Semester berhasil diperbarui!', 'success')
                ->position('center')
                ->autoClose(5000);

            return redirect()->route('mahasiswa.dashboard');
        } catch (\Exception $e) {
            // Jika terjadi error, tampilkan notifikasi error
            Alert::toast('Gagal memperbarui semester: ' . $e->getMessage(), 'error')
                ->position('center')
                ->autoClose(5000);
            return redirect()->back()->withInput();
        }
    }

}
