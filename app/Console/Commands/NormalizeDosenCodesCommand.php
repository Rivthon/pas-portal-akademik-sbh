<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Services\DosenCodeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeDosenCodesCommand extends Command
{
    protected $signature = 'dosen:normalize-codes {--apply : Terapkan perubahan kode dosen}';

    protected $description = 'Preview atau normalisasi kode dosen berdasarkan program studi tanpa mengubah nomor urut lama';

    public function handle(DosenCodeService $codeService): int
    {
        $lecturers = Dosen::query()
            ->with('programStudi')
            ->orderBy('dosen_id')
            ->get();

        $changes = [];
        foreach ($lecturers->groupBy('jurusan_id') as $jurusanId => $group) {
            $format = $codeService->formatForJurusan($jurusanId);
            if (! $format) {
                continue;
            }

            $maximum = $group
                ->pluck('kd_dosen')
                ->map(fn ($code) => (int) ($codeService->sequenceForFormat($code, $format) ?? 0))
                ->max() ?? 0;

            foreach ($group as $dosen) {
                if ($codeService->isNormalized($dosen->kd_dosen, $format)) {
                    continue;
                }

                $sequence = $codeService->legacySequence($dosen->kd_dosen);
                $preserveWidth = $sequence !== null;

                if ($sequence === null) {
                    $sequence = (string) ++$maximum;
                }

                $newCode = $codeService->buildCode($format, $sequence, $preserveWidth);
                $changes[] = [
                    'dosen' => $dosen,
                    'old' => $dosen->kd_dosen ?: '-',
                    'new' => $newCode,
                    'program' => $format['name'],
                ];
            }
        }

        if ($changes === []) {
            $this->info('Semua kode dosen pada prodi yang didukung sudah sesuai.');

            return self::SUCCESS;
        }

        $changedIds = collect($changes)->pluck('dosen.dosen_id');
        $targetCodes = collect($changes)->pluck('new');
        $unchangedCodes = $lecturers
            ->reject(fn (Dosen $dosen) => $changedIds->contains($dosen->dosen_id))
            ->pluck('kd_dosen')
            ->filter()
            ->map(fn ($code) => strtoupper(trim($code)));

        $duplicateTargets = $targetCodes
            ->duplicates()
            ->merge($targetCodes->filter(fn ($code) => $unchangedCodes->contains($code)))
            ->unique()
            ->values();

        $this->table(
            ['ID', 'Nama', 'Prodi', 'Kode Lama', 'Kode Baru'],
            collect($changes)->map(fn ($change) => [
                $change['dosen']->dosen_id,
                $change['dosen']->nama,
                $change['program'],
                $change['old'],
                $change['new'],
            ])->all()
        );

        if ($duplicateTargets->isNotEmpty()) {
            $this->error('Normalisasi dibatalkan karena kode tujuan duplikat: '.$duplicateTargets->implode(', '));

            return self::FAILURE;
        }

        if (! $this->option('apply')) {
            $this->warn('Ini hanya preview. Jalankan kembali dengan --apply untuk menyimpan perubahan.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($changes): void {
            foreach ($changes as $change) {
                Dosen::query()
                    ->whereKey($change['dosen']->getKey())
                    ->update(['kd_dosen' => $change['new']]);
            }
        });

        $this->info(count($changes).' kode dosen berhasil dinormalisasi.');

        return self::SUCCESS;
    }
}
