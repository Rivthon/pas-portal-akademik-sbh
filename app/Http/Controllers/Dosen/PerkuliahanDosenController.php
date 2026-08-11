<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiPraktik;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\Mahasiswa;
use App\Models\Pertemuan;
use App\Models\PertemuanPraktik;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;

class PerkuliahanDosenController extends Controller
{
    public function jadwalIndex()
    {
        $dosen = auth('dosen')->user();

        // Pastikan ada dosen yang login
        if (! $dosen) {
            return redirect()->route('login')->with('error', 'Silakan login sebagai dosen!');
        }

        // Ambil Tahun Akademik aktif
        $activeTA = TahunAkademik::where('status_ta', 1)
            ->orderByDesc('ta_id')
            ->first(['ta_id', 'nama', 'semester']);

        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        // Tampilkan seluruh jadwal teori yang ditugaskan, termasuk penugasan lintas program studi.
        $jadwalList = Jadwal::where('ta_id', $activeTA->ta_id)
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->dosen_id)
                    ->whereRaw('LOWER(jenis_dosen) = ?', ['teori']);
            })
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
                'kurikulum.dosenToMatakuliah.dosen',
                'ruangan',
            ])
            ->get()
            ->groupBy(function ($jadwal) {
                return $jadwal->hari ?? 'Tidak Ada Hari';
            })
            ->map(function ($jadwalPerHari) {
                return $jadwalPerHari->map(function ($jadwal) {

                    return [
                        'jadwal_id' => $jadwal->id,
                        // 'ta_id' => $jadwal->ta_id ?? 'Tidak ada data',
                        'hari' => $jadwal->hari ?? 'Tidak ada data',
                        'jam_mulai' => $jadwal->jam_mulai ?? 'Tidak ada data',
                        'jam_selesai' => $jadwal->jam_selesai ?? 'Tidak ada data',
                        'kode_matakuliah' => $jadwal->kurikulum->mataKuliah->matakuliah_id ?? null,
                        'nama_matakuliah' => $jadwal->kurikulum->mataKuliah->nama ?? 'Tidak ada data',
                        'program_studi' => $jadwal->kurikulum->programStudi?->nama ?? 'Tidak ada data',
                        'semester_matkul' => $jadwal->kurikulum->mataKuliah->smt ?? 'Tidak ada data',
                        'jenis_kelas' => $jadwal->jenis_kelas ?? 'Tidak ada data',
                        'ruangan' => $jadwal->ruangan->nama ?? 'Tidak ada data',
                        'dosen' => $jadwal->kurikulum->dosenToMatakuliah
                            ->map(function ($dosenToMatakuliah) {
                                return [
                                    'id' => $dosenToMatakuliah->dosen->dosen_id ?? null,
                                    'nama' => $dosenToMatakuliah->dosen->nama ?? 'Tidak ada data',
                                    'jenis_dosen' => $dosenToMatakuliah->jenis_dosen ?? 'tidak diketahui',
                                    'jenis_kelas' => $dosenToMatakuliah->jenis_kelas ?? 'tidak diketahui',
                                ];
                            })
                            ->filter(function ($dosen) {
                                return strtolower((string) $dosen['jenis_dosen']) === 'teori';
                            })
                            ->unique('id')
                            ->values(),
                    ];
                });
            });

        activity_log('lihat_jadwal', 'Dosen melihat jadwal mengajar');

        return view('dosen.jadwal.index', compact('jadwalList', 'activeTA'));
    }

    public function search(Request $request)
    {
        $query = $request->search;
        $dosen = auth('dosen')->user();
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $jadwalList = Jadwal::where('ta_id', $activeTA->ta_id)
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->dosen_id)
                    ->whereRaw('LOWER(jenis_dosen) = ?', ['teori']);
            })
            ->with(['kurikulum.mataKuliah', 'kurikulum.programStudi', 'kurikulum.dosenToMatakuliah.dosen', 'ruangan'])
            ->get()
            ->groupBy(function ($jadwal) {
                return $jadwal->hari ?? 'Tidak Ada Hari';
            })
            ->map(function ($jadwalPerHari) {
                return $jadwalPerHari->map(function ($jadwal) {
                    return [
                        'jadwal_id' => $jadwal->id,
                        'hari' => $jadwal->hari ?? 'Tidak ada data',
                        'jam_mulai' => $jadwal->jam_mulai ?? 'Tidak ada data',
                        'jam_selesai' => $jadwal->jam_selesai ?? 'Tidak ada data',
                        'kode_matakuliah' => optional($jadwal->kurikulum->mataKuliah)->matakuliah_id ?? null,
                        'nama_matakuliah' => optional($jadwal->kurikulum->mataKuliah)->nama ?? 'Tidak ada data',
                        'program_studi' => $jadwal->kurikulum->programStudi?->nama ?? 'Tidak ada data',
                        'jenis_kelas' => $jadwal->jenis_kelas ?? 'Tidak ada data',
                        'ruangan' => optional($jadwal->ruangan)->nama ?? 'Tidak ada data',
                        'dosen' => $jadwal->kurikulum->dosenToMatakuliah->map(function ($dosenToMatakuliah) {
                            return [
                                'id' => optional($dosenToMatakuliah->dosen)->dosen_id ?? null,
                                'nama' => optional($dosenToMatakuliah->dosen)->nama ?? 'Tidak ada data',
                                'jenis_dosen' => $dosenToMatakuliah->jenis_dosen ?? 'tidak diketahui',
                                'jenis_kelas' => $dosenToMatakuliah->jenis_kelas ?? 'tidak diketahui',
                            ];
                        })->filter(function ($dosen) {
                            return strtolower((string) $dosen['jenis_dosen']) === 'teori';
                        })->unique('id')->values(),
                    ];
                });
            });

        // Jika ada query pencarian, filter data berdasarkan nama mata kuliah atau nama dosen
        if (! empty($query)) {
            $jadwalList = $jadwalList->map(function ($jadwalPerHari) use ($query) {
                return $jadwalPerHari->filter(function ($item) use ($query) {
                    return stripos($item['nama_matakuliah'], $query) !== false ||
                        collect($item['dosen'])->contains(function ($dosen) use ($query) {
                            return stripos($dosen['nama'], $query) !== false;
                        });
                });
            })->filter(function ($jadwalPerHari) {
                return $jadwalPerHari->isNotEmpty();
            });
        }

        return view('dosen.jadwal.partial_list', compact('jadwalList'));
    }

    public function PraktikIndex()
    {
        // Ambil dosen yang sedang login dari guard 'dosen'
        $dosen = auth('dosen')->user();

        // Pastikan ada dosen yang login
        if (! $dosen) {
            return redirect()->route('login')->with('error', 'Silakan login sebagai dosen!');
        }
        $activeTA = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }
        // Ambil jadwal kuliah berdasarkan kurikulum yang diajar oleh dosen
        $jadwalList = JadwalPraktik::whereHas('kurikulum', function ($query) use ($activeTA, $dosen) {
            $query->where('ta_id', $activeTA->ta_id)
                ->where('jurusan_id', $dosen->jurusan_id);
        })
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->dosen_id)
                    ->where('jenis_dosen', 'praktik'); // Periksa jenis_dosen praktik
            })
            ->with(['kurikulum.mataKuliah', 'kurikulum.dosenToMatakuliah.dosen'])
            ->get()
            ->groupBy(function ($jadwal) {
                return $jadwal->hari ?? 'Tidak Ada Hari'; // Kelompokkan berdasarkan hari
            })
            ->map(function ($jadwalPerHari) {
                return $jadwalPerHari->map(function ($jadwal) {
                    return [
                        'jadwal_praktik_id' => $jadwal->id,
                        'hari' => $jadwal->hari ?? 'Tidak ada data',
                        'jam_mulai' => $jadwal->jam_mulai ?? 'Tidak ada data',
                        'jam_selesai' => $jadwal->jam_selesai ?? 'Tidak ada data',
                        'kode_matakuliah' => $jadwal->kurikulum->mataKuliah->matakuliah_id ?? null,
                        'nama_matakuliah' => $jadwal->kurikulum->mataKuliah->nama ?? 'Tidak ada data',
                        'semester_matkul' => $jadwal->kurikulum->mataKuliah->smt ?? 'Tidak ada data',
                        'jenis_kelas' => $jadwal->jenis_kelas ?? 'Tidak ada data',
                        'ruangan' => $jadwal->ruangan->nama ?? 'Tidak ada data',
                        'dosen' => $jadwal->kurikulum->dosenToMatakuliah->map(function ($dosenToMatakuliah) {
                            return [
                                'id' => $dosenToMatakuliah->dosen->dosen_id ?? null,
                                'nama' => $dosenToMatakuliah->dosen->nama ?? 'Tidak ada data',
                                'jenis_dosen' => $dosenToMatakuliah->jenis_dosen ?? 'tidak diketahui',
                                'jenis_kelas' => $dosenToMatakuliah->jenis_kelas ?? 'tidak diketahui',
                            ];
                        })->filter(function ($dosen) {
                            return $dosen['jenis_dosen'] === 'praktik'; // Hanya ambil dosen praktik
                        })->unique('id')->values(),
                    ];
                });
            });

        return view('dosen.jadwal-praktik.index', compact('jadwalList', 'activeTA'));
    }

    public function searchPraktik(Request $request)
    {
        $query = $request->search;
        $dosen = auth('dosen')->user();
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $jadwalList = JadwalPraktik::whereHas('kurikulum', function ($q) use ($activeTA, $dosen) {
            $q->where('ta_id', $activeTA->ta_id)
                ->where('jurusan_id', $dosen->jurusan_id);
        })
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->dosen_id)
                    ->where('jenis_dosen', 'praktik'); // Periksa jenis_dosen praktik
            })
            ->with(['kurikulum.mataKuliah', 'kurikulum.dosenToMatakuliah.dosen', 'ruangan'])
            ->get()
            ->groupBy(function ($jadwal) {
                return $jadwal->hari ?? 'Tidak Ada Hari';
            })
            ->map(function ($jadwalPerHari) {
                return $jadwalPerHari->map(function ($jadwal) {
                    return [
                        'jadwal_praktik_id' => $jadwal->id,
                        'hari' => $jadwal->hari ?? 'Tidak ada data',
                        'jam_mulai' => $jadwal->jam_mulai ?? 'Tidak ada data',
                        'jam_selesai' => $jadwal->jam_selesai ?? 'Tidak ada data',
                        'kode_matakuliah' => optional($jadwal->kurikulum->mataKuliah)->matakuliah_id ?? null,
                        'nama_matakuliah' => optional($jadwal->kurikulum->mataKuliah)->nama ?? 'Tidak ada data',
                        'jenis_kelas' => $jadwal->jenis_kelas ?? 'Tidak ada data',
                        'ruangan' => optional($jadwal->ruangan)->nama ?? 'Tidak ada data',
                        'dosen' => $jadwal->kurikulum->dosenToMatakuliah->map(function ($dosenToMatakuliah) {
                            return [
                                'id' => optional($dosenToMatakuliah->dosen)->dosen_id ?? null,
                                'nama' => optional($dosenToMatakuliah->dosen)->nama ?? 'Tidak ada data',
                                'jenis_dosen' => $dosenToMatakuliah->jenis_dosen ?? 'tidak diketahui',
                                'jenis_kelas' => $dosenToMatakuliah->jenis_kelas ?? 'tidak diketahui',
                            ];
                        })->filter(function ($dosen) {
                            return $dosen['jenis_dosen'] === 'praktik'; // Hanya ambil dosen praktik
                        })->unique('id')->values(),
                    ];
                });
            });

        // Jika ada query pencarian, filter data berdasarkan nama mata kuliah atau nama dosen
        if (! empty($query)) {
            $jadwalList = $jadwalList->map(function ($jadwalPerHari) use ($query) {
                return $jadwalPerHari->filter(function ($item) use ($query) {
                    return stripos($item['nama_matakuliah'], $query) !== false ||
                        collect($item['dosen'])->contains(function ($dosen) use ($query) {
                            return stripos($dosen['nama'], $query) !== false;
                        });
                });
            })->filter(function ($jadwalPerHari) {
                return $jadwalPerHari->isNotEmpty();
            });
        }

        return view('dosen.jadwal-praktik.partial_list', compact('jadwalList'));
    }

    public function indexAbsensi()
    {
        // Ambil dosen yang sedang login dari guard 'dosen'
        $dosen = auth('dosen')->user();

        // Pastikan ada dosen yang login
        if (! $dosen) {
            return redirect()->route('login')->with('error', 'Silakan login sebagai dosen!');
        }
        $activeTA = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }
        // Ambil jadwal kuliah berdasarkan kurikulum yang diajar oleh dosen
        $dosen = auth('dosen')->user();
        $absensiList = Jadwal::whereHas('kurikulum', function ($query) use ($activeTA, $dosen) {
            $query->where('ta_id', $activeTA->ta_id)
                ->where('jurusan_id', $dosen->jurusan_id);
        })
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->dosen_id);
            })
        // ->where('jenis_kelas', $dosen->jenis_kelas)
            ->with(['kurikulum.mataKuliah', 'kurikulum.dosenToMatakuliah.dosen'])
            ->get()
            ->groupBy(function ($jadwal) {
                return $jadwal->kurikulum->mataKuliah->smt ?? 'Tidak Ada Semester';
            })
            ->map(function ($jadwalPerSemester) {
                return $jadwalPerSemester->map(function ($jadwal) {
                    return [
                        'jadwal_id' => $jadwal->id,
                        'hari' => $jadwal->hari ?? 'Tidak ada data',
                        'jam_mulai' => $jadwal->jam_mulai ?? 'Tidak ada data',
                        'jam_selesai' => $jadwal->jam_selesai ?? 'Tidak ada data',
                        'semester' => $jadwal->kurikulum->mataKuliah->smt ?? 'Tidak Ada Semester',
                        'kode_matakuliah' => $jadwal->kurikulum->mataKuliah->matakuliah_id ?? null,
                        'nama_matakuliah' => $jadwal->kurikulum->mataKuliah->nama ?? 'Tidak ada data',
                        'ruangan' => $jadwal->ruangan->nama ?? 'Tidak ada data',
                        'jenis_kelas' => $jadwal->jenis_kelas ?? 'Tidak ada data',
                        'dosen' => $jadwal->kurikulum->dosenToMatakuliah->map(function ($dosenToMatakuliah) {
                            return [
                                'id' => $dosenToMatakuliah->dosen->dosen_id ?? null,
                                'nama' => $dosenToMatakuliah->dosen->nama ?? 'Tidak ada data',
                                'jenis_dosen' => $dosenToMatakuliah->jenis_dosen ?? 'tidak diketahui',
                            ];
                        })->unique('id')->values(),
                    ];
                });
            });

        return view('dosen.absensi.index', compact('absensiList', 'activeTA'));
    }

    public function getFilteredAbsensi(Request $request)
    {
        $dosen = auth('dosen')->user();
        abort_unless($dosen, 401);

        $activeTA = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);
        if (! $activeTA) {
            return response()->json([
                'html' => view('dosen.absensi.partial_list', ['absensiList' => collect()])->render(),
                'statistik' => ['total' => 0, 'reguler' => 0, 'karyawan' => 0],
            ]);
        }

        $query = Jadwal::query()
            ->where('ta_id', $activeTA->ta_id)
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->dosen_id)
                    ->whereRaw('LOWER(jenis_dosen) = ?', ['teori']);
            })
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
                'kurikulum.dosenToMatakuliah' => function ($query) {
                    $query->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])->with('dosen');
                },
                'ruangan',
            ])
            ->withCount('pertemuan');

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($query) use ($search) {
                $query->whereHas('kurikulum.mataKuliah', function ($query) use ($search) {
                    $query->where('nama', 'like', '%'.$search.'%')
                        ->orWhere('matakuliah_id', 'like', '%'.$search.'%');
                })->orWhereHas('kurikulum.dosenToMatakuliah.dosen', function ($query) use ($search) {
                    $query->where('nama', 'like', '%'.$search.'%');
                });
            });
        }

        if ($request->filled('jenis_kelas') && $request->jenis_kelas !== 'semua') {
            $query->whereRaw('LOWER(jenis_kelas) = ?', [strtolower($request->jenis_kelas)]);
        }

        $jadwal = $query->orderBy('hari')->orderBy('jam_mulai')->get();
        $statistik = [
            'total' => $jadwal->count(),
            'reguler' => $jadwal->filter(fn ($item) => strtolower((string) $item->jenis_kelas) === 'reguler')->count(),
            'karyawan' => $jadwal->filter(fn ($item) => strtolower((string) $item->jenis_kelas) === 'karyawan')->count(),
        ];

        $absensiList = $jadwal
            ->groupBy(fn ($item) => $item->kurikulum?->mataKuliah?->smt ?? 'Tidak Ada Semester')
            ->map(function ($items) {
                return $items->map(function ($jadwal) {
                    return [
                        'jadwal_id' => $jadwal->id,
                        'hari' => $jadwal->hari ?? '-',
                        'jam_mulai' => $jadwal->jam_mulai ?? '-',
                        'jam_selesai' => $jadwal->jam_selesai ?? '-',
                        'semester' => $jadwal->kurikulum?->mataKuliah?->smt ?? '-',
                        'kode_matakuliah' => $jadwal->kurikulum?->mataKuliah?->matakuliah_id,
                        'nama_matakuliah' => $jadwal->kurikulum?->mataKuliah?->nama ?? '-',
                        'program_studi' => $jadwal->kurikulum?->programStudi?->nama ?? '-',
                        'sks' => $jadwal->kurikulum?->mataKuliah?->sks ?? 0,
                        'ruangan' => $jadwal->ruangan?->nama ?? '-',
                        'jenis_kelas' => strtolower((string) $jadwal->jenis_kelas),
                        'jumlah_pertemuan' => (int) $jadwal->pertemuan_count,
                        'dosen' => $jadwal->kurikulum?->dosenToMatakuliah
                            ?->map(fn ($assignment) => [
                                'id' => $assignment->dosen?->dosen_id,
                                'nama' => $assignment->dosen?->nama ?? '-',
                                'jenis_dosen' => $assignment->jenis_dosen,
                            ])->filter(fn ($item) => $item['id'])->unique('id')->values() ?? collect(),
                    ];
                });
            })
            ->sortKeysUsing(function ($semesterA, $semesterB) {
                if (is_numeric($semesterA) && is_numeric($semesterB)) {
                    return (int) $semesterA <=> (int) $semesterB;
                }

                return strcmp((string) $semesterA, (string) $semesterB);
            });

        return response()->json([
            'html' => view('dosen.absensi.partial_list', compact('absensiList', 'activeTA'))->render(),
            'statistik' => $statistik,
        ]);
    }

    public function searchAbsen(Request $request)
    {
        $query = $request->search;
        $dosen = auth('dosen')->user();
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $absensiList = Jadwal::whereHas('kurikulum', function ($q) use ($activeTA, $dosen) {
            $q->where('ta_id', $activeTA->ta_id)
                ->where('jurusan_id', $dosen->jurusan_id);

        })
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->dosen_id);
            })
            ->with(['kurikulum.mataKuliah', 'kurikulum.dosenToMatakuliah.dosen', 'ruangan'])
            ->get()
            ->groupBy(function ($jadwal) {
                return optional($jadwal->kurikulum->mataKuliah)->smt ?? 'Tidak Ada Semester';
            })
            ->map(function ($jadwalPerSemester) {
                return $jadwalPerSemester->map(function ($jadwal) {
                    return [
                        'jadwal_id' => $jadwal->id,
                        'hari' => $jadwal->hari ?? 'Tidak ada data',
                        'jam_mulai' => $jadwal->jam_mulai ?? 'Tidak ada data',
                        'jam_selesai' => $jadwal->jam_selesai ?? 'Tidak ada data',
                        'semester' => optional($jadwal->kurikulum->mataKuliah)->smt ?? 'Tidak Ada Semester',
                        'kode_matakuliah' => optional($jadwal->kurikulum->mataKuliah)->matakuliah_id ?? null,
                        'nama_matakuliah' => optional($jadwal->kurikulum->mataKuliah)->nama ?? 'Tidak ada data',
                        'ruangan' => optional($jadwal->ruangan)->nama ?? 'Tidak ada data',
                        'jenis_kelas' => $jadwal->jenis_kelas ?? 'Tidak ada data',
                        'jenis_kelas' => $jadwal->jenis_kelas ?? 'Tidak ada data',
                        'dosen' => optional($jadwal->kurikulum->dosenToMatakuliah)->map(function ($dosenToMatakuliah) {
                            return [
                                'id' => optional($dosenToMatakuliah->dosen)->dosen_id ?? null,
                                'nama' => optional($dosenToMatakuliah->dosen)->nama ?? 'Tidak ada data',
                                'jenis_dosen' => $dosenToMatakuliah->jenis_dosen ?? 'tidak diketahui',
                            ];
                        })->unique('id')->values() ?? [],
                    ];
                });
            });

        if ($query) {
            $absensiList = $absensiList->map(function ($jadwalPerSemester) use ($query) {
                return $jadwalPerSemester->filter(function ($item) use ($query) {
                    return stripos($item['nama_matakuliah'], $query) !== false ||
                        collect($item['dosen'])->contains(function ($dosen) use ($query) {
                            return stripos($dosen['nama'], $query) !== false;
                        });
                });
            })->filter(function ($jadwalPerSemester) {
                return $jadwalPerSemester->isNotEmpty();
            });
        }

        return view('dosen.absensi.partial_list', compact('absensiList'));
    }

    public function storePertemuan(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,id',
            'tanggal_pertemuan' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'metode_pbm' => 'required|in:online,offline',
            'topik' => 'required|string|max:255',
            'sub_topik' => 'required|string|max:255',
        ]);

        $dosen = auth('dosen')->user();
        $jadwalDiampu = Jadwal::whereKey($request->jadwal_id)
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->dosen_id)
                    ->whereRaw('LOWER(jenis_dosen) = ?', ['teori']);
            })->exists();
        abort_unless($jadwalDiampu, 403, 'Jadwal teori ini tidak diampu oleh Anda.');

        try {
            DB::beginTransaction();
            // Simpan pertemuan baru
            $pertemuan = Pertemuan::create([
                'jadwal_id' => $request->jadwal_id,
                'tanggal_pertemuan' => $request->tanggal_pertemuan,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'metode_pbm' => $request->metode_pbm,
                'topik' => $request->topik,
                'sub_topik' => $request->sub_topik,
                'dosen_id' => auth('dosen')->user()->dosen_id, // Menyimpan dosen_id dari user yang login
            ]);

            // Ambil jadwal beserta relasi ke kurikulum dan matakuliah
            $jadwal = Jadwal::with('kurikulum.matakuliah')->find($request->jadwal_id);

            if (! $jadwal || ! $jadwal->kurikulum || ! $jadwal->kurikulum->matakuliah) {
                DB::rollBack();

                return response()->json(['message' => 'Jadwal atau mata kuliah tidak ditemukan'], 404);
            }

            $matakuliah = $jadwal->kurikulum->matakuliah;
            $semester = $matakuliah->smt;
            $jurusan_id = $matakuliah->jurusan_id;

            // Tarik mahasiswa berdasarkan semester, jurusan, status aktif, dan jenis kelas
            $mahasiswaList = Mahasiswa::where('semester', $semester)
                ->where('jurusan_id', $jurusan_id)
                ->where('status_mhs', 'aktif') // Tambahkan kondisi status_mhs aktif
                ->when($jadwal->jenis_kelas === 'reguler', function ($query) {
                    return $query->whereIn('kelas', ['pagi', 'reguler']);
                })
                ->when($jadwal->jenis_kelas === 'karyawan', function ($query) {
                    return $query->where('kelas', 'karyawan'); // Cocokan dengan kelas yang ada di mahasiswa karyawan
                })
                ->pluck('mahasiswa_id'); // Ambil hanya ID untuk efisiensi

            if ($mahasiswaList->isEmpty()) {
                Log::error('Daftar mahasiswa kosong berdasarkan filter yang diberikan', [
                    'semester' => $semester,
                    'jurusan_id' => $jurusan_id,
                    'jenis_kelas' => $jadwal->jenis_kelas,
                ]);

                DB::rollBack();

                return response()->json([
                    'message' => 'Tidak ada mahasiswa yang cocok',
                    'error' => 'Daftar mahasiswa kosong berdasarkan filter yang diberikan',
                    'details' => [
                        'semester' => $semester,
                        'jurusan_id' => $jurusan_id,
                        'jenis_kelas' => $jadwal->jenis_kelas,
                    ],
                ], 404);
            }

            // Data absensi yang akan dimasukkan
            $absensiData = $mahasiswaList->map(function ($mahasiswa_id) use ($pertemuan, $request) {
                return [
                    'pertemuan_id' => $pertemuan->pertemuan_id,
                    'jadwal_id' => $request->jadwal_id,
                    'mahasiswa_id' => $mahasiswa_id,
                    'status' => 'tidak hadir',
                    'keterangan' => null,
                    'tanggal' => now('Asia/Jakarta'),
                ];
            })->toArray();

            // Insert batch absensi
            Absensi::insert($absensiData);

            DB::commit();

            activity_log('buat_pertemuan', 'Dosen membuat pertemuan teori: '.$request->topik);

            return response()->json([
                'message' => 'Pertemuan dan absensi berhasil disimpan',
                'pertemuan_id' => $pertemuan->pertemuan_id,
                'jadwal_id' => $request->jadwal_id,
                'redirect_url' => route('dosen.absensi.create', $pertemuan->pertemuan_id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan pertemuan dan absensi: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan pertemuan dan absensi',
                'error' => $e->getMessage(), // Tampilkan pesan error
                'line' => $e->getLine(), // Tampilkan baris kode yang error
                'file' => $e->getFile(), // Tampilkan file yang error
            ], 500);
        }
    }

    public function listPertemuan($jadwal_id)
    {
        $dosen = auth('dosen')->user();
        $jadwalDiampu = Jadwal::whereKey($jadwal_id)
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->dosen_id)
                    ->whereRaw('LOWER(jenis_dosen) = ?', ['teori']);
            })->exists();
        abort_unless($jadwalDiampu, 403);

        $pertemuan = Pertemuan::where('jadwal_id', $jadwal_id)->orderBy('tanggal_pertemuan', 'desc')->get();

        return response()->json($pertemuan);
    }

    public function lihat($pertemuan_id)
    {
        // Ambil data pertemuan beserta relasi lengkap
        $pertemuan = Pertemuan::with('jadwal.kurikulum.mataKuliah')
            ->where('dosen_id', auth('dosen')->id())
            ->findOrFail($pertemuan_id);

        // Ambil data absensi berdasarkan pertemuan
        $absensi = Absensi::where('pertemuan_id', $pertemuan_id)
            ->with('mahasiswa') // Pastikan ada relasi ke Mahasiswa
            ->get();

        $mahasiswaAbsensi = Absensi::where('pertemuan_id', $pertemuan_id)->pluck('mahasiswa_id');
        // Ambil semua mahasiswa berdasarkan jurusan yang belum ada di absensi
        $mahasiswaTambahan = Mahasiswa::where('jurusan_id', $pertemuan->jadwal->kurikulum->jurusan_id)
            ->whereNotIn('mahasiswa_id', $mahasiswaAbsensi)
            ->get();

        return view('dosen.absensi.absen', compact('pertemuan', 'absensi', 'mahasiswaTambahan'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'pertemuan_id' => 'required|exists:pertemuan,pertemuan_id',
            'status' => 'required|array',
            'status.*' => 'in:hadir,izin,sakit,tidak hadir',
            'keterangan' => 'nullable|array',
        ]);

        $pertemuan = Pertemuan::where('dosen_id', auth('dosen')->id())
            ->findOrFail($request->pertemuan_id);

        try {
            DB::beginTransaction();

            // Looping dengan collect() untuk optimalisasi
            collect($request->status)->each(function ($status, $mahasiswa_id) use ($request, $pertemuan) {
                Absensi::updateOrCreate(
                    [
                        'pertemuan_id' => $request->pertemuan_id,
                        'mahasiswa_id' => $mahasiswa_id,
                    ],
                    [
                        'jadwal_id' => $pertemuan->jadwal_id,
                        'tanggal' => $pertemuan->tanggal_pertemuan,
                        'status' => $status,
                        'keterangan' => $request->keterangan[$mahasiswa_id] ?? null,
                    ]
                );
            });

            DB::commit();
            activity_log('simpan_absensi', 'Dosen menyimpan absensi pertemuan ID: '.$request->pertemuan_id);
            Alert::success('Berhasil', 'Absensi berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan absensi: '.$e->getMessage());
            Alert::error('Gagal', 'Terjadi kesalahan saat menyimpan absensi.');
        }

        return redirect()->back();
    }

    public function storePertemuanPraktik(Request $request)
    {
        $request->validate([
            // 'jadwal_id' => 'required|exists:jadwal,id',
            'tanggal_pertemuan' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'metode_pbm' => 'required|in:online,offline',
            'topik' => 'required|string|max:255',
            'sub_topik' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Simpan pertemuan baru
            $pertemuan = PertemuanPraktik::create([
                'jadwal_praktik_id' => $request->jadwal_praktik_id,
                'tanggal_pertemuan' => $request->tanggal_pertemuan,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'metode_pbm' => $request->metode_pbm,
                'topik' => $request->topik,
                'sub_topik' => $request->sub_topik,
                'dosen_id' => auth('dosen')->user()->dosen_id, // Menyimpan dosen_id dari user yang login
            ]);

            // Ambil jadwal beserta relasi ke kurikulum dan matakuliah
            $jadwal = JadwalPraktik::with('kurikulum.matakuliah')->find($request->jadwal_praktik_id);

            if (! $jadwal || ! $jadwal->kurikulum || ! $jadwal->kurikulum->matakuliah) {
                return response()->json(['message' => 'Jadwal atau mata kuliah tidak ditemukan'], 404);
            }

            $matakuliah = $jadwal->kurikulum->matakuliah;
            $semester = $matakuliah->smt;
            $jurusan_id = $matakuliah->jurusan_id;

            // Tarik mahasiswa berdasarkan semester, jurusan, status aktif, dan jenis kelas
            $mahasiswaList = Mahasiswa::where('semester', $semester)
                ->where('jurusan_id', $jurusan_id)
                ->where('status_mhs', 'aktif') // Tambahkan kondisi status_mhs aktif
                ->when($jadwal->jenis_kelas === 'reguler', function ($query) {
                    return $query->where('kelas', 'pagi'); // Cocokan dengan kelas yang ada di mahasiswa pagi
                })
                ->when($jadwal->jenis_kelas === 'karyawan', function ($query) {
                    return $query->where('kelas', 'karyawan'); // Cocokan dengan kelas yang ada di mahasiswa karyawan
                })
                ->pluck('mahasiswa_id'); // Ambil hanya ID untuk efisiensi
            if ($mahasiswaList->isEmpty()) {
                Log::error('Daftar mahasiswa kosong berdasarkan filter yang diberikan', [
                    'semester' => $semester,
                    'jurusan_id' => $jurusan_id,
                    'jenis_kelas' => $jadwal->jenis_kelas,
                ]);

                return response()->json([
                    'message' => 'Tidak ada mahasiswa yang cocok',
                    'error' => 'Daftar mahasiswa kosong berdasarkan filter yang diberikan',
                    'details' => [
                        'semester' => $semester,
                        'jurusan_id' => $jurusan_id,
                        'jenis_kelas' => $jadwal->jenis_kelas,
                    ],
                ], 404);
            }
            // Data absensi yang akan dimasukkan
            $absensiData = $mahasiswaList->map(function ($mahasiswa_id) use ($pertemuan, $request) {
                return [
                    'pertemuan_praktik_id' => $pertemuan->pertemuan_praktik_id,
                    'jadwal_praktik_id' => $request->jadwal_praktik_id,
                    'mahasiswa_id' => $mahasiswa_id,
                    'status' => 'tidak hadir',
                    'keterangan' => null,
                    'tanggal' => now('Asia/Jakarta'),
                ];
            })->toArray();

            // Insert batch absensi
            AbsensiPraktik::insert($absensiData);

            DB::commit();

            activity_log('buat_pertemuan_praktik', 'Dosen membuat pertemuan praktik: '.$request->topik);

            return response()->json([
                'message' => 'Pertemuan dan absensi berhasil disimpan',
                'pertemuan_praktik_id' => $pertemuan->pertemuan_praktik_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan pertemuan dan absensi: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan pertemuan dan absensi',
                'error' => $e->getMessage(), // Tampilkan pesan error
                'line' => $e->getLine(), // Tampilkan baris kode yang error
                'file' => $e->getFile(), // Tampilkan file yang error
            ], 500);
        }
    }

    public function listPertemuanPraktik($jadwal_praktik_id)
    {
        $pertemuan = PertemuanPraktik::where('jadwal_praktik_id', $jadwal_praktik_id)->orderBy('tanggal_pertemuan', 'desc')->get();

        return response()->json($pertemuan);
    }

    public function lihatPraktik($pertemuan_praktik_id)
    {
        // Ambil data pertemuan beserta relasi lengkap
        $pertemuan = PertemuanPraktik::with('jadwal.kurikulum.mataKuliah')->findOrFail($pertemuan_praktik_id);
        // Ambil data absensi berdasarkan pertemuan
        $absensi = AbsensiPraktik::where('pertemuan_praktik_id', $pertemuan_praktik_id)
            ->with('mahasiswa') // Pastikan ada relasi ke Mahasiswa
            ->get();

        $mahasiswaAbsensi = AbsensiPraktik::where('pertemuan_praktik_id', $pertemuan_praktik_id)->pluck('mahasiswa_id');
        // Ambil semua mahasiswa berdasarkan jurusan yang belum ada di absensi
        $mahasiswaTambahan = Mahasiswa::where('jurusan_id', $pertemuan->jadwal->kurikulum->jurusan_id)
            ->whereNotIn('mahasiswa_id', $mahasiswaAbsensi)
            ->get();

        return view('dosen.absensi.absensi-praktik', compact('pertemuan', 'absensi', 'mahasiswaTambahan'));
    }

    public function storePraktik(Request $request)
    {
        // Validasi input
        $request->validate([
            'pertemuan_praktik_id' => 'required|exists:pertemuan_praktik,pertemuan_praktik_id',
            'status' => 'required|array',
            'status.*' => 'in:hadir,izin,sakit,tidak hadir',
            'keterangan' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // Looping dengan collect() untuk optimalisasi
            collect($request->status)->each(function ($status, $mahasiswa_id) use ($request) {
                AbsensiPraktik::updateOrCreate(
                    [
                        'pertemuan_praktik_id' => $request->pertemuan_praktik_id,
                        'mahasiswa_id' => $mahasiswa_id,
                    ],
                    [
                        'status' => $status,
                        'keterangan' => $request->keterangan[$mahasiswa_id] ?? null,
                    ]
                );
            });

            DB::commit();
            activity_log('simpan_absensi_praktik', 'Dosen menyimpan absensi praktik pertemuan ID: '.$request->pertemuan_praktik_id);
            Alert::success('Berhasil', 'Absensi berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan absensi: '.$e->getMessage());
            Alert::error('Gagal', 'Terjadi kesalahan saat menyimpan absensi.');
        }

        return redirect()->back();
    }
}
