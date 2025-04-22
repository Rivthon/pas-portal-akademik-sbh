<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Dosen;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
;

class LoginDosenController extends Controller
{
    public function showLoginForm()
    {
        $settings = Setting::first();
        if (Auth::guard('dosen')->check()) {
            return redirect()->route('dosen.dashboard');
        }
        return view('auth.dosen-login', compact('settings'));
    }

    public function login(Request $request)
{
    // Validasi input: email atau NIDN dan password wajib diisi
    $request->validate([
        'email' => 'required|string',
        'password' => 'required|string|min:6',
    ]);

    // Tentukan apakah input adalah email atau NIDN
    $fieldType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'nidn';

    // Cek apakah user ada di database
    $dosen = Dosen::where($fieldType, $request->email)->first();

    if (!$dosen) {
        return back()->withErrors(['email' => 'Akun tidak ditemukan.']);
    }

    // Debugging: Log informasi dosen ditemukan
    \Log::info('Dosen ditemukan', ['email/nidn' => $request->email]);

// Coba login dengan kredensial yang diberikan
if (Auth::guard('dosen')->attempt([$fieldType => $request->email, 'password' => $request->password])) {
    // Debugging: Log jika login berhasil
    \Log::info('Login dosen berhasil', ['email/nidn' => $request->email]);

    // Redirect ke dashboard dosen dengan alert toast berhasil login
    Alert::success('Login Berhasil', 'Selamat datang ' . $dosen->nama)->showConfirmButton('OK', '#3085d6');
    return redirect()->route('dosen.dashboard');
}

    // Debugging: Log jika password salah
    \Log::warning('Gagal login dosen, password salah', [
        'fieldType' => $fieldType,
        'emailOrNIDN' => $request->email,
    ]);

    // Jika gagal, tampilkan notifikasi error
    return back()->withErrors(['password' => 'Password salah!']);
}


    private function getFieldType(string $input): string
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'nidn';
    }

    public function logout(Request $request)
    {
        Auth::guard('dosen')->logout();

        // Hapus sesi dan regenerasi token keamanan
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect ke halaman login dosen (pastikan route ini ada)
        Alert::Success('Logout Berhasil', 'Anda telah berhasil logout');
        return redirect()->route('dosen.login');
    }

}