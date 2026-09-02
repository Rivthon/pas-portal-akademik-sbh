<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Services\DosenCodeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DosenCodeNormalizationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_command_preserves_legacy_sequence_and_applies_program_code(): void
    {
        $dosen = Dosen::query()
            ->whereHas('programStudi', fn ($query) => $query->where('nama', 'GIZI'))
            ->firstOrFail();
        $dosen->forceFill(['kd_dosen' => 'DTG043365099'])->save();

        $this->artisan('dosen:normalize-codes', ['--apply' => true])
            ->assertSuccessful();

        $this->assertSame('DTG04336503099', $dosen->fresh()->kd_dosen);
    }

    public function test_preview_does_not_change_codes(): void
    {
        $dosen = Dosen::query()
            ->whereHas('programStudi', fn ($query) => $query->where('nama', 'FARMASI'))
            ->firstOrFail();

        $dosen->forceFill(['kd_dosen' => 'DTF043365008'])->save();

        $this->artisan('dosen:normalize-codes')
            ->expectsOutputToContain('Ini hanya preview')
            ->assertSuccessful();

        $this->assertSame('DTF043365008', $dosen->fresh()->kd_dosen);
    }

    public function test_next_code_counts_only_the_sequence_after_program_code(): void
    {
        $program = ProgramStudi::query()->where('nama', 'FARMASI')->firstOrFail();
        $service = app(DosenCodeService::class);
        $format = $service->formatForJurusan($program->jurusan_id);
        $maximum = Dosen::query()
            ->where('jurusan_id', $program->jurusan_id)
            ->pluck('kd_dosen')
            ->map(fn ($code) => (int) ($service->sequenceForFormat($code, $format) ?? 0))
            ->max();

        $nextCode = $service->nextCode($program->jurusan_id);

        $this->assertSame(
            $service->buildCode($format, $maximum + 1),
            $nextCode
        );
    }
}
