<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\LmsMateri;
use App\Models\LmsQuiz;
use App\Models\LmsTugas;
use App\Models\Pertemuan;
use App\Models\PertemuanPraktik;
use App\Models\ProductionError;
use App\Models\ProductionErrorEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ProductionErrorReporter
{
    private const CAPTURED_ATTRIBUTE = '_production_error_captured';

    public function captureException(Throwable $exception, ?Request $request = null): void
    {
        $status = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        if ($status < 500) {
            return;
        }

        $this->store([
            'exception_class' => $exception::class,
            'message' => $this->sanitizeText($exception->getMessage() ?: 'Kesalahan server tanpa pesan.'),
            'file' => $this->relativePath($exception->getFile()),
            'line' => $exception->getLine(),
            'trace' => $this->sanitizeTrace($exception->getTraceAsString()),
            'http_status' => $status,
        ], $request);
    }

    public function captureResponse(Request $request, int $status, mixed $content = null): void
    {
        if ($status < 500 || $request->attributes->get(self::CAPTURED_ATTRIBUTE)) {
            return;
        }

        $message = 'Respons HTTP '.$status.' dikembalikan oleh aplikasi.';
        if (is_array($content)) {
            $message = (string) ($content['error'] ?? $content['message'] ?? $message);
        } elseif (is_string($content) && Str::isJson($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $message = (string) ($decoded['error'] ?? $decoded['message'] ?? $message);
            }
        }

        $this->store([
            'exception_class' => null,
            'message' => $this->sanitizeText($message),
            'file' => null,
            'line' => null,
            'trace' => null,
            'http_status' => $status,
        ], $request);
    }

    private function store(array $error, ?Request $request): void
    {
        if (! config('system.monitoring.capture_production_errors', false)) {
            return;
        }

        try {
            if (! Schema::hasTable('production_errors')) {
                return;
            }

            $request ??= app()->bound('request') ? request() : null;
            $actor = $this->actor($request);
            $context = $this->context($request);
            $routeName = $request?->route()?->getName();
            $method = $request?->method();
            $path = $request ? '/'.ltrim($request->path(), '/') : null;
            $fingerprint = hash('sha256', implode('|', [
                $error['exception_class'] ?? 'http-response',
                $this->normalizedFingerprintMessage($error['message']),
                $routeName ?: $path,
                $error['file'] ?? '',
                $error['line'] ?? '',
            ]));
            $now = now();

            DB::transaction(function () use (
                $actor,
                $context,
                $error,
                $fingerprint,
                $method,
                $now,
                $path,
                $routeName
            ) {
                $existing = ProductionError::query()
                    ->where('fingerprint', $fingerprint)
                    ->lockForUpdate()
                    ->first();

                $latest = [
                    ...$error,
                    'method' => $method,
                    'path' => $path,
                    'route_name' => $routeName,
                    ...$actor,
                    'context' => $context,
                    'last_seen_at' => $now,
                ];

                if ($existing) {
                    $existing->update([
                        ...$latest,
                        'occurrences' => $existing->occurrences + 1,
                    ]);
                    $errorRecord = $existing;
                } else {
                    $errorRecord = ProductionError::create([
                        'fingerprint' => $fingerprint,
                        ...$latest,
                        'occurrences' => 1,
                        'first_seen_at' => $now,
                    ]);
                }

                $errorRecord->events()->create([
                    ...$actor,
                    'context' => $context,
                    'occurred_at' => $now,
                ]);

                $oldEventIds = $errorRecord->events()
                    ->latest('occurred_at')
                    ->latest('id')
                    ->skip(50)
                    ->take(1000)
                    ->pluck('id');
                if ($oldEventIds->isNotEmpty()) {
                    ProductionErrorEvent::whereIn('id', $oldEventIds)->delete();
                }
            });

            $request?->attributes->set(self::CAPTURED_ATTRIBUTE, true);
        } catch (Throwable $reportingFailure) {
            if (app()->runningUnitTests()) {
                throw $reportingFailure;
            }

            // Pelaporan error tidak boleh menimbulkan error kedua atau mengganggu respons pengguna.
        }
    }

    private function actor(?Request $request): array
    {
        $path = $request?->path() ?? '';
        $guards = match (true) {
            str_starts_with($path, 'dosen') => ['dosen', 'web', 'mahasiswa'],
            str_starts_with($path, 'mahasiswa'), str_starts_with($path, 'api/v1/mahasiswa') => ['mahasiswa', 'web', 'dosen'],
            default => ['web', 'dosen', 'mahasiswa'],
        };

        foreach ($guards as $guard) {
            try {
                if (! Auth::guard($guard)->check()) {
                    continue;
                }

                $user = Auth::guard($guard)->user();

                return [
                    'actor_guard' => $guard,
                    'actor_type' => class_basename($user),
                    'actor_id' => (string) $user->getAuthIdentifier(),
                    'actor_name' => $user->nama ?? $user->name ?? null,
                    'actor_identifier' => $user->nim
                        ?? $user->nidn
                        ?? $user->kd_dosen
                        ?? $user->email
                        ?? null,
                ];
            } catch (Throwable) {
                // Coba guard berikutnya.
            }
        }

        return [
            'actor_guard' => null,
            'actor_type' => null,
            'actor_id' => null,
            'actor_name' => 'Pengguna tidak terautentikasi',
            'actor_identifier' => null,
        ];
    }

    private function context(?Request $request): array
    {
        if (! $request) {
            return [];
        }

        $context = [
            'route_parameters' => collect($this->routeParameters($request))
                ->map(fn ($value) => $value instanceof Model ? $value->getKey() : $value)
                ->filter(fn ($value) => is_scalar($value) || $value === null)
                ->map(fn ($value) => is_string($value) ? Str::limit($value, 150) : $value)
                ->all(),
            'input_summary' => collect(['tanggal_pertemuan', 'topik', 'semester'])
                ->filter(fn (string $key) => $request->filled($key))
                ->mapWithKeys(fn (string $key) => [$key => Str::limit((string) $request->input($key), 150)])
                ->all(),
            'client' => Str::limit((string) $request->userAgent(), 255),
        ];

        if ($request->hasSession() && $request->session()->has('impersonator_admin_id')) {
            $context['impersonator_admin_id'] = (int) $request->session()->get('impersonator_admin_id');
        }

        $schedule = $this->resolveSchedule($request);
        if ($schedule) {
            $schedule->loadMissing(['kurikulum.mataKuliah', 'programStudi']);
            $mataKuliah = $schedule->kurikulum?->mataKuliah;
            $programStudi = $schedule->programStudi ?: $schedule->kurikulum?->programStudi;
            $context['akademik'] = [
                'jenis_jadwal' => $schedule instanceof JadwalPraktik ? 'praktik' : 'teori',
                'jadwal_id' => $schedule->getKey(),
                'mata_kuliah' => $mataKuliah?->nama,
                'kode_mata_kuliah' => $mataKuliah?->matakuliah_id,
                'semester' => $mataKuliah?->smt,
                'kelas' => match (strtolower((string) $schedule->jenis_kelas)) {
                    'karyawan' => 'Reguler B',
                    'reguler' => 'Reguler A',
                    default => $schedule->jenis_kelas,
                },
                'program_studi' => $programStudi?->nama,
                'tahun_akademik_id' => $schedule->ta_id,
            ];
        }

        return array_filter($context, fn ($value) => $value !== [] && $value !== null && $value !== '');
    }

    private function resolveSchedule(Request $request): Jadwal|JadwalPraktik|null
    {
        try {
            foreach ($this->routeParameters($request) as $parameter) {
                $schedule = $this->scheduleFromModel($parameter);
                if ($schedule) {
                    return $schedule;
                }
            }

            $theoryId = $request->input('jadwal_id')
                ?? $request->route('jadwal_id');
            if ($theoryId && is_numeric($theoryId)) {
                if (str_contains($request->path(), 'praktik')) {
                    return JadwalPraktik::find((int) $theoryId);
                }

                return Jadwal::find((int) $theoryId);
            }

            $practiceId = $request->input('jadwal_praktik_id')
                ?? $request->route('jadwal_praktik_id');
            if ($practiceId && is_numeric($practiceId)) {
                return JadwalPraktik::find((int) $practiceId);
            }

            $meetingId = $request->input('pertemuan_id')
                ?? $request->route('pertemuan_id');
            if ($meetingId && is_numeric($meetingId)) {
                return Pertemuan::with('jadwal')->find((int) $meetingId)?->jadwal;
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function routeParameters(Request $request): array
    {
        try {
            return $request->route()?->parameters() ?? [];
        } catch (Throwable) {
            return [];
        }
    }

    private function scheduleFromModel(mixed $model): Jadwal|JadwalPraktik|null
    {
        return match (true) {
            $model instanceof Jadwal, $model instanceof JadwalPraktik => $model,
            $model instanceof Pertemuan, $model instanceof LmsMateri,
            $model instanceof LmsTugas, $model instanceof LmsQuiz => $model->jadwal,
            $model instanceof PertemuanPraktik => $model->jadwal,
            default => null,
        };
    }

    private function normalizedFingerprintMessage(string $message): string
    {
        $message = preg_replace('/[0-9a-f]{8}-[0-9a-f-]{27,}/i', '{uuid}', $message) ?? $message;
        $message = preg_replace('/\b\d+\b/', '{n}', $message) ?? $message;

        return Str::lower(Str::limit($message, 1000, ''));
    }

    private function sanitizeText(string $text): string
    {
        $patterns = [
            '/(password|passwd|pwd|token|secret|api[_-]?key)(\s*[=:]\s*)[^\s,;]+/i' => '$1$2[disamarkan]',
            '/Bearer\s+[A-Za-z0-9._~+\/-]+=*/i' => 'Bearer [disamarkan]',
            '/([?&](?:password|token|secret|api_key)=)[^&\s]+/i' => '$1[disamarkan]',
        ];

        return Str::limit(trim((string) preg_replace(array_keys($patterns), array_values($patterns), $text)), 4000, '');
    }

    private function sanitizeTrace(string $trace): string
    {
        $trace = str_replace([base_path(), '\\'], ['[base]', '/'], $trace);

        return Str::limit($this->sanitizeText($trace), 20000, '');
    }

    private function relativePath(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);
        $base = rtrim(str_replace('\\', '/', base_path()), '/').'/';

        return str_starts_with($normalized, $base)
            ? substr($normalized, strlen($base))
            : basename($normalized);
    }
}
