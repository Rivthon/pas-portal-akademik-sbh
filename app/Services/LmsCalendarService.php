<?php

namespace App\Services;

use App\Models\LmsCalendarNote;
use App\Models\LmsMateri;
use App\Models\LmsQuiz;
use App\Models\LmsTugas;
use Illuminate\Support\Collection;

class LmsCalendarService
{
    public function events(Collection $jadwalIds, string $viewer, string $ownerType, int $ownerId): array
    {
        $jadwalIds = $jadwalIds->map(fn ($id) => (int) $id)->unique()->values();
        $events = collect();

        if ($jadwalIds->isNotEmpty()) {
            $events = $events
                ->concat($this->materiEvents($jadwalIds, $viewer))
                ->concat($this->tugasEvents($jadwalIds, $viewer))
                ->concat($this->quizEvents($jadwalIds, $viewer));
        }

        return $events
            ->concat($this->noteEvents($ownerType, $ownerId))
            ->sortBy(fn ($event) => $event['date'].' '.($event['time'] ?? '00:00'))
            ->values()
            ->all();
    }

    private function materiEvents(Collection $jadwalIds, string $viewer): Collection
    {
        return LmsMateri::with('jadwal.kurikulum.mataKuliah')
            ->whereIn('jadwal_id', $jadwalIds)
            ->where('status', 1)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($materi) => [
                'id' => 'materi-'.$materi->materi_id,
                'type' => 'materi',
                'title' => 'Materi baru: '.$materi->judul,
                'subtitle' => $this->mataKuliah($materi),
                'date' => $materi->created_at->toDateString(),
                'time' => $materi->created_at->format('H:i'),
                'color' => 'info',
                'icon' => 'bx-file',
                'url' => $this->url($viewer, 'materi', $materi),
                'private' => false,
            ]);
    }

    private function tugasEvents(Collection $jadwalIds, string $viewer): Collection
    {
        return LmsTugas::with('jadwal.kurikulum.mataKuliah')
            ->whereIn('jadwal_id', $jadwalIds)
            ->where('aktif', true)
            ->whereNotNull('deadline')
            ->orderBy('deadline')
            ->get()
            ->map(fn ($tugas) => [
                'id' => 'tugas-'.$tugas->tugas_id,
                'type' => 'tugas',
                'title' => 'Deadline tugas: '.$tugas->judul,
                'subtitle' => $this->mataKuliah($tugas),
                'date' => $tugas->deadline->toDateString(),
                'time' => $tugas->deadline->format('H:i'),
                'color' => 'warning',
                'icon' => 'bx-task',
                'url' => $this->url($viewer, 'tugas', $tugas),
                'private' => false,
            ]);
    }

    private function quizEvents(Collection $jadwalIds, string $viewer): Collection
    {
        return LmsQuiz::with('jadwal.kurikulum.mataKuliah')
            ->whereIn('jadwal_id', $jadwalIds)
            ->where('aktif', true)
            ->orderBy('mulai_at')
            ->get()
            ->flatMap(function ($quiz) use ($viewer) {
                $events = collect();
                if ($quiz->mulai_at) {
                    $events->push([
                        'id' => 'quiz-start-'.$quiz->quiz_id,
                        'type' => 'quiz',
                        'title' => 'Ujian dimulai: '.$quiz->judul,
                        'subtitle' => $this->mataKuliah($quiz),
                        'date' => $quiz->mulai_at->toDateString(),
                        'time' => $quiz->mulai_at->format('H:i'),
                        'color' => 'primary',
                        'icon' => 'bx-question-mark',
                        'url' => $this->url($viewer, 'quiz', $quiz),
                        'private' => false,
                    ]);
                }
                if ($quiz->deadline) {
                    $events->push([
                        'id' => 'quiz-deadline-'.$quiz->quiz_id,
                        'type' => 'quiz',
                        'title' => 'Deadline ujian: '.$quiz->judul,
                        'subtitle' => $this->mataKuliah($quiz),
                        'date' => $quiz->deadline->toDateString(),
                        'time' => $quiz->deadline->format('H:i'),
                        'color' => 'danger',
                        'icon' => 'bx-time-five',
                        'url' => $this->url($viewer, 'quiz', $quiz),
                        'private' => false,
                    ]);
                }

                return $events;
            });
    }

    private function noteEvents(string $ownerType, int $ownerId): Collection
    {
        return LmsCalendarNote::where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->orderBy('note_date')
            ->orderBy('note_time')
            ->get()
            ->map(fn ($note) => [
                'id' => 'note-'.$note->note_id,
                'note_id' => $note->note_id,
                'type' => 'note',
                'title' => $note->title,
                'subtitle' => $note->description,
                'date' => $note->note_date->toDateString(),
                'time' => $note->note_time ? substr($note->note_time, 0, 5) : null,
                'color' => $note->color,
                'icon' => 'bx-note',
                'url' => null,
                'private' => true,
            ]);
    }

    private function mataKuliah($item): string
    {
        return $item->jadwal?->kurikulum?->mataKuliah?->nama ?? 'Mata kuliah';
    }

    private function url(string $viewer, string $type, $item): string
    {
        if ($viewer === 'admin') {
            return route('admin.lms.show', $item->jadwal_id);
        }
        if ($viewer === 'dosen') {
            return match ($type) {
                'materi' => route('dosen.lms.kelola', $item->jadwal_id),
                'tugas' => route('dosen.lms.tugas.pengumpulan', $item),
                'quiz' => route('dosen.lms.quiz.manage', $item),
            };
        }

        return match ($type) {
            'materi' => route('mahasiswa.lms.materi.show', $item),
            'tugas' => route('mahasiswa.lms.tugas.show', $item),
            'quiz' => route('mahasiswa.lms.quiz.show', $item),
        };
    }
}
