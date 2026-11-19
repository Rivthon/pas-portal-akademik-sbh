@extends('layouts.dosen')
@section('title', 'Rekap Absensi Dosen')
@section('content')
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#1e3c72,#2a5298)">
        <h4 class="text-white mb-1"><i class="bx bx-calendar-check me-2"></i>Rekap Absensi Seluruh Dosen</h4>
        <p class="mb-0 text-white-50">Lihat dan unduh rekap absensi dosen pada program studi yang Anda kelola.</p>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('dosen.sekprodi.absensi.index') }}" class="row g-3 align-items-end">
            @if($managedPrograms->count() > 1)
                <div class="col-md-3">
                    <label class="form-label">Program Studi</label>
                    <select name="program_studi_id" class="form-select">
                        @foreach($managedPrograms as $program)
                            <option value="{{ $program->jurusan_id }}" @selected((string)$selectedProgramId === (string)$program->jurusan_id)>{{ $program->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="program_studi_id" value="{{ $selectedProgramId }}">
            @endif
            <div class="col-md-2">
                <label class="form-label">Tahun Akademik</label>
                <select name="ta_id" class="form-select">
                    @foreach($tahunAkademik as $ta)
                        <option value="{{ $ta->ta_id }}" @selected((int)$selectedTaId === (int)$ta->ta_id)>{{ $ta->nama }} - {{ $ta->semester }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Jenis Perkuliahan</label>
                <select name="jenis" class="form-select">
                    <option value="teori" @selected($selectedJenis === 'teori')>Teori</option>
                    <option value="praktik" @selected($selectedJenis === 'praktik')>Praktik</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dosen</label>
                <select name="dosen_id" class="form-select">
                    <option value="">Semua Dosen</option>
                    @foreach($dosenPengajar as $dosen)
                        <option value="{{ $dosen->dosen_id }}" @selected((int)$selectedDosenId === (int)$dosen->dosen_id)>{{ $dosen->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Pencarian</label>
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Mata kuliah/dosen">
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-primary"><i class="bx bx-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Absensi {{ ucfirst($selectedJenis) }}</h5>
        <span class="badge bg-label-primary">{{ $jadwals->total() }} Jadwal</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Mata Kuliah</th><th>Dosen Pengajar</th><th>Kelas</th><th>Jadwal</th><th class="text-center">Pertemuan</th><th class="text-center">Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($jadwals as $jadwal)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $jadwal->kurikulum?->mataKuliah?->nama ?? '-' }}</div>
                            <small class="text-muted">{{ $jadwal->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }} · Semester {{ $jadwal->kurikulum?->mataKuliah?->smt ?? '-' }}</small>
                        </td>
                        <td>{{ $jadwal->nama_pengajar_sekprodi }}</td>
                        <td><span class="badge bg-label-info">{{ jenis_kelas_label($jadwal->jenis_kelas ?? 'Reguler') }}</span></td>
                        <td><div>{{ $jadwal->hari ?: '-' }}</div><small class="text-muted">{{ $jadwal->jam_mulai ? substr($jadwal->jam_mulai,0,5) : '--:--' }}–{{ $jadwal->jam_selesai ? substr($jadwal->jam_selesai,0,5) : '--:--' }}</small></td>
                        <td class="text-center"><span class="badge bg-label-primary">{{ $jadwal->pertemuan_count }}</span></td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('dosen.sekprodi.absensi.show', [$selectedJenis, $jadwal->id]) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-show me-1"></i>Lihat</a>
                            @if($jadwal->pertemuan_count > 0)
                                <a href="{{ route('dosen.sekprodi.absensi.download', [$selectedJenis, $jadwal->id]) }}" class="btn btn-sm btn-primary"><i class="bx bx-download me-1"></i>PDF</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">Belum ada jadwal atau absensi yang sesuai dengan filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($jadwals->hasPages())
        <div class="card-footer bg-white d-flex justify-content-center py-3">
            {{ $jadwals->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
