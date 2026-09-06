<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class MahasiswaImpersonationController extends Controller
{
    public function store(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $admin = Auth::guard('web')->user();

        abort_unless($admin, 403);

        if ($request->session()->get('impersonating_dosen')) {
            $dosen = Auth::guard('dosen')->user();

            if ($dosen) {
                activity_log_for(
                    $admin,
                    'admin',
                    'switch_impersonation',
                    'Admin berpindah dari akun dosen '.$dosen->nama.' ke akun mahasiswa '.$mahasiswa->nama
                );
                activity_log_for(
                    $dosen,
                    'dosen',
                    'impersonated_logout',
                    'Sesi impersonasi dosen dihentikan karena admin berpindah ke akun mahasiswa.'
                );
            }

            Auth::guard('dosen')->logout();
            $request->session()->forget([
                'impersonating_dosen',
                'impersonated_dosen_id',
            ]);
        }

        if (Auth::guard('mahasiswa')->check()) {
            Auth::guard('mahasiswa')->logout();
        }

        Auth::guard('mahasiswa')->login($mahasiswa);

        $request->session()->put([
            'impersonating_mahasiswa' => true,
            'impersonator_admin_id' => $admin->id,
            'impersonated_mahasiswa_id' => $mahasiswa->mahasiswa_id,
        ]);

        activity_log_for(
            $admin,
            'admin',
            'impersonate_mahasiswa',
            'Admin login sebagai mahasiswa: '.$mahasiswa->nama.' (NIM: '.$mahasiswa->nim.')'
        );

        activity_log_for(
            $mahasiswa,
            'mahasiswa',
            'impersonated_login',
            'Akun mahasiswa diakses oleh admin: '.$admin->name.' ('.$admin->email.')'
        );

        Alert::toast('Anda sedang login sebagai mahasiswa '.$mahasiswa->nama.'.', 'success')
            ->position('center')
            ->autoClose(3000);

        return redirect()->route('mahasiswa.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $admin = Auth::guard('web')->user();
        $mahasiswa = Auth::guard('mahasiswa')->user();

        if ($admin && $mahasiswa) {
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

        Alert::toast('Anda telah kembali ke sesi admin.', 'success')
            ->position('center')
            ->autoClose(3000);

        return redirect()->route('admin.mahasiswa.index');
    }
}
