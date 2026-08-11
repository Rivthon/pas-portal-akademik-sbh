<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\Kurikulum;
use App\Models\Pertemuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RekapAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $validated = $request->validate([
            'semester' => ['nullable', 'integer', 'min:1', 'max:14'],
        ]);

        $krsSemesters = Krs::query()
            ->join('kurikulum', 'kurikulum.kurikulum_id', '=', 'krs.kurikulum_id')
            ->join('matakuliah', 'matakuliah.matakuliah_id', '=', 'kurikulum.matakuliah_id')
            ->where('krs.mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->pluck('matakuliah.smt');
        $absensiSemesters = Absensi::query()
            ->join('jadwal', 'jadwal.id', '=', 'absensi.jadwal_id')
            ->join('kurikulum', 'kurikulum.kurikulum_id', '=', 'jadwal.kurikulum_id')
            ->join('matakuliah', 'matakuliah.matakuliah_id', '=', 'kurikulum.matakuliah_id')
            ->where('absensi.mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->pluck('matakuliah.smt');

        $semesterBerjalan = max(1, min(14, (int) ($mahasiswa->semester ?: 1)));
        $semesterMaksimal = $krsSemesters
            ->merge($absensiSemesters)
            ->push($semesterBerjalan)
            ->map(fn ($semester) => (int) $semester)
            ->filter(fn ($semester) => $semester >= 1 && $semester <= 14)
            ->max() ?: $semesterBerjalan;
        $semesterList = collect(range(1, $semesterMaksimal));
        $selectedSemester = (int) ($validated['semester'] ?? $semesterBerjalan);

        $krs = Krs::with([
            'kurikulum.mataKuliah',
            'kurikulum.programStudi',
        ])
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereHas('kurikulum.mataKuliah', fn ($query) => $query
                ->where('smt', $selectedSemester))
            ->get()
            ->keyBy('kurikulum_id');

        $absensiKurikulumIds = Absensi::query()
            ->join('jadwal', 'absensi.jadwal_id', '=', 'jadwal.id')
            ->join('kurikulum', 'kurikulum.kurikulum_id', '=', 'jadwal.kurikulum_id')
            ->join('matakuliah', 'matakuliah.matakuliah_id', '=', 'kurikulum.matakuliah_id')
            ->where('absensi.mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereNotNull('jadwal.kurikulum_id')
            ->where('matakuliah.smt', $selectedSemester)
            ->pluck('jadwal.kurikulum_id');

        $kurikulumIds = $krs->keys()
            ->merge($absensiKurikulumIds)
            ->filter()
            ->unique()
            ->values();

        $kurikulum = Kurikulum::with(['mataKuliah', 'programStudi'])
            ->whereIn('kurikulum_id', $kurikulumIds)
            ->whereHas('mataKuliah', fn ($query) => $query
                ->where('smt', $selectedSemester))
            ->get()
            ->keyBy('kurikulum_id');

        $jadwalDenganAbsensi = Absensi::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereIn('jadwal_id', Jadwal::whereIn('kurikulum_id', $kurikulumIds)->select('id'))
            ->pluck('jadwal_id')
            ->unique();
        $kelas = strtolower(trim((string) $mahasiswa->kelas));
        $jenisKelas = $kelas === 'karyawan' ? 'karyawan' : 'reguler';
        $jadwalPerKurikulum = Jadwal::whereIn('kurikulum_id', $kurikulumIds)
            ->where(function ($query) use ($jadwalDenganAbsensi, $jenisKelas) {
                $query->whereIn('id', $jadwalDenganAbsensi)
                    ->orWhereRaw('LOWER(jenis_kelas) = ?', [$jenisKelas]);
            })
            ->orderByDesc('ta_id')
            ->get()
            ->groupBy('kurikulum_id');
        $absensiPerJadwal = Absensi::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereIn('jadwal_id', $jadwalPerKurikulum->flatten()->pluck('id'))
            ->get()
            ->groupBy('jadwal_id');

        $rekap = $kurikulumIds
            ->map(function ($kurikulumId) use (
                $krs,
                $kurikulum,
                $mahasiswa,
                $jadwalPerKurikulum,
                $absensiPerJadwal
            ) {
                $item = $krs->get($kurikulumId) ?? new Krs([
                    'kurikulum_id' => $kurikulumId,
                    'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                ]);
                $item->setRelation('kurikulum', $kurikulum->get($kurikulumId));

                $jadwalIds = $jadwalPerKurikulum->get($kurikulumId, collect())->pluck('id');

                if ($jadwalIds->isEmpty()) {
                    $item->hadir = 0;
                    $item->izin = 0;
                    $item->sakit = 0;
                    $item->alpha = 0;
                    $item->persentase = 0;
                    $item->jadwal_id = null;
                    $item->detail_id = null;

                    return $item;
                }

                $absensi = $jadwalIds
                    ->flatMap(fn ($jadwalId) => $absensiPerJadwal->get($jadwalId, collect()));

                $hadir = $absensi->where('status', 'hadir')->count();
                $izin = $absensi->where('status', 'izin')->count();
                $sakit = $absensi->where('status', 'sakit')->count();
                $alpha = $absensi->where('status', 'tidak hadir')->count();
                $total = $absensi->count();

                $item->hadir = $hadir;
                $item->izin = $izin;
                $item->sakit = $sakit;
                $item->alpha = $alpha;
                $item->persentase = $total > 0 ? round(($hadir / $total) * 100) : 0;

                $item->jadwal_id = $jadwalIds->first();
                $item->detail_id = $kurikulumId;

                return $item;
            })
            ->groupBy(function ($item) {
                return optional(optional($item->kurikulum)->mataKuliah)->matakuliah_id;
            })
            ->map(function ($itemsPerMatkul) {
                $hadir = $itemsPerMatkul->sum('hadir');
                $izin = $itemsPerMatkul->sum('izin');
                $sakit = $itemsPerMatkul->sum('sakit');
                $alpha = $itemsPerMatkul->sum('alpha');
                $total = $hadir + $izin + $sakit + $alpha;

                $itemsPerMatkul = $itemsPerMatkul->values();
                $detailItem = $itemsPerMatkul
                    ->sortByDesc(function ($it) {
                        return ($it->hadir ?? 0) + ($it->izin ?? 0) + ($it->sakit ?? 0) + ($it->alpha ?? 0);
                    })
                    ->first();

                $first = $detailItem ?: $itemsPerMatkul->first();

                $first->hadir = $hadir;
                $first->izin = $izin;
                $first->sakit = $sakit;
                $first->alpha = $alpha;
                $first->persentase = $total > 0 ? round(($hadir / $total) * 100) : 0;

                $first->detail_id = $first->detail_id ?? optional($first)->detail_id;

                return $first;
            })
            ->values();

        return view('mahasiswa.rekap-absensi.index', [
            'mahasiswa' => $mahasiswa,
            'krs' => $rekap,
            'semesterList' => $semesterList,
            'selectedSemester' => $selectedSemester,
            'semesterBerjalan' => $semesterBerjalan,
        ]);
    }

    public function detail($id, Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $request->validate([
            'semester' => ['nullable', 'integer', 'min:1', 'max:14'],
        ]);

        $krs = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('kurikulum_id', $id)
            ->first();

        // 1) Anggap $id sebagai kurikulum_id
        $semuaJadwalIds = Jadwal::where('kurikulum_id', $id)->pluck('id');
        $jadwalDenganAbsensi = Absensi::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereIn('jadwal_id', $semuaJadwalIds)
            ->pluck('jadwal_id');
        $jenisKelas = strtolower((string) $mahasiswa->kelas) === 'karyawan'
            ? 'karyawan'
            : 'reguler';
        $jadwalIds = Jadwal::where('kurikulum_id', $id)
            ->where(function ($query) use ($jadwalDenganAbsensi, $jenisKelas) {
                $query->whereIn('id', $jadwalDenganAbsensi)
                    ->orWhereRaw('LOWER(jenis_kelas) = ?', [$jenisKelas]);
            })
            ->pluck('id');

        // 2) Jika kosong, anggap $id sebagai jadwal_id
        $jadwalLama = Jadwal::find($id);
        if ($jadwalIds->isEmpty() && $jadwalLama) {
            $jadwalIds = collect([$jadwalLama->id]);
            $id = $jadwalLama->kurikulum_id;

            if (! $krs) {
                $krs = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                    ->where('kurikulum_id', $id)
                    ->first();
            }
        }

        abort_unless($jadwalIds->isNotEmpty(), 404);
        abort_unless(
            $krs || Absensi::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->whereIn('jadwal_id', $jadwalIds)
                ->exists(),
            403
        );

        $jadwal = Jadwal::with([
            'kurikulum.mataKuliah',
            'kurikulum.programStudi',
            'programStudi',
            'dosen',
        ])
            ->whereIn('id', $jadwalIds)
            ->firstOrFail();
        $selectedSemester = (int) ($request->input('semester')
            ?: ($jadwal->kurikulum?->mataKuliah?->smt ?? $mahasiswa->semester));

        $kelasMahasiswa = strtolower((string) $mahasiswa->kelas);
        $lmsJadwal = Jadwal::whereIn('id', $jadwalIds)
            ->when($kelasMahasiswa === 'karyawan', fn ($query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', ['karyawan']))
            ->when($kelasMahasiswa !== 'karyawan', fn ($query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', ['reguler']))
            ->latest('ta_id')
            ->first();

        // Rekap absensi hanya memuat data kehadiran. Materi dan tugas tersedia di LMS.
        $pertemuan = Pertemuan::whereIn('jadwal_id', $jadwalIds)
            ->with([
                'absensi' => function ($query) use ($mahasiswa) {
                    $query->where('mahasiswa_id', $mahasiswa->mahasiswa_id);
                },
            ])
            ->orderBy('tanggal_pertemuan')
            ->orderBy('pertemuan_id')
            ->get();

        // Hitung statistik absensi
        $hadir = 0;
        $izin = 0;
        $sakit = 0;
        $alpha = 0;

        foreach ($pertemuan as $item) {
            $status = optional($item->absensi->first())->status;

            switch (strtolower($status ?? '')) {
                case 'hadir':
                    $hadir++;
                    break;
                case 'izin':
                    $izin++;
                    break;
                case 'sakit':
                    $sakit++;
                    break;
                case 'tidak hadir':
                case 'alpha':
                    $alpha++;
                    break;
            }
        }

        $total = $hadir + $izin + $sakit + $alpha;

        $persentase = $total > 0
            ? round(($hadir / $total) * 100)
            : 0;

        return view(
            'mahasiswa.rekap-absensi.detail',
            compact(
                'jadwal',
                'pertemuan',
                'hadir',
                'izin',
                'sakit',
                'alpha',
                'persentase',
                'lmsJadwal',
                'selectedSemester'
            )
        )->with('persentaseData', $persentase);
    }
}
