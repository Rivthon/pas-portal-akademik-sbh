<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
class ProfileDosenController extends Controller
{
    public function index()
    {
        return view('pages-dosen.profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Validate the input
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:png,jpg,jpeg|max:512', // Max size: 512 KB
        ]);

        try {
            // Update user details
            $user->nama = $request->input('nama');
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

            // Flash a success message
            Alert::toast('Profile berhasil diperbaharui.', 'success')
                ->position('bottom-end')
                ->autoClose(3000);

            return redirect()->route('dosen.profile.index');
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Failed to update profile: ' . $e->getMessage(), ['user_id' => $user->id]);

            // Flash an error message to the user
            Alert::toast('Terjadi kesalahan saat memperbaharui profil. Silakan coba lagi. Error: ' . $e->getMessage(), 'error')
                ->position('bottom-end')
                ->autoClose(5000);

            return redirect()->route('dosen.profile.index')->withInput();
        }
    }
}