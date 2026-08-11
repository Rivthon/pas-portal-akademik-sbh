<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\LmsMateri;
use App\Models\LmsQuiz;
use App\Models\LmsTugas;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Services\LmsCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LmsController extends Controller
{
    public function index(Request $request, LmsCalendarService $calendar)
    {
        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        $taId = $request->input('ta_id', $activeTa?->ta_id);

        $jadwalList = Jadwal::query()
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
                'kurikulum.dosenToMatakuliah.dosen',
                'ruangan',
            ])
            ->withCount(['pertemuan', 'materi', 'tugas', 'quiz'])
            ->when($taId, fn ($query) => $query->where('ta_id', $taId))
            ->when($request->filled('prodi_id'), fn ($query) => $query
                ->where('jurusan_id', $request->prodi_id))
            ->when($request->filled('jenis_kelas'), fn ($query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', [strtolower($request->jenis_kelas)]))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);
                $query->where(function ($query) use ($search) {
                    $query->whereHas('kurikulum.mataKuliah', fn ($mataKuliah) => $mataKuliah
                        ->where('nama', 'like', '%'.$search.'%')
                        ->orWhere('matakuliah_id', 'like', '%'.$search.'%'))
                        ->orWhereHas('kurikulum.dosenToMatakuliah.dosen', fn ($dosen) => $dosen
                            ->where('nama', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('jurusan_id')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate(15)
            ->withQueryString();

        $calendarEvents = $calendar->events(
            Jadwal::query()
                ->when($taId, fn ($query) => $query->where('ta_id', $taId))
                ->pluck('id'),
            'admin',
            'admin',
            (int) auth()->id()
        );

        return view('admin.akademik.lms.index', [
            'jadwalList' => $jadwalList,
            'tahunAkademik' => TahunAkademik::orderByDesc('ta_id')->get(),
            'programStudi' => ProgramStudi::orderBy('nama')->get(),
            'selectedTaId' => $taId,
            'calendarEvents' => $calendarEvents,
        ]);
    }

    public function show(Jadwal $jadwal)
    {
        $jadwal->load([
            'kurikulum.mataKuliah',
            'kurikulum.programStudi',
            'kurikulum.dosenToMatakuliah.dosen',
            'ruangan',
            'pertemuan' => fn ($query) => $query
                ->with(['materi', 'tugas.pengumpulan', 'quiz.soal', 'quiz.attempts'])
                ->orderBy('tanggal_pertemuan')
                ->orderBy('pertemuan_id'),
        ]);

        $materiList = LmsMateri::where('jadwal_id', $jadwal->id)
            ->with('pertemuan')->orderBy('pertemuan_id')->get();
        $tugasList = LmsTugas::where('jadwal_id', $jadwal->id)
            ->with('pertemuan')->withCount('pengumpulan')->orderBy('pertemuan_id')->get();
        $quizList = LmsQuiz::where('jadwal_id', $jadwal->id)
            ->with(['pertemuan', 'soal'])->withCount('attempts')->orderBy('pertemuan_id')->get();
        $nomorPertemuan = $jadwal->pertemuan->pluck('pertemuan_id')->flip()
            ->map(fn ($index) => (int) $index + 1);

        $kelas = strtolower((string) $jadwal->jenis_kelas);
        $totalPeserta = Krs::where('kurikulum_id', $jadwal->kurikulum_id)
            ->where('ta_id', $jadwal->ta_id)
            ->whereHas('mahasiswa', function ($query) use ($kelas) {
                $kelas === 'karyawan'
                    ? $query->whereRaw('LOWER(kelas) = ?', ['karyawan'])
                    : $query->where(fn ($kelas) => $kelas->whereNull('kelas')
                        ->orWhereRaw('LOWER(kelas) != ?', ['karyawan']));
            })->distinct('mahasiswa_id')->count('mahasiswa_id');

        return view('admin.akademik.lms.show', compact(
            'jadwal', 'materiList', 'tugasList', 'quizList',
            'nomorPertemuan', 'totalPeserta'
        ));
    }

    public function showMateri(LmsMateri $materi)
    {
        abort_unless(
            $materi->file && Storage::disk('public')->exists($materi->file),
            404,
            'File materi tidak ditemukan.'
        );

        return response()->file(Storage::disk('public')->path($materi->file));
    }

    public function showMateriFile(string $filename)
    {
        abort_unless($filename === basename($filename), 404);

        $path = 'lms/materi/'.$filename;
        $materi = LmsMateri::where('file', $path)->firstOrFail();

        return $this->showMateri($materi);
    }
}
