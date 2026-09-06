<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Setting;
use App\Services\LoginAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

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

    public function login(Request $request, LoginAttemptService $loginAttempts)
    {
        // Validasi input: email atau NIDN dan password wajib diisi
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string|min:6',
            'remember' => 'nullable|boolean',
        ]);

        // Tentukan apakah input adalah email atau NIDN
        $fieldType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'nidn';

        // Coba login dengan kredensial yang diberikan
        if (Auth::guard('dosen')->attempt(
            [$fieldType => $request->email, 'password' => $request->password],
            $request->boolean('remember')
        )) {
            $loginAttempts->clear($request, 'dosen');
            $request->session()->regenerate();
            $dosen = Auth::guard('dosen')->user();
            // Log aktivitas login dosen
            activity_log('login', 'Dosen berhasil login: '.$dosen->nama.' ('.($dosen->nidn ?? '-').')');

            // Redirect ke dashboard dosen dengan alert toast berhasil login
            Alert::success('Login Berhasil', 'Selamat datang '.$dosen->nama)->showConfirmButton('OK', '#3085d6');

            return redirect()->intended(route('dosen.dashboard'));
        }

        // Debugging: Log jika password salah
        \Log::warning('Percobaan login dosen gagal.', ['ip' => $request->ip()]);

        // Jika gagal, tampilkan notifikasi error
        return $loginAttempts->failureResponse(
            $request,
            'dosen',
            'Email/NIDN atau password salah.'
        );
    }

    private function getFieldType(string $input): string
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'nidn';
    }

    public function logout(Request $request)
    {
        // Log aktivitas logout sebelum session dihapus
        $dosen = Auth::guard('dosen')->user();

        if ($request->session()->get('impersonating_dosen') && Auth::guard('web')->check()) {
            $admin = Auth::guard('web')->user();

            if ($dosen) {
                activity_log_for(
                    $admin,
                    'admin',
                    'stop_impersonate_dosen',
                    'Admin kembali dari akun dosen: '.$dosen->nama.' (NIDN/Kode: '.($dosen->nidn ?: $dosen->kd_dosen ?: '-').')'
                );

                activity_log_for(
                    $dosen,
                    'dosen',
                    'impersonated_logout',
                    'Sesi impersonasi dosen dihentikan oleh admin: '.$admin->name.' ('.$admin->email.')'
                );
            }

            Auth::guard('dosen')->logout();
            $request->session()->forget([
                'impersonating_dosen',
                'impersonator_admin_id',
                'impersonated_dosen_id',
            ]);
            $request->session()->regenerateToken();

            Alert::Success('Kembali ke Admin', 'Anda telah kembali ke sesi admin');

            return redirect()->route('admin.dosen.index');
        }

        if ($dosen) {
            activity_log_for($dosen, 'dosen', 'logout', 'Dosen berhasil logout: '.$dosen->nama);
        }

        Auth::guard('dosen')->logout();

        // Hapus sesi dan regenerasi token keamanan
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect ke halaman login dosen (pastikan route ini ada)
        Alert::Success('Logout Berhasil', 'Anda telah berhasil logout');

        return redirect()->route('dosen.login');
    }
}
