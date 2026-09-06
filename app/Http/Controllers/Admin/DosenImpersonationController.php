<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class DosenImpersonationController extends Controller
{
    public function store(Request $request, Dosen $dosen): RedirectResponse
    {
        $admin = Auth::guard('web')->user();

        abort_unless($admin, 403);
        abort_if(
            $request->session()->get('impersonating_mahasiswa'),
            409,
            'Hentikan mode login sebagai mahasiswa terlebih dahulu.'
        );

        if (Auth::guard('dosen')->check()) {
            Auth::guard('dosen')->logout();
        }

        Auth::guard('dosen')->login($dosen);

        $request->session()->put([
            'impersonating_dosen' => true,
            'impersonator_admin_id' => $admin->id,
            'impersonated_dosen_id' => $dosen->dosen_id,
        ]);

        activity_log_for(
            $admin,
            'admin',
            'impersonate_dosen',
            'Admin login sebagai dosen: '.$dosen->nama.' (NIDN/Kode: '.($dosen->nidn ?: $dosen->kd_dosen ?: '-').')'
        );

        activity_log_for(
            $dosen,
            'dosen',
            'impersonated_login',
            'Akun dosen diakses oleh admin: '.$admin->name.' ('.$admin->email.')'
        );

        Alert::toast('Anda sedang login sebagai dosen '.$dosen->nama.'.', 'success')
            ->position('center')
            ->autoClose(3000);

        return redirect()->route('dosen.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $admin = Auth::guard('web')->user();
        $dosen = Auth::guard('dosen')->user();

        if ($admin && $dosen) {
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

        Alert::toast('Anda telah kembali ke sesi admin.', 'success')
            ->position('center')
            ->autoClose(3000);

        return redirect()->route('admin.dosen.index');
    }
}
