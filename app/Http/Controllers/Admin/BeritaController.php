<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BeritaController extends Controller
{
    public function index()
    {
        return view('dosen.berita.index');
    }

    public function indexBerita()
    {
        return view('mahasiswa.berita.index');
    }

    public function getBeritaKampus()
    {
        $berita = Cache::remember('berita_wordpress', 3600, function () {
            $response = Http::get('https://api.sbh.ac.id/wp-json/wp/v2/posts', [
                'per_page' => 30,
                'orderby' => 'date',
                'order' => 'desc',
            ]);

            if ($response->failed()) {
                Log::error('Gagal mengambil data berita dari API WordPress');

                return [];
            }

            return collect($response->json())->map(fn ($post) => $this->formatBerita($post));
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
            $response = Http::get('https://api.sbh.ac.id/wp-json/wp/v2/posts?per_page=10&_embed');

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
                        asset('dashboard_assets/assets/img/img-not-found.jpg'),
                ];
            });
        });

        return view('dosen.berita.detail', compact('berita', 'beritaTerkait'));
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
            $response = Http::get('https://api.sbh.ac.id/wp-json/wp/v2/posts?per_page=10&_embed');

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

        return view('mahasiswa.berita.detail', compact('berita', 'beritaTerkait'));
    }

    private function formatBerita($post, $includeContent = false)
    {

        return [
            'id' => $post['id'],
            'title' => html_entity_decode($post['title']['rendered']),
            'date' => Carbon::parse($post['date'])->translatedFormat('d F Y'),
            'link' => $post['link'],

            'image' => $this->getFeaturedImage($post),
            'content' => $includeContent ? $this->sanitizeContent(
                $this->cleanElementorSliderFromContent(html_entity_decode($post['content']['rendered'] ?? ''))
            ) : null,
        ];
    }

    private function cleanElementorSliderFromContent($content)
    {
        libxml_use_internal_errors(true); // Supaya tidak error kalau HTML kurang rapi
        $dom = new DOMDocument;
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

        $xpath = new DOMXPath($dom);
        foreach ($xpath->query("//div[contains(@class, 'elementor-widget-slider')]") as $node) {
            $node->parentNode->removeChild($node);
        }

        return $dom->saveHTML();
    }

    private function sanitizeContent(string $content): string
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        foreach (iterator_to_array($xpath->query('//script|//iframe|//object|//embed|//link|//meta|//style')) as $node) {
            $node->parentNode?->removeChild($node);
        }

        foreach (iterator_to_array($xpath->query('//*')) as $element) {
            foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
                $name = strtolower($attribute->name);
                $value = trim($attribute->value);

                if (str_starts_with($name, 'on') || in_array($name, ['style', 'srcdoc', 'formaction', 'xlink:href'], true)) {
                    $element->removeAttribute($attribute->name);

                    continue;
                }

                if (in_array($name, ['href', 'src'], true)
                    && ! preg_match('#^(https?:|mailto:|/|\#)#i', $value)) {
                    $element->removeAttribute($attribute->name);
                }
            }
        }

        libxml_clear_errors();

        return $dom->saveHTML();
    }

    private function getFeaturedImage($post)
    {
        if (! isset($post['_links']['wp:featuredmedia'][0]['href'])) {
            return asset('assets/img/no-image.jpg');
        }

        try {
            $mediaResponse = Http::get($post['_links']['wp:featuredmedia'][0]['href']);

            return $mediaResponse->successful() ? ($mediaResponse->json()['source_url'] ?? asset('assets/img/no-image.jpg')) : asset('assets/img/no-image.jpg');
        } catch (\Exception $e) {
            Log::error('Gagal mengambil gambar berita: '.$e->getMessage());

            return asset('assets/img/no-image.jpg');
        }
    }

    public function getBerita()
    {
        $berita = Cache::remember('berita_wordpress', 3600, function () {
            $response = Http::get('https://api.sbh.ac.id/wp-json/wp/v2/posts', [
                'per_page' => 30,
                'orderby' => 'date',
                'order' => 'desc',
            ]);

            if ($response->failed()) {
                Log::error('Gagal mengambil data berita dari API WordPress');

                return [];
            }

            return collect($response->json())->map(fn ($post) => $this->formatBerita($post));
        });

        return response()->json($berita);
    }
}
