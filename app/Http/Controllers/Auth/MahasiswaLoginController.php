<?php

namespace App\Http\Controllers\Auth;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;


class MahasiswaLoginController extends Controller
{
    public function showLoginForm()
    {
        $settings = Setting::first();
        if (Auth::guard('mahasiswa')->check()) {
            return redirect()->route('mahasiswa.dashboard');
        }
        return view('auth.mahasiswa-login', compact('settings'));
    }

public function login(Request $request)
{
    // Validate input: email or NIM and password are required
    $request->validate([
        'email' => 'required|string',
        'password' => 'required|string|min:6',
    ]);

    // Determine if the input is an email or NIM
    $fieldType = $this->getFieldType($request->email);

    // Attempt to log in based on the determined field type
    if (Auth::guard('mahasiswa')->attempt([$fieldType => $request->email, 'password' => $request->password])) {
        // Get the authenticated user
        $mahasiswa = Auth::guard('mahasiswa')->user();

        // Debugging: Log if login is successful
        \Log::info('Login mahasiswa berhasil', ['email/nim' => $request->email]);

        // Redirect to mahasiswa dashboard with a success alert
        Alert::success('Login Berhasil', 'Selamat datang ' . $mahasiswa->nama)->showConfirmButton('OK', '#3085d6');
        return redirect()->route('mahasiswa.dashboard');
    }

    // Debugging: Log the failed credentials
    \Log::error('Failed login attempt', [
        'fieldType' => $fieldType,
        'emailOrNIM' => $request->email,
    ]);

    // If login fails, display a toast notification
    session()->flash('error', 'NIM atau email, atau password salah!');

    return back()->withInput($request->only('email'));
}

    private function getFieldType(string $input): string
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'nim';
    }

     public function logout(Request $request)
    {
         Auth::guard('mahasiswa')->logout();

        // Hapus sesi dan regenerasi token keamanan
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect ke halaman login dosen (pastikan route ini ada)
        Alert::Success('Logout Berhasil', 'Anda telah berhasil logout');
        return redirect()->route('mahasiswa.login');

    }
}