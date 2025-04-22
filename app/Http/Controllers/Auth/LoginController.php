<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class LoginController extends Controller
{
    // Menampilkan form login

    public function showLoginForm()
    {
        if (Auth::guard('mahasiswa')->check()) {
            return redirect()->route('mahasiswa.dashboard');
        }
        if (Auth::guard('web')->check()) {
            return redirect()->intended(route('admin.home'));
        }

        // Retrieve the first setting record for the login page
        $settings = Setting::first();

        // Return the login view with the retrieved settings
        return view('auth.login', [
            'settings' => $settings
        ]);
    }

    // Proses login
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Cek apakah email ada di database
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Jika email tidak ditemukan, tampilkan alert
            Alert::toast('Email tidak terdaftar.', 'warning')
                ->position('top-right')
                ->autoClose(3000);
            return back()->withErrors([
                'email' => 'Email tidak terdaftar.',
            ])->withInput($request->except('password'));
        }

        // Autentikasi dengan guard web
        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            // Jika berhasil, tampilkan alert dan arahkan ke halaman home admin
            Alert::toast('Anda telah berhasil login.', 'success')
                ->position('center')
                ->autoClose(3000);
            return redirect()->intended(route('admin.home'));
        }

        // Jika gagal login karena password salah, tampilkan alert
        Alert::toast('Email atau password salah.', 'error')
            ->position('top-right')
            ->autoClose(3000);
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->except('password'));
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Alert::toast('Anda telah berhasil logout.', 'success')
            ->position('center')
            ->autoClose(3000);
        return redirect()->route('admin.login');
    }

    }