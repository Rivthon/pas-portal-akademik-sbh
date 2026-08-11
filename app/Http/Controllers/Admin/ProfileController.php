<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class ProfileController extends Controller
{
    public function index()
    {
        activity_log('lihat_profil', 'Admin mengakses halaman profil');

        return view('admin.profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        // Validate the input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:png,jpg,jpeg|max:512', // Max size: 512 KB
        ]);

        try {
            // Update user details
            $user->name = $request->input('name');
            $user->email = $request->input('email');

            if ($request->filled('password')) {
                $user->password = Hash::make($request->input('password'));
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if it exists and is stored in the filesystem
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Store the new avatar
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            $user->save();

            activity_log('update_profil', 'Admin memperbarui profil: '.$user->name);

            // Flash a success message
            Alert::toast('Profile berhasil diperbaharui.', 'success')
                ->position('bottom-end')
                ->autoClose(3000);

            return redirect()->route('admin.profile.index');
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Failed to update profile: '.$e->getMessage(), ['user_id' => $user->id]);

            // Flash an error message to the user
            Alert::toast('Terjadi kesalahan saat memperbaharui profil. Silakan coba lagi. Error: '.$e->getMessage(), 'error')
                ->position('bottom-end')
                ->autoClose(5000);

            return redirect()->route('admin.profile.index')->withInput();
        }
    }
}
