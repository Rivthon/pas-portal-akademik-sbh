<?php

namespace App\Http\Controllers\Dosen;

use Carbon\Carbon;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use App\Models\CalenderAkademik;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class DashboardDosenController extends Controller
{
        public function index()
        {
            $dosen = auth('dosen')->user();
            $settings = Setting::first();
            $ta = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
            $kalenderAkademik = CalenderAkademik::where('jurusan_id', $dosen->jurusan_id)->get();
            $tanggalSekarang = Carbon::now()->translatedFormat('l, d F Y');

            // Ambil berita terbaru dari WordPress (maksimal 10 berita)
            // $response = Http::get('https://sbh.ac.id/wp-json/wp/v2/posts', [
            //     'per_page' => 20,
            //     'orderby' => 'date',
            //     'order' => 'desc'
            // ]);

            // $berita = collect($response->json())->map(function ($post) {
            //     $imageUrl = asset('assets/img/no-image.jpg'); // Default gambar jika tidak ada
            //     if (isset($post['_links']['wp:featuredmedia'][0]['href'])) {
            //         $mediaResponse = Http::get($post['_links']['wp:featuredmedia'][0]['href']);
            //         $media = $mediaResponse->json();
            //         $imageUrl = $media['source_url'] ?? $imageUrl;
            //     }
            //     return [
            //         'title' => $post['title']['rendered'],
            //         'date' => Carbon::parse($post['date'])->translatedFormat('d F Y'),
            //         'link' => $post['link'],
            //         'image' => $imageUrl,
            //     ];
            // });

            return view('pages-dosen.dashboard', compact(
                'tanggalSekarang',
                'settings',
                'ta',
                'kalenderAkademik',
                // 'berita'
            ));
        }

    }