<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DosenCodeService
{
    public const INSTITUTION_CODE = '043365';

    private const PROGRAM_FORMATS = [
        'KEBIDANAN' => ['prefix' => 'DTB', 'code' => '01'],
        'FARMASI' => ['prefix' => 'DTF', 'code' => '02'],
        'GIZI' => ['prefix' => 'DTG', 'code' => '03'],
    ];

    public function formatForJurusan(string|int $jurusanId): ?array
    {
        $name = ProgramStudi::query()
            ->where('jurusan_id', $jurusanId)
            ->value('nama');

        if (! $name) {
            return null;
        }

        $normalizedName = Str::upper(trim($name));

        foreach (self::PROGRAM_FORMATS as $programName => $format) {
            if (Str::contains($normalizedName, $programName)) {
                return $format + ['name' => $programName];
            }
        }

        return null;
    }

    public function buildCode(array $format, string|int $sequence, bool $preserveWidth = false): string
    {
        $sequence = preg_replace('/\D/', '', (string) $sequence) ?? '';
        if ($sequence === '') {
            throw new InvalidArgumentException('Nomor urut kode dosen tidak valid.');
        }

        if (! $preserveWidth) {
            $sequence = str_pad((string) ((int) $sequence), 3, '0', STR_PAD_LEFT);
        }

        return $format['prefix'].self::INSTITUTION_CODE.$format['code'].$sequence;
    }

    public function legacySequence(?string $code): ?string
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return null;
        }

        if (preg_match('/'.self::INSTITUTION_CODE.'(\d+)$/', $code, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/(\d+)$/', $code, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    public function isNormalized(?string $code, array $format): bool
    {
        return preg_match(
            '/^'.preg_quote($format['prefix'].self::INSTITUTION_CODE.$format['code'], '/').'\d{3,}$/',
            strtoupper(trim((string) $code))
        ) === 1;
    }

    public function sequenceForFormat(?string $code, array $format): ?string
    {
        if (! $this->isNormalized($code, $format)) {
            return $this->legacySequence($code);
        }

        $prefix = $format['prefix'].self::INSTITUTION_CODE.$format['code'];

        return substr(strtoupper(trim((string) $code)), strlen($prefix)) ?: null;
    }

    public function nextCode(string|int $jurusanId): string
    {
        $format = $this->formatForJurusan($jurusanId);
        if (! $format) {
            throw new InvalidArgumentException('Format kode dosen untuk program studi ini belum tersedia.');
        }

        $maximum = Dosen::query()
            ->where('jurusan_id', $jurusanId)
            ->pluck('kd_dosen')
            ->map(fn ($code) => (int) ($this->sequenceForFormat($code, $format) ?? 0))
            ->max() ?? 0;

        return $this->buildCode($format, $maximum + 1);
    }
}
