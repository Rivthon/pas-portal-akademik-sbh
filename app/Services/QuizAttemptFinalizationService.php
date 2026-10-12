<?php

namespace App\Services;

use App\Models\LmsQuizAttempt;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class QuizAttemptFinalizationService
{
    public function expiresAt(LmsQuizAttempt $attempt): ?CarbonInterface
    {
        $attempt->loadMissing('quiz');
        $quiz = $attempt->quiz;
        if (! $quiz) {
            return null;
        }

        $candidates = collect();

        // Izin ulang dari dosen boleh melewati deadline lama, tetapi durasi
        // pengerjaan baru tetap dihitung dari started_at yang sudah direset.
        if (! $attempt->izinkan_ulang && $quiz->deadline) {
            $candidates->push($quiz->deadline->copy());
        }

        if ($quiz->durasi_menit && $attempt->started_at) {
            $candidates->push($attempt->started_at->copy()->addMinutes($quiz->durasi_menit));
        }

        return $candidates->sortBy(fn (CarbonInterface $date) => $date->getTimestamp())->first();
    }

    public function isExpired(LmsQuizAttempt $attempt): bool
    {
        $expiresAt = $this->expiresAt($attempt);

        return $expiresAt !== null && now()->greaterThanOrEqualTo($expiresAt);
    }

    public function canBeAutomaticallyFinalized(LmsQuizAttempt $attempt): bool
    {
        return $attempt->status === 'draft' || (bool) $attempt->izinkan_ulang;
    }

    public function finalize(LmsQuizAttempt $attempt): LmsQuizAttempt
    {
        return DB::transaction(function () use ($attempt) {
            $lockedAttempt = LmsQuizAttempt::query()
                ->whereKey($attempt->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->canBeAutomaticallyFinalized($lockedAttempt)) {
                return $lockedAttempt;
            }

            $lockedAttempt->load(['quiz.soal', 'jawaban']);
            $nilaiPg = 0.0;
            $adaEssay = false;
            $jawabanBySoal = $lockedAttempt->jawaban->keyBy('soal_id');

            foreach ($lockedAttempt->quiz->soal as $soal) {
                $jawaban = $jawabanBySoal->get($soal->soal_id)
                    ?? $lockedAttempt->jawaban()->create(['soal_id' => $soal->soal_id]);

                if ($soal->tipe === 'essay') {
                    $adaEssay = true;
                    $jawaban->update(['nilai' => null, 'feedback' => null]);

                    continue;
                }

                $nilai = $jawaban->pilihan_jawaban !== null
                    && (string) $jawaban->pilihan_jawaban === (string) $soal->kunci_jawaban
                        ? (float) $soal->bobot
                        : 0.0;
                $jawaban->update(['nilai' => $nilai, 'feedback' => null]);
                $nilaiPg += $nilai;
            }

            $lockedAttempt->update([
                'status' => $adaEssay ? 'submitted' : 'graded',
                'submitted_at' => now(),
                'nilai_pg' => $nilaiPg,
                'nilai_essay' => $adaEssay ? null : 0,
                'nilai_total' => $adaEssay ? null : $nilaiPg,
                'feedback' => null,
                'dinilai_at' => $adaEssay ? null : now(),
                'dinilai_oleh' => null,
                'izinkan_ulang' => false,
            ]);

            return $lockedAttempt->fresh(['quiz.jadwal']);
        });
    }
}
