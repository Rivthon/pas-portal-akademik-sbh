@extends('layouts.dosen')

@section('title', 'Kurikulum KRS Mahasiswa')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-4 text-white position-relative" style="background: linear-gradient(135deg, #1e3c72, #2a5298);">
            <div class="position-relative" style="z-index: 1;">
                <span class="badge bg-white text-primary mb-3">
                    <i class="bx bx-calendar me-1"></i>Tahun Akademik {{ $activeTa->nama ?? 'Belum Aktif' }}
                </span>
                <h3 class="text-white fw-bold mb-2">
                    <i class="bx bx-book-content me-2"></i>Kurikulum KRS Mahasiswa
                </h3>
                <p class="text-white-50 mb-0">
                    Referensi mata kuliah yang perlu diambil mahasiswa, disusun per semester dan jenis kelas.
                </p>
            </div>
            <i class="bx bx-library position-absolute text-white"
                style="right: 2rem; bottom: -1rem; font-size: 9rem; opacity: .08;"></i>
        </div>
    </div>

    @unless($activeTa)
        <div class="alert alert-warning border-0 shadow-sm">
            <i class="bx bx-error-circle me-2"></i>Belum ada Tahun Akademik aktif.
        </div>
    @endunless

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom pt-4">
            <h5 class="fw-bold mb-1"><i class="bx bx-filter-alt text-primary me-2"></i>Filter Kurikulum</h5>
            <small class="text-muted">Pilih program studi, kelas, atau semester yang ingin diperiksa.</small>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dosen.kurikulum-krs.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6 col-xl-3">
                    <label for="prodi" class="form-label fw-semibold">Program Studi</label>
                    <select id="prodi" name="prodi" class="form-select">
                        @forelse($programStudiList as $prodi)
                            <option value="{{ $prodi->jurusan_id }}" @selected((string) $prodi->jurusan_id === $selectedProdi)>
                                {{ $prodi->nama }}
                            </option>
                        @empty
                            <option value="">Tidak ada program studi</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="jenis-kelas" class="form-label fw-semibold">Jenis Kelas</label>
                    <select id="jenis-kelas" name="jenis_kelas" class="form-select">
                        <option value="semua" @selected($jenisKelas === 'semua')>Semua Kelas</option>
                        <option value="reguler" @selected($jenisKelas === 'reguler')>Reguler A</option>
                        <option value="karyawan" @selected($jenisKelas === 'karyawan')>Reguler B</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="semester" class="form-label fw-semibold">Semester</label>
                    <select id="semester" name="semester" class="form-select">
                        <option value="">Semua Semester</option>
                        @foreach(range(1, 14) as $nomorSemester)
                            <option value="{{ $nomorSemester }}" @selected($semester === $nomorSemester)>
                                Semester {{ $nomorSemester }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 col-xl-3">
                    <label for="search" class="form-label fw-semibold">Cari Mata Kuliah</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input id="search" type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Kode atau nama mata kuliah">
                    </div>
                </div>
                <div class="col-md-4 col-xl-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bx bx-filter-alt me-1"></i>Terapkan
                    </button>
                    <a href="{{ route('dosen.kurikulum-krs.index') }}" class="btn btn-outline-secondary" title="Reset filter">
                        <i class="bx bx-reset"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['Mata Kuliah', $stats['mata_kuliah'], 'bx-book', 'primary'],
            ['Total SKS', $stats['sks'], 'bx-calculator', 'info'],
            ['Mata Kuliah Wajib', $stats['wajib'], 'bx-check-shield', 'success'],
            ['Mata Kuliah Pilihan', $stats['pilihan'], 'bx-list-plus', 'warning'],
        ] as [$label, $value, $icon, $color])
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar-initial rounded bg-label-{{ $color }} p-3">
                            <i class="bx {{ $icon }} fs-4"></i>
                        </span>
                        <div>
                            <small class="text-muted">{{ $label }}</small>
                            <h4 class="mb-0 fw-bold">{{ $value }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="alert alert-info border-0 shadow-sm">
        <i class="bx bx-info-circle me-2"></i>
        Halaman ini merupakan referensi kurikulum. KRS aktual mahasiswa tetap mengikuti pengajuan dan persetujuan Dosen Pembimbing.
    </div>

    <div class="accordion" id="kurikulumSemesterAccordion">
        @forelse($kurikulumPerSemester as $nomorSemester => $daftarKurikulum)
            @php
                $semesterId = 'semester-kurikulum-'.$nomorSemester;
                $totalSksSemester = $daftarKurikulum->sum(fn ($item) => (int) ($item->mataKuliah?->sks ?? 0));
            @endphp
            <div class="accordion-item border-0 shadow-sm mb-3 rounded overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                        data-bs-toggle="collapse" data-bs-target="#{{ $semesterId }}"
                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $semesterId }}">
                        <span class="badge bg-primary me-3">Semester {{ $nomorSemester ?: '-' }}</span>
                        <span class="fw-bold me-2">{{ $daftarKurikulum->count() }} Mata Kuliah</span>
                        <span class="text-muted small">{{ $totalSksSemester }} SKS</span>
                    </button>
                </h2>
                <div id="{{ $semesterId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                    data-bs-parent="#kurikulumSemesterAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">No.</th>
                                        <th style="width: 130px;">Kode</th>
                                        <th>Mata Kuliah</th>
                                        <th class="text-center" style="width: 80px;">SKS</th>
                                        <th class="text-center">Kategori</th>
                                        <th>Jenis Kelas</th>
                                        <th>PBM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daftarKurikulum as $item)
                                        @php
                                            $mataKuliah = $item->mataKuliah;
                                            $kategori = (int) ($mataKuliah?->kategori_mk ?? -1);
                                        @endphp
                                        <tr>
                                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                            <td><span class="badge bg-label-primary">{{ $mataKuliah?->matakuliah_id ?? '-' }}</span></td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $mataKuliah?->nama ?? '-' }}</div>
                                                <small class="text-muted">Semester {{ $mataKuliah?->smt ?? '-' }}</small>
                                            </td>
                                            <td class="text-center fw-bold">{{ $mataKuliah?->sks ?? 0 }}</td>
                                            <td class="text-center">
                                                @if($kategori === 0)
                                                    <span class="badge bg-label-primary">Wajib</span>
                                                @elseif($kategori === 1)
                                                    <span class="badge bg-label-success">Pilihan</span>
                                                @else
                                                    <span class="badge bg-label-secondary">Belum Diatur</span>
                                                @endif
                                            </td>
                                            <td>
                                                @forelse($item->kelas_tersedia as $kelas)
                                                    <span class="badge bg-label-{{ $kelas === 'reguler' ? 'info' : 'warning' }} text-capitalize">
                                                        {{ $kelas }}
                                                    </span>
                                                @empty
                                                    <span class="badge bg-label-secondary">Belum Diatur</span>
                                                @endforelse
                                            </td>
                                            <td>
                                                @forelse($item->jenis_pbm as $jenis)
                                                    <span class="badge bg-label-{{ $jenis === 'teori' ? 'primary' : 'success' }} text-capitalize">
                                                        {{ $jenis }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted small">Belum ada penugasan</span>
                                                @endforelse
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bx bx-folder-open text-muted" style="font-size: 3.5rem;"></i>
                    <h5 class="mt-3 mb-1">Kurikulum Tidak Ditemukan</h5>
                    <p class="text-muted mb-0">Tidak ada mata kuliah yang sesuai dengan filter pada Tahun Akademik aktif.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
