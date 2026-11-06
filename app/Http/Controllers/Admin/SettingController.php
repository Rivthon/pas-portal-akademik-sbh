<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnforceWebsiteAvailability;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Menampilkan form edit pengaturan.
     */
    public function edit()
    {
        activity_log('lihat_setting', 'Admin mengakses halaman pengaturan');
        $setting = Setting::first(); // Ambil data pengaturan pertama

        return view('admin.settings.edit', compact('setting'));
    }

    /**
     * Menyimpan perubahan pengaturan.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png|max:2048',
            'footer_name' => 'required|string|max:255',
            'copyright' => 'required|string|max:500',
        ]);

        $setting = Setting::first();
        $data = $request->only(['name', 'footer_name', 'copyright']);

        // Proses upload logo
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $logo;
        }

        // Proses upload favicon
        if ($request->hasFile('favicon')) {
            $favicon = $request->file('favicon')->store('favicons', 'public');
            $data['favicon'] = $favicon;
        }

        $setting->update($data);

        activity_log('update_setting', 'Admin memperbarui pengaturan aplikasi');

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui!');
    }

    public function toggleWebsiteAvailability(Request $request)
    {
        $validated = $request->validate([
            'website_down' => ['required', 'boolean'],
        ]);

        $setting = Setting::query()->firstOrCreate([], [
            'name' => config('app.name', 'PAS'),
        ]);
        $setting->update(['website_down' => (bool) $validated['website_down']]);
        EnforceWebsiteAvailability::clearCache();

        $isDown = (bool) $validated['website_down'];
        activity_log(
            $isDown ? 'aktifkan_website_down' : 'nonaktifkan_website_down',
            $isDown
                ? 'Admin menonaktifkan akses publik PAS.'
                : 'Admin mengaktifkan kembali akses publik PAS.'
        );

        return redirect()->route('admin.settings.edit')->with(
            'success',
            $isDown
                ? 'Website Down aktif. Hanya Admin yang dapat mengakses PAS.'
                : 'Website sudah aktif kembali dan dapat diakses seluruh pengguna.'
        );
    }
}
