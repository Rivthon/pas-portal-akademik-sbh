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

class MahasiswaLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('mahasiswa')->check()) {
            return redirect()->route('mahasiswa.dashboard');
        }

        try {
            $settings = Cache::remember('app_settings', 3600, function () {
                return DB::table('settings')->first();
            });
        } catch (QueryException $e) {
            report($e);

            return response()->view('errors.mysql', [
                'message' => 'Database sedang mengalami gangguan. Silakan coba beberapa saat lagi.',
            ], 500);
        }

        return view('auth.mahasiswa-login', compact('settings'));
    }

    public function login(Request $request, LoginAttemptService $loginAttempts)
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
            $loginAttempts->clear($request, 'mahasiswa');
            $request->session()->regenerate();
            // Get the authenticated user
            $mahasiswa = Auth::guard('mahasiswa')->user();

            // Log aktivitas login mahasiswa
            activity_log('login', 'Mahasiswa berhasil login: '.$mahasiswa->nama.' ('.$mahasiswa->nim.')');

            // Debugging: Log if login is successful
            \Log::info('Login mahasiswa berhasil', ['email/nim' => $request->email]);

            // Redirect to mahasiswa dashboard with a success alert
            Alert::success('Login Berhasil', 'Selamat datang '.$mahasiswa->nama)->showConfirmButton('OK', '#3085d6');

            return redirect()->route('mahasiswa.dashboard');
        }

        // Debugging: Log the failed credentials
        \Log::warning('Percobaan login mahasiswa gagal.', ['ip' => $request->ip()]);

        return $loginAttempts->failureResponse(
            $request,
            'mahasiswa',
            'NIM/email atau password salah.'
        );
    }

    private function getFieldType(string $input): string
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'nim';
    }

    public function logout(Request $request)
    {
        // Log aktivitas logout sebelum session dihapus
        $mahasiswa = Auth::guard('mahasiswa')->user();

        if ($request->session()->get('impersonating_mahasiswa') && Auth::guard('web')->check()) {
            $admin = Auth::guard('web')->user();

            if ($mahasiswa) {
                activity_log_for(
                    $admin,
                    'admin',
                    'stop_impersonate_mahasiswa',
                    'Admin kembali dari akun mahasiswa: '.$mahasiswa->nama.' (NIM: '.$mahasiswa->nim.')'
                );

                activity_log_for(
                    $mahasiswa,
                    'mahasiswa',
                    'impersonated_logout',
                    'Sesi impersonasi mahasiswa dihentikan oleh admin: '.$admin->name.' ('.$admin->email.')'
                );
            }

            Auth::guard('mahasiswa')->logout();
            $request->session()->forget([
                'impersonating_mahasiswa',
                'impersonator_admin_id',
                'impersonated_mahasiswa_id',
            ]);
            $request->session()->regenerateToken();

            Alert::Success('Kembali ke Admin', 'Anda telah kembali ke sesi admin');

            return redirect()->route('admin.mahasiswa.index');
        }

        if ($mahasiswa) {
            activity_log_for($mahasiswa, 'mahasiswa', 'logout', 'Mahasiswa berhasil logout: '.$mahasiswa->nama);
        }

        Auth::guard('mahasiswa')->logout();

        // Hapus sesi dan regenerasi token keamanan
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect ke halaman login dosen (pastikan route ini ada)
        Alert::Success('Logout Berhasil', 'Anda telah berhasil logout');

        return redirect()->route('mahasiswa.login');

    }
}
