<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Models\Setting;
use App\Models\Permintaan;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use RealRashid\SweetAlert\Facades\Alert;

class PerminataanController extends Controller
{
     public function index()
    {
        $semester = Auth::guard('mahasiswa')->user()->semester;
        $prodi = Auth::guard('mahasiswa')->user()->jurusan_id;
        $settings = Setting::first();
        // Ambil data mahasiswa yang sedang login
        $mahasiswa = Auth::guard('mahasiswa')->user();
         $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });

        $permintaanTerakhir = Permintaan::where('mahasiswa_id', Auth::id('mahasiswa'))->latest()->first();
        $permintaan = Permintaan::where('mahasiswa_id', Auth::id('mahasiswa'))->latest()->get();

        return view('students.permintaan.index', compact('permintaan', 'permintaanTerakhir', 'settings', 'mahasiswa', 'semester', 'prodi','ta'));
    }

    // Menampilkan form permintaan baru
    public function create()
    {
        return view('students.permintaan.form');
    }

    // Simpan permintaan baru
    public function store(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $request->validate([
            'jenis_permintaan' => 'required',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'prioritas' => 'required|in:rendah,sedang,tinggi,urgen',
            'file_lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,zip|max:2048',
        ]);

        $fileName = null;
        if ($request->hasFile('file_lampiran')) {
            $fileName = $request->file('file_lampiran')->store('lampiran_permintaan', 'public');
        }

        $permintaan = Permintaan::create([
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'jenis_permintaan' => $request->jenis_permintaan,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'prioritas' => $request->prioritas,
            'file_lampiran' => $fileName,
            // 'menunggu','disetujui','ditolak','revisi','selesai'
            'status' => 'menunggu',
        ]);
        // Kirim pesan WA setelah berhasil disimpan
        try {
            $this->kirimWhatsAppNotif($mahasiswa, $permintaan);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan berhasil dikirim dan notifikasi WA terkirim.',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal kirim WA notifikasi: ', [
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'jenis_permintaan' => $request->jenis_permintaan,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'prioritas' => $request->prioritas,
                'file_lampiran' => $fileName,
                'status' => 'menunggu',
                'created_at' => now(),
                'updated_at' => now(),
                'permintaan_id' => $permintaan->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('mahasiswa.permintaan.index')->with([
                'success' => 'Permintaan berhasil dikirim.',
                'warning' => 'Namun, pengiriman notifikasi WhatsApp gagal: ' . $e->getMessage(),
            ]);
        }

        Alert::success('Permintaan Dikirim', 'Permintaan Anda telah berhasil dikirim.');
        return redirect()->route('mahasiswa.permintaan.index');
    }

    private function kirimWhatsAppNotif($mahasiswa, $permintaan)
    {
        $nama = $mahasiswa->nama;
        $jurusan = $mahasiswa->programStudi->nama ?? '-';
        $judul = $permintaan->judul;
        $jenis = ucfirst($permintaan->jenis_permintaan);
        $prioritas = ucfirst($permintaan->prioritas);

        $pesan = "Halo {$nama},\n\n" .
                "Terima kasih telah mengirim permintaan bantuan/saran melalui sistem kami.\n\n" .
                "📌 *Judul:* {$judul}\n" .
                "📂 *Jenis:* {$jenis}\n" .
                "⚠️ *Prioritas:* {$prioritas}\n\n" .
                "Permintaan kamu sedang kami proses. Kami akan segera menindaklanjutinya. 🙏\n\n" .
                "- ICT STIKes Bogor Husada" ;
        // Kirim pesan WA menggunakan API Watzap
        // Pastikan Anda sudah mengatur API_KEY dan NUMBER_KEY di file .env

        $dataSending = [
            "api_key" => env('WATZAP_API_KEY'),
            "number_key" => env('WATZAP_NUMBER_KEY'),
            "phone_no" => $mahasiswa->no_telp,
            "message" => $pesan,
            "wait_until_send" => "1",
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.watzap.id/v1/send_message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($dataSending),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            throw new \Exception("CURL Error: " . $error);
        }

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON Response: " . $response);
        }

        $status = $result['status'] ?? 'error';
        $message = $result['message'] ?? 'Unknown Error';
        $detail = $result['data']['error'] ?? '-';

        if ($status !== 'success' && !str_contains(strtolower($message), 'successfully')) {
            throw new \Exception("WA Error: {$message} | Detail: {$detail}");
        }

        // Log response WA
        Log::info('WA Response:', $result);

        return $result;
    }

    // Detail permintaan (untuk admin/mahasiswa)
    public function show(Permintaan $permintaan)
    {
        return view('permintaan.show', compact('permintaan'));
    }

    // Halaman edit status permintaan (untuk admin)
    public function edit(Permintaan $permintaan)
    {
        return view('permintaan.edit', compact('permintaan'));
    }

    // Update status oleh admin
    public function update(Request $request, Permintaan $permintaan)
    {
        $request->validate([
            'status' => 'required|in:pending,diproses,selesai,ditolak',
            'komentar_admin' => 'nullable|string|max:255',
        ]);

        $permintaan->update($request->only(['status', 'komentar_admin']));

        Alert::success('Status Diperbarui', 'Permintaan berhasil diperbarui.');
        return redirect()->route('admin.permintaan.index');
    }

    // Menghapus permintaan
    public function destroy($id)
    {
        $p = Permintaan::findOrFail($id); // Cari data berdasarkan ID
        $p->delete(); // Hapus data
        Alert::success('Berhasil', 'Permintaan berhasil dihapus');
        return redirect()->back();
    }
}