<?php

namespace App\Http\Controllers\Mahasiswa;

use Carbon\Carbon;
use App\Models\Krs;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use App\Models\CalenderAkademik;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{

        public function index()
    {
        $mahasiswa = auth('mahasiswa')->user();
        $tanggalSekarang = Carbon::now()->translatedFormat('l, d F Y');

        // **1. Gunakan Cache untuk Data yang Jarang Berubah**
        $settings = Cache::remember('settings', 3600, function () {
            return Setting::first();
        });

        $ta = Cache::remember('tahun_akademik_aktif', 3600, function () {
            return TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        });

        // **2. Cache Kalender Akademik Berdasarkan Jurusan**
        $kalenderAkademik = Cache::remember("kalender_akademik_{$mahasiswa->jurusan_id}", 3600, function () use ($mahasiswa) {
            return CalenderAkademik::where('jurusan_id', $mahasiswa->jurusan_id)
                // ->orderBy('tanggal_mulai', 'desc')
                // ->take(5)
                ->get();
        });

        // **3. Hitung Total SKS dan IPK (Hanya Jika KHS Sudah Dinilai)**
          $khs = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereHas('kurikulum.mataKuliah') // Pastikan ada relasi ke mata kuliah
            ->get();

            // Filter hanya KHS yang memiliki nilai
            $filteredKhs = $khs->filter(fn($item) => !empty($item->khs));

            // Hitung total SKS
            $totalSks = $filteredKhs->sum(fn($item) => optional($item->kurikulum->mataKuliah)->sks ?? 0);

            // Hitung total bobot
            function calculateWeight($grade) {
                $gradeWeights = [
                    'A' => 4.00, 'AB' => 3.75, 'BA' => 3.50, 'B' => 3.00,
                    'BC' => 2.75, 'C' => 2.00, 'D' => 1.00, 'E' => 0
                ];
                return $gradeWeights[$grade] ?? 0;
            }

            $totalBobot = $filteredKhs->sum(fn($item) =>
                (optional($item->kurikulum->mataKuliah)->sks ?? 0) * calculateWeight($item->khs)
            );

            // Hitung IPK (Indeks Prestasi Kumulatif)
            $ipk = $totalSks ? $totalBobot / $totalSks : 0;


        // **4. Cache Berita dari WordPress**
        $berita = Cache::remember('berita_wp', 1800, function () {
            $response = Http::get('https://sbh.ac.id/wp-json/wp/v2/posts', [
                'per_page' => 10,
                'orderby' => 'date',
                'order' => 'desc'
            ]);

            return collect($response->json())->map(function ($post) {
                $imageUrl = asset('assets/img/no-image.jpg');
                if (isset($post['_links']['wp:featuredmedia'][0]['href'])) {
                    $mediaResponse = Http::get($post['_links']['wp:featuredmedia'][0]['href']);
                    $media = $mediaResponse->json();
                    $imageUrl = $media['source_url'] ?? $imageUrl;
                }
                return [
                    'title' => $post['title']['rendered'],
                    'date' => Carbon::parse($post['date'])->translatedFormat('d F Y'),
                    'link' => $post['link'],
                    'image' => $imageUrl,
                ];
            });
        });

        return view('students.dashboard', compact(
            'tanggalSekarang',
            'settings',
            'ta',
            'kalenderAkademik',
            'totalSks',
            'ipk',
            'berita'
        ));
    }

    // **Fungsi Konversi Nilai ke Bobot IPK**
    private function convertNilaiKeBobot($nilai)
    {
        return match (strtoupper($nilai)) {
            'A' => 4.0,
            'A-' => 3.7,
            'B+' => 3.3,
            'B' => 3.0,
            'B-' => 2.7,
            'C+' => 2.3,
            'C' => 2.0,
            'C-' => 1.7,
            'D' => 1.0,
            'E' => 0.0,
            default => 0.0,
        };
    }


}
