<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginAttemptService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
                'message' => 'Database sedang mengalami gangguan. Silakan coba beberapa saat lagi.',
            ], 500);
        }

        return view('auth.login', [
            'settings' => $settings,
        ]);
    }

    // Proses login
    public function login(Request $request, LoginAttemptService $loginAttempts)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Autentikasi dengan guard web
        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $loginAttempts->clear($request, 'web');
            $request->session()->regenerate();
            // Log aktivitas login admin
            activity_log('login', 'Admin berhasil login: '.$request->email);

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

        return $loginAttempts->failureResponse($request, 'web', 'Email atau password salah.');
    }

    public function logout(Request $request)
    {
        // Log aktivitas logout sebelum session dihapus
        $user = Auth::user();
        if ($user) {
            activity_log_for($user, 'admin', 'logout', 'Admin berhasil logout');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Alert::toast('Anda telah berhasil logout.', 'success')
            ->position('center')
            ->autoClose(3000);

        return redirect()->route('admin.login');
    }
}
