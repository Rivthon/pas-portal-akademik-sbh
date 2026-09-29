<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        $login = trim($credentials['login']);
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nim';

        $mahasiswa = Mahasiswa::query()
            ->where($field, $login)
            ->first();

        if (! $mahasiswa || ! Hash::check($credentials['password'], $mahasiswa->password)) {
            return response()->json([
                'message' => 'NIM/email atau password salah.',
            ], 401);
        }

        $deviceName = 'android:'.trim($credentials['device_name']);

        // Satu token aktif per perangkat agar token lama tidak terus menumpuk.
        $mahasiswa->tokens()->where('name', $deviceName)->delete();

        $token = $mahasiswa->createToken($deviceName, ['mahasiswa'])->plainTextToken;

        activity_log_for(
            $mahasiswa,
            'mahasiswa',
            'login_mobile',
            'Mahasiswa login melalui aplikasi Android: '.$mahasiswa->nama.' ('.$mahasiswa->nim.')'
        );

        return response()->json([
            'message' => 'Login berhasil.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'mahasiswa' => $this->profile($mahasiswa),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();

        return response()->json([
            'mahasiswa' => $this->profile($mahasiswa),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();

        activity_log_for(
            $mahasiswa,
            'mahasiswa',
            'logout_mobile',
            'Mahasiswa logout dari aplikasi Android: '.$mahasiswa->nama.' ('.$mahasiswa->nim.')'
        );

        $mahasiswa->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    private function profile(Mahasiswa $mahasiswa): array
    {
        $mahasiswa->loadMissing('programStudi', 'dosen');

        return [
            'id' => (int) $mahasiswa->mahasiswa_id,
            'nim' => $mahasiswa->nim,
            'nama' => $mahasiswa->nama,
            'email' => $mahasiswa->email,
            'semester' => (int) $mahasiswa->semester,
            'kelas' => $mahasiswa->label_kelas,
            'status' => $mahasiswa->status_mhs,
            'program_studi' => $mahasiswa->programStudi?->nama,
            'dosen_pembimbing' => $mahasiswa->dosen?->nama,
            'avatar_url' => $mahasiswa->avatar_url,
        ];
    }
}
