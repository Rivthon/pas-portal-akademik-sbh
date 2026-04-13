<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Contracts\Encryption\DecryptException;

class VerificationController extends Controller
{
    /**
     * Verifikasi keaslian dokumen Kartu Ujian fisik berdasarkan scan QR Code
     */
    public function verifyUjian($token)
    {
        try {
            // Dekripsi token string dan decode JSON
            $decryptedString = Crypt::decryptString(base64_decode($token));
            $payload = json_decode($decryptedString, true);

            // Validasi format Payload
            if (!isset($payload['m']) || !isset($payload['t']) || !isset($payload['type'])) {
                return view('public.verify-ujian', [
                    'status' => 'invalid',
                    'message' => 'Struktur Token Verifikasi Tidak Dikenali.'
                ]);
            }

            $mahasiswa = Mahasiswa::with('programStudi')->find($payload['m']);
            $ta = TahunAkademik::find($payload['t']);
            $type = $payload['type']; // UTS atau UAS

            if (!$mahasiswa || !$ta) {
                return view('public.verify-ujian', [
                    'status' => 'invalid',
                    'message' => 'Data Mahasiswa atau Tahun Akademik tidak valid.'
                ]);
            }

            return view('public.verify-ujian', [
                'status' => 'valid',
                'mahasiswa' => $mahasiswa,
                'ta' => $ta,
                'type' => $type
            ]);

        } catch (DecryptException $e) {
            // Token corrupt / dibuat asal-asalan
            return view('public.verify-ujian', [
                'status' => 'invalid',
                'message' => 'Invalid Verification Token. Kartu Ujian ini mungkin Palsu atau Cacat Kriptografi.'
            ]);
        }
    }
}
