<?php

namespace App\Http\Controllers;

use App\Models\LmsCalendarNote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LmsCalendarNoteController extends Controller
{
    public function store(Request $request)
    {
        [$ownerType, $ownerId] = $this->owner();
        $data = $this->validateNote($request);
        LmsCalendarNote::create($data + [
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
        ]);

        return back()->with('success', 'Catatan kalender pribadi berhasil ditambahkan.');
    }

    public function update(Request $request, LmsCalendarNote $note)
    {
        $this->ensureOwner($note);
        $note->update($this->validateNote($request));

        return back()->with('success', 'Catatan kalender pribadi berhasil diperbarui.');
    }

    public function destroy(LmsCalendarNote $note)
    {
        $this->ensureOwner($note);
        $note->delete();

        return back()->with('success', 'Catatan kalender pribadi berhasil dihapus.');
    }

    private function validateNote(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'note_date' => ['required', 'date'],
            'note_time' => ['nullable', 'date_format:H:i'],
            'color' => ['required', Rule::in(['primary', 'success', 'warning', 'danger', 'info'])],
        ]);
    }

    private function ensureOwner(LmsCalendarNote $note): void
    {
        [$ownerType, $ownerId] = $this->owner();
        abort_unless(
            $note->owner_type === $ownerType && (int) $note->owner_id === $ownerId,
            403,
            'Catatan ini bukan milik Anda.'
        );
    }

    private function owner(): array
    {
        if (auth('dosen')->check()) {
            return ['dosen', (int) auth('dosen')->id()];
        }
        if (auth('mahasiswa')->check()) {
            return ['mahasiswa', (int) auth('mahasiswa')->id()];
        }
        if (auth()->check()) {
            return ['admin', (int) auth()->id()];
        }

        abort(401);
    }
}
