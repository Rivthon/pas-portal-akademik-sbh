<?php

namespace App\Http\Controllers;

use DOMXPath;
use DOMDocument;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BeritaController extends Controller
{
    public function index()
    {
        return view('pages-dosen.berita.index');
    }
    public function indexBerita()
    {
        return view('students.berita.index');
    }
    //  public function indexBeritaDosen()
    // {
    //     return view('pages-dosen.berita.index');
    // }

    public function getBeritaKampus()
    {
        $berita = Cache::remember('berita_wordpress', 3600, function () {
            $response = Http::get('https://api.sbh.ac.id/wp-json/wp/v2/posts', [
                'per_page' => 30,
                'orderby' => 'date',
                'order' => 'desc'
            ]);

            if ($response->failed()) {
                Log::error("Gagal mengambil data berita dari API WordPress");
                return [];
            }

            return collect($response->json())->map(fn($post) => $this->formatBerita($post));
        });

        return response()->json($berita);
    }
    public function getDetailBeritaDosen($id)
    {
        // Ambil berita utama berdasarkan ID
        $berita = Cache::remember("berita_wordpress_{$id}", 3600, function () use ($id) {
            $response = Http::get("https://api.sbh.ac.id/wp-json/wp/v2/posts/{$id}");

            if ($response->failed()) {
                abort(404, 'Berita tidak ditemukan');
            }

            return $this->formatBerita($response->json(), true);
        });

        // Ambil 10 berita terbaru atau populer
        $beritaTerkait = Cache::remember('berita_terkait', 3600, function () {
            $response = Http::get("https://api.sbh.ac.id/wp-json/wp/v2/posts?per_page=10&_embed");

            if ($response->failed()) {
                return [];
            }

            return collect($response->json())->map(function ($post) {
                return [
                    'id' => $post['id'],
                    'title' => $post['title']['rendered'],
                    'link' => $post['link'],
                    'image' => isset($post['_embedded']['wp:featuredmedia'][0]['source_url']) ?
                        $post['_embedded']['wp:featuredmedia'][0]['source_url'] :
                        asset('assets/img/no-image.jpg'),
                ];
            });
        });

        return view('pages-dosen.berita.detail', compact('berita', 'beritaTerkait'));
    }

    public function getDetailBerita($id)
    {
        // Ambil berita utama berdasarkan ID
        $berita = Cache::remember("berita_wordpress_{$id}", 3600, function () use ($id) {
            $response = Http::get("https://api.sbh.ac.id/wp-json/wp/v2/posts/{$id}");

            if ($response->failed()) {
                abort(404, 'Berita tidak ditemukan');
            }

            return $this->formatBerita($response->json(), true);
        });

        // Ambil 10 berita terbaru atau populer
        $beritaTerkait = Cache::remember('berita_terkait', 3600, function () {
            $response = Http::get("https://api.sbh.ac.id/wp-json/wp/v2/posts?per_page=10&_embed");

            if ($response->failed()) {
                return [];
            }

            return collect($response->json())->map(function ($post) {
                return [
                    'id' => $post['id'],
                    'title' => $post['title']['rendered'],
                    'link' => $post['link'],
                    'image' => isset($post['_embedded']['wp:featuredmedia'][0]['source_url']) ?
                        $post['_embedded']['wp:featuredmedia'][0]['source_url'] :
                        asset('assets/img/no-image.jpg'),
                ];
            });
        });

        return view('students.berita.detail', compact('berita', 'beritaTerkait'));
    }


   private function formatBerita($post, $includeContent = false)
    {

        return [
            'id' => $post['id'],
            'title' => html_entity_decode($post['title']['rendered']),
            'date' => Carbon::parse($post['date'])->translatedFormat('d F Y'),
            'link' => $post['link'],

            'image' => $this->getFeaturedImage($post),
            'content' => $includeContent ? html_entity_decode($post['content']['rendered'] ?? '') : null,
             'content' => $includeContent ? $this->cleanElementorSliderFromContent(html_entity_decode($post['content']['rendered'] ?? '')) : null,
        ];
    }

    private function cleanElementorSliderFromContent($content)
    {
        libxml_use_internal_errors(true); // Supaya tidak error kalau HTML kurang rapi
        $dom = new DOMDocument();
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

        $xpath = new DOMXPath($dom);
        foreach ($xpath->query("//div[contains(@class, 'elementor-widget-slider')]") as $node) {
            $node->parentNode->removeChild($node);
        }

        return $dom->saveHTML();
    }

    private function getFeaturedImage($post)
    {
        if (!isset($post['_links']['wp:featuredmedia'][0]['href'])) {
            return asset('assets/img/no-image.jpg');
        }

        try {
            $mediaResponse = Http::get($post['_links']['wp:featuredmedia'][0]['href']);
            return $mediaResponse->successful() ? ($mediaResponse->json()['source_url'] ?? asset('assets/img/no-image.jpg')) : asset('assets/img/no-image.jpg');
        } catch (\Exception $e) {
            Log::error("Gagal mengambil gambar berita: " . $e->getMessage());
            return asset('assets/img/no-image.jpg');
        }
    }

    public function getBerita()
    {
        $berita = Cache::remember('berita_wordpress', 3600, function () {
            $response = Http::get('https://api.sbh.ac.id/wp-json/wp/v2/posts', [
                'per_page' => 30,
                'orderby' => 'date',
                'order' => 'desc'
            ]);

            if ($response->failed()) {
                Log::error("Gagal mengambil data berita dari API WordPress");
                return [];
            }

            return collect($response->json())->map(fn($post) => $this->formatBerita($post));
        });

        return response()->json($berita);
    }
}
