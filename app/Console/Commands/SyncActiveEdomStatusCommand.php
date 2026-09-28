<?php

namespace App\Console\Commands;

use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Services\EdomCompletionService;
use Illuminate\Console\Command;

class SyncActiveEdomStatusCommand extends Command
{
    protected $signature = 'edom:sync-active-status';

    protected $description = 'Sinkronkan status EDOM legacy dari jawaban nyata pada tahun akademik aktif';

    public function handle(EdomCompletionService $completion): int
    {
        $taId = TahunAkademik::query()->where('status_ta', 1)->value('ta_id');
        if (! $taId) {
            $this->error('Tahun akademik aktif tidak ditemukan.');

            return self::FAILURE;
        }

        $updated = 0;
        Mahasiswa::query()->select(['mahasiswa_id', 'status_edom'])->chunkById(100, function ($students) use ($completion, $taId, &$updated) {
            foreach ($students as $student) {
                $expected = $completion->isComplete($student, (int) $taId) ? 1 : 0;
                if ((int) $student->status_edom !== $expected) {
                    $student->forceFill(['status_edom' => $expected])->saveQuietly();
                    $updated++;
                }
            }
        }, 'mahasiswa_id');

        $this->info($updated.' status EDOM mahasiswa disinkronkan untuk TA '.$taId.'.');

        return self::SUCCESS;
    }
}
