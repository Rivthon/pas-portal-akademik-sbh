@extends('layouts.master')
@section('title', 'Jadwal Perkuliahan Keseluruhan')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge bg-label-primary mb-3"><i class="bx bx-calendar me-1"></i>Jadwal Terpadu</span>
                    <h3 class="fw-bold text-dark mb-2">Jadwal Perkuliahan Keseluruhan</h3>
                    <p class="text-muted mb-3">Pantau seluruh jadwal teori dan praktik berdasarkan hari, waktu, kelas, dan ruangan pada satu halaman.</p>
                    <span class="badge bg-primary px-3 py-2">{{ $tahunAjaran->nama }} &bull; {{ $tahunAjaran->semester }}</span>
                </div>
                <div class="col-lg-4 text-lg-end text-center">
                    <i class="bx bx-calendar-week text-primary" style="font-size: 7rem; opacity: .16;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-1"><i class="bx bx-filter-alt text-primary me-2"></i>Filter Jadwal</h5>
            <small class="text-muted">Kosongkan filter untuk menampilkan seluruh jadwal pada tahun akademik aktif.</small>
        </div>
        <div class="card-body px-4 pb-4">
            <form method="GET" action="{{ route('admin.jadwal-keseluruhan.index') }}" class="row g-3 align-items-end">
                <div class="col-sm-6 col-xl-3">
                    <label class="form-label fw-semibold">Program Studi</label>
                    <select name="jurusan_id" class="form-select">
                        <option value="">Semua Program Studi</option>
                        @foreach($programStudi as $prodi)
                            <option value="{{ $prodi->jurusan_id }}" @selected((string) ($filters['jurusan_id'] ?? '') === (string) $prodi->jurusan_id)>{{ $prodi->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-xl-2">
                    <label class="form-label fw-semibold">Semester</label>
                    <select name="semester" class="form-select">
                        <option value="">Semua Semester</option>
                        @foreach($semesterOptions as $semester)
                            <option value="{{ $semester }}" @selected((int) ($filters['semester'] ?? 0) === $semester)>Semester {{ $semester }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-xl-2">
                    <label class="form-label fw-semibold">Kelas</label>
                    <select name="kelas" class="form-select">
                        <option value="">Semua Kelas</option>
                        <option value="reguler" @selected(($filters['kelas'] ?? '') === 'reguler')>Reguler A</option>
                        <option value="karyawan" @selected(($filters['kelas'] ?? '') === 'karyawan')>Reguler B</option>
                    </select>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <label class="form-label fw-semibold">Ruangan</label>
                    <select name="ruangan_id" class="form-select">
                        <option value="">Semua Ruangan</option>
                        @foreach($ruangan as $ruang)
                            <option value="{{ $ruang->ruangan_id }}" @selected((string) ($filters['ruangan_id'] ?? '') === (string) $ruang->ruangan_id)>{{ $ruang->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" type="submit"><i class="bx bx-search me-1"></i>Terapkan</button>
                    <a href="{{ route('admin.jadwal-keseluruhan.index') }}" class="btn btn-outline-secondary" title="Reset filter"><i class="bx bx-reset"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Total Jadwal</small><h3 class="fw-bold text-primary mb-0">{{ $statistik['total'] }}</h3></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Teori</small><h3 class="fw-bold text-info mb-0">{{ $statistik['teori'] }}</h3></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Praktik</small><h3 class="fw-bold text-success mb-0">{{ $statistik['praktik'] }}</h3></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Hari Belum Diatur</small><h3 class="fw-bold text-warning mb-0">{{ $statistik['belum_diatur'] }}</h3></div></div></div>
    </div>

    @if($statistik['total'] === 0)
        <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bx bx-calendar-x text-muted mb-3" style="font-size: 4rem;"></i><h5>Jadwal tidak ditemukan</h5><p class="text-muted mb-0">Coba ubah atau kosongkan filter yang digunakan.</p></div></div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach($jadwalPerHari as $namaHari => $items)
                <div class="card border-0 shadow-sm overflow-hidden">
                    <button class="card-header bg-white border-0 p-4 d-flex align-items-center justify-content-between text-start" type="button" data-bs-toggle="collapse" data-bs-target="#jadwal-{{ strtolower($namaHari) }}" aria-expanded="true">
                        <span><span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-label-primary me-2" style="width: 38px; height: 38px;"><i class="bx bx-calendar-event"></i></span><span class="fw-bold fs-5 text-dark">{{ $namaHari }}</span></span>
                        <span class="d-flex align-items-center gap-2"><span class="badge bg-label-primary">{{ $items->count() }} jadwal</span><i class="bx bx-chevron-down fs-4"></i></span>
                    </button>
                    <div class="collapse show" id="jadwal-{{ strtolower($namaHari) }}">
                        @if($items->isEmpty())
                            <div class="border-top text-center text-muted py-4"><i class="bx bx-calendar-x me-1"></i>Tidak ada perkuliahan pada hari {{ $namaHari }}.</div>
                        @else
                            @include('admin.akademik.jadwal-keseluruhan._table', ['jadwalItems' => $items])
                        @endif
                    </div>
                </div>
            @endforeach

            @if($belumDiatur->isNotEmpty())
                <div class="card border border-warning shadow-sm overflow-hidden">
                    <div class="card-header bg-label-warning border-0 p-4">
                        <h5 class="fw-bold text-warning mb-1"><i class="bx bx-error-circle me-2"></i>Hari Belum Diatur</h5>
                        <small>Terdapat {{ $belumDiatur->count() }} jadwal yang belum memiliki hari perkuliahan.</small>
                    </div>
                    @include('admin.akademik.jadwal-keseluruhan._table', ['jadwalItems' => $belumDiatur])
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
