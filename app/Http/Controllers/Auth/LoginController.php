<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

        try {
            $settings = Cache::remember('app_settings', 3600, function () {
                return DB::table('settings')->first();
            });
        } catch (QueryException $e) {
            report($e); // log error-nya

            // Fallback jika error, bisa tampilkan pesan default atau halaman error custom
            return response()->view('errors.mysql', [
                'message' => 'Database sedang mengalami gangguan. Silakan coba beberapa saat lagi.'
            ], 500);
        }

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