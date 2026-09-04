<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\CalendarAkademik;
use App\Models\Jadwal;
use App\Models\KhsPublication;
use App\Models\Krs;
use App\Models\LmsMateri;
use App\Models\LmsQuiz;
use App\Models\LmsTugas;
use App\Models\Setting;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
            return CalendarAkademik::where('jurusan_id', $mahasiswa->jurusan_id)
                ->aktif()
                ->whereNotNull('path')
                ->get()
                ->filter(fn (CalendarAkademik $kalender) => $kalender->fileExists())
                ->values();
        });

        // **3. Hitung Total SKS dan IPK (Hanya Jika KHS Sudah Dinilai)**
        $khs = Krs::with('kurikulum.mataKuliah')
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereHas('kurikulum.mataKuliah') // Pastikan ada relasi ke mata kuliah
            ->get();
        $khs = KhsPublication::filterPublishedKrs($khs, $mahasiswa);
        $isKhsPublished = $khs->isNotEmpty();

        // Filter hanya KHS yang memiliki nilai
        $filteredKhs = $khs->filter(fn ($item) => ! empty($item->khs));

        // Hitung total SKS
        $totalSks = $filteredKhs->sum(fn ($item) => optional($item->kurikulum->mataKuliah)->sks ?? 0);

        // Hitung total bobot
        function calculateWeight($grade)
        {
            $gradeWeights = [
                'A' => 4.00, 'AB' => 3.75, 'BA' => 3.50, 'B' => 3.00,
                'BC' => 2.75, 'C' => 2.00, 'D' => 1.00, 'E' => 0,
            ];

            return $gradeWeights[$grade] ?? 0;
        }

        $totalBobot = $filteredKhs->sum(fn ($item) => (optional($item->kurikulum->mataKuliah)->sks ?? 0) * calculateWeight($item->khs)
        );

        // Hitung IPK (Indeks Prestasi Kumulatif)
        $ipk = $totalSks ? $totalBobot / $totalSks : 0;

        // **4. Cache Berita dari WordPress (Dihidden sementara untuk penggantian API)**
        $berita = collect([]);
        /*
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
        */

        $lmsAnnouncements = $this->lmsAnnouncements($mahasiswa, $ta);

        activity_log('akses_dashboard', 'Mahasiswa mengakses dashboard');

        return view('mahasiswa.dashboard', compact(
            'tanggalSekarang',
            'settings',
            'ta',
            'kalenderAkademik',
            'totalSks',
            'ipk',
            'berita',
            'lmsAnnouncements'
        ));
    }

    private function lmsAnnouncements($mahasiswa, $ta)
    {
        if (! $ta) {
            return collect();
        }

        $kurikulumIds = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->pluck('kurikulum_id');

        $jadwalIds = Jadwal::where('ta_id', $ta->ta_id)
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->when(strtolower((string) $mahasiswa->kelas) === 'karyawan', fn ($query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', ['karyawan']))
            ->when(strtolower((string) $mahasiswa->kelas) !== 'karyawan', fn ($query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', ['reguler']))
            ->pluck('id');

        if ($jadwalIds->isEmpty()) {
            return collect();
        }

        $materi = LmsMateri::whereIn('jadwal_id', $jadwalIds)
            ->where('status', 1)
            ->with('jadwal.kurikulum.mataKuliah')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($item) => [
                'type' => 'materi',
                'title' => 'Materi baru: '.$item->judul,
                'detail' => $item->tipe ? strtoupper($item->tipe) : 'Materi pembelajaran',
                'course' => $item->jadwal?->kurikulum?->mataKuliah?->nama ?? '-',
                'event_at' => $item->created_at,
                'url' => route('mahasiswa.lms.show', $item->jadwal_id),
                'icon' => 'bx-book-open',
                'color' => 'info',
            ]);

        $tugas = LmsTugas::whereIn('jadwal_id', $jadwalIds)
            ->where('aktif', true)
            ->with('jadwal.kurikulum.mataKuliah')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($item) => [
                'type' => 'tugas',
                'title' => 'Tugas baru: '.$item->judul,
                'detail' => $item->deadline
                    ? 'Deadline '.$item->deadline->translatedFormat('d M Y, H:i')
                    : 'Tanpa deadline',
                'course' => $item->jadwal?->kurikulum?->mataKuliah?->nama ?? '-',
                'event_at' => $item->created_at,
                'url' => route('mahasiswa.lms.tugas.show', $item->tugas_id),
                'icon' => 'bx-task',
                'color' => 'warning',
            ]);

        $quiz = LmsQuiz::whereIn('jadwal_id', $jadwalIds)
            ->where('aktif', true)
            ->with('jadwal.kurikulum.mataKuliah')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($item) => [
                'type' => 'quiz',
                'title' => 'Quiz baru: '.$item->judul,
                'detail' => $item->mulai_at && now()->lt($item->mulai_at)
                    ? 'Mulai '.$item->mulai_at->translatedFormat('d M Y, H:i')
                    : 'Sudah dapat dibuka',
                'course' => $item->jadwal?->kurikulum?->mataKuliah?->nama ?? '-',
                'event_at' => $item->created_at,
                'url' => route('mahasiswa.lms.quiz.index', $item->jadwal_id),
                'icon' => 'bx-question-mark',
                'color' => 'primary',
            ]);

        return $materi
            ->concat($tugas)
            ->concat($quiz)
            ->sortByDesc('event_at')
            ->take(8)
            ->values();
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
