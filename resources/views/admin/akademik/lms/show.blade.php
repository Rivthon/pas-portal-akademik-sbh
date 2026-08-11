@extends('layouts.master')
@section('title', 'Detail LMS')

@section('content')
@php
    $mk=$jadwal->kurikulum?->mataKuliah; $prodi=$jadwal->kurikulum?->programStudi;
    $dosen=$jadwal->kurikulum?->dosenToMatakuliah?->pluck('dosen.nama')->filter()->unique()->implode(', ');
@endphp
<div class="mb-3"><a href="{{ route('admin.lms.index') }}" class="btn btn-sm btn-label-secondary"><i class="bx bx-arrow-back me-1"></i>Semua Kelas</a></div>
<div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between gap-3"><div><span class="badge bg-label-primary mb-2">MONITORING LMS</span><h3 class="fw-bold mb-1">{{ $mk?->nama ?? '-' }}</h3><p class="text-muted mb-1">{{ $mk?->matakuliah_id }} &bull; {{ $prodi?->nama ?? '-' }} &bull; {{ strtoupper($jadwal->jenis_kelas ?: '-') }}</p><small><i class="bx bx-user me-1"></i>{{ $dosen ?: 'Dosen belum ditentukan' }}</small></div>
    <div class="d-flex flex-wrap gap-2 align-content-start"><span class="badge bg-label-secondary p-2">{{ $totalPeserta }} Mahasiswa</span><span class="badge bg-label-info p-2">{{ $materiList->count() }} Materi</span><span class="badge bg-label-warning p-2">{{ $tugasList->count() }} Tugas</span><span class="badge bg-label-primary p-2">{{ $quizList->count() }} Quiz</span></div></div>
</div></div>

<div class="row g-4 mb-4">
    <div class="col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white"><h5 class="fw-bold mb-0"><i class="bx bx-file text-info me-2"></i>Materi</h5></div><div class="card-body">
        @forelse($materiList as $materi)<div class="border rounded p-3 mb-2"><div class="fw-semibold">{{ $materi->judul }}</div><small class="text-muted d-block mb-2">Pertemuan {{ $nomorPertemuan->get($materi->pertemuan_id,'-') }} &bull; {{ strtoupper($materi->tipe ?: 'FILE') }}</small>
            @if($materi->youtube_url)<a target="_blank" href="{{ str_starts_with($materi->youtube_url,'http')?$materi->youtube_url:'https://'.$materi->youtube_url }}" class="btn btn-sm btn-label-danger"><i class="bx bxl-youtube"></i> Buka</a>@elseif($materi->file)<a target="_blank" href="{{ asset('storage/'.$materi->file) }}" class="btn btn-sm btn-label-primary"><i class="bx bx-show"></i> Lihat</a>@endif
        </div>@empty<p class="text-center text-muted py-4">Belum ada materi.</p>@endforelse
    </div></div></div>
    <div class="col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white"><h5 class="fw-bold mb-0"><i class="bx bx-task text-warning me-2"></i>Tugas</h5></div><div class="card-body">
        @forelse($tugasList as $tugas)<div class="border rounded p-3 mb-2"><div class="d-flex justify-content-between gap-2"><strong>{{ $tugas->judul }}</strong><span class="badge bg-label-primary">{{ $tugas->pengumpulan_count }}/{{ $totalPeserta }}</span></div><small class="text-muted d-block">Pertemuan {{ $nomorPertemuan->get($tugas->pertemuan_id,'-') }}</small><small class="text-muted">Deadline {{ $tugas->deadline?->translatedFormat('d M Y H:i') ?? '-' }}</small></div>
        @empty<p class="text-center text-muted py-4">Belum ada tugas.</p>@endforelse
    </div></div></div>
    <div class="col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white"><h5 class="fw-bold mb-0"><i class="bx bx-question-mark text-primary me-2"></i>Quiz</h5></div><div class="card-body">
        @forelse($quizList as $quiz)<div class="border rounded p-3 mb-2"><div class="d-flex justify-content-between gap-2"><strong>{{ $quiz->judul }}</strong><span class="badge bg-label-success">{{ $quiz->attempts_count }}/{{ $totalPeserta }}</span></div><small class="text-muted d-block">Pertemuan {{ $nomorPertemuan->get($quiz->pertemuan_id,'-') }} &bull; {{ $quiz->soal->count() }} soal</small><small class="text-muted">Deadline {{ $quiz->deadline?->translatedFormat('d M Y H:i') ?? '-' }}</small></div>
        @empty<p class="text-center text-muted py-4">Belum ada quiz.</p>@endforelse
    </div></div></div>
</div>

<div class="card border-0 shadow-sm"><div class="card-header bg-white"><h5 class="fw-bold mb-0"><i class="bx bx-calendar-event me-2 text-primary"></i>Riwayat Pertemuan</h5></div><div class="card-body">
@forelse($jadwal->pertemuan as $pertemuan)
    <div class="border rounded mb-2 overflow-hidden">
        <button class="btn w-100 text-start d-flex justify-content-between align-items-center p-3" data-bs-toggle="collapse" data-bs-target="#adminMeeting{{ $pertemuan->pertemuan_id }}"><span><span class="badge bg-label-primary me-2">{{ $loop->iteration }}</span><strong>{{ $pertemuan->topik ?: 'Topik belum diisi' }}</strong></span><i class="bx bx-chevron-down"></i></button>
        <div class="collapse" id="adminMeeting{{ $pertemuan->pertemuan_id }}"><div class="p-3 pt-0"><small class="text-muted">{{ $pertemuan->tanggal_pertemuan ? \Carbon\Carbon::parse($pertemuan->tanggal_pertemuan)->translatedFormat('d F Y') : '-' }}</small><div class="d-flex gap-2 mt-2"><span class="badge bg-label-info">{{ $pertemuan->materi->count() }} Materi</span><span class="badge bg-label-warning">{{ $pertemuan->tugas->count() }} Tugas</span><span class="badge bg-label-primary">{{ $pertemuan->quiz->count() }} Quiz</span></div></div></div>
    </div>
@empty<div class="text-center text-muted py-5">Belum ada pertemuan pada kelas ini.</div>@endforelse
</div></div>
@endsection
