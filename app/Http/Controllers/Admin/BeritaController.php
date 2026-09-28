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
        return response()->json($this->articles());
    }

    public function getDetailBeritaDosen($id)
    {
        [$berita, $beritaTerkait] = $this->articleDetail($id);

        return view('dosen.berita.detail', compact('berita', 'beritaTerkait'));
    }

    public function getDetailBerita($id)
    {
        [$berita, $beritaTerkait] = $this->articleDetail($id);

        return view('mahasiswa.berita.detail', compact('berita', 'beritaTerkait'));
    }

    private function articles()
    {
        $cacheKey = 'berita_sbh_articles_v1';
        $fallbackKey = $cacheKey.'_last_success';

        return Cache::remember($cacheKey, now()->addHour(), function () use ($fallbackKey) {
            try {
                $response = Http::acceptJson()
                    ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                    ->connectTimeout(3)
                    ->timeout(8)
                    ->retry(2, 200, throw: false)
                    ->get('https://sbh.ac.id/api/articles');

                if ($response->failed() || ! is_array($response->json('data'))) {
                    Log::warning('API artikel SBH gagal merespons.', ['status' => $response->status()]);

                    return Cache::get($fallbackKey, collect());
                }

                $articles = collect($response->json('data'))
                    ->map(fn (array $article) => $this->formatArticle($article, true))
                    ->values();

                Cache::forever($fallbackKey, $articles);

                return $articles;
            } catch (\Throwable $exception) {
                Log::warning('API artikel SBH tidak dapat dihubungi.', [
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);

                return Cache::get($fallbackKey, collect());
            }
        });
    }

    private function articleDetail($id): array
    {
        $articles = collect($this->articles());
        $berita = $articles->firstWhere('id', (int) $id);
        abort_unless($berita, 404, 'Berita tidak ditemukan atau belum tersedia pada feed terbaru.');

        return [
            $berita,
            $articles->where('id', '!=', (int) $id)->take(5)->values(),
        ];
    }

    private function formatArticle(array $article, bool $includeContent = false): array
    {
        $slug = trim((string) ($article['slug'] ?? ''));
        $thumbnail = ltrim((string) ($article['thumbnail'] ?? ''), '/');

        return [
            'id' => (int) ($article['id'] ?? 0),
            'title' => html_entity_decode((string) ($article['title'] ?? 'Tanpa Judul')),
            'date' => Carbon::parse($article['published_at'] ?? $article['created_at'] ?? now())
                ->translatedFormat('d F Y'),
            'link' => $slug !== '' ? 'https://sbh.ac.id/artikel/'.$slug : 'https://sbh.ac.id/artikel',
            'image' => $thumbnail !== ''
                ? 'https://sbh.ac.id/storage/'.$thumbnail
                : asset('assets/img/no-image.jpg'),
            'content' => $includeContent
                ? $this->sanitizeContent((string) ($article['content'] ?? ''))
                : null,
        ];
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

    public function getBerita()
    {
        return response()->json($this->articles());
    }
}
