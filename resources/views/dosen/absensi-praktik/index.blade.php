@extends('layouts.dosen')
@section('title', 'Absensi Praktik')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg,#1e3c72,#2a5298)">
        <div class="card-body p-4 text-white">
            <h4 class="text-white fw-bold mb-2"><i class="bx bx-test-tube me-2"></i>Absensi Praktik</h4>
            <p class="mb-0 text-white-50">Buat pertemuan praktik, lalu catat kehadiran mahasiswa pada kelas yang Anda ampu.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><strong>Data belum dapat disimpan.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @unless($activeTa)
        <div class="alert alert-warning">Belum ada tahun akademik aktif.</div>
    @endunless

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-10"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama atau kode mata kuliah"></div>
                <div class="col-md-2 d-grid"><button class="btn btn-primary"><i class="bx bx-search me-1"></i>Cari</button></div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @forelse($jadwal as $item)
            @php($modalId = 'buat-pertemuan-'.$item->id)
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1 fw-bold">{{ $item->kurikulum?->mataKuliah?->nama ?? '-' }}</h5>
                            <span class="badge bg-label-primary">{{ $item->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }}</span>
                            <span class="badge bg-label-warning">Semester {{ $item->kurikulum?->mataKuliah?->smt ?? '-' }}</span>
                            <span class="badge bg-label-info text-capitalize">{{ $item->jenis_kelas }}</span>
                        </div>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}"><i class="bx bx-plus me-1"></i>Pertemuan</button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted"><i class="bx bx-calendar me-1"></i>{{ $item->hari }}, {{ substr($item->jam_mulai,0,5) }}–{{ substr($item->jam_selesai,0,5) }} &nbsp; <i class="bx bx-map me-1"></i>{{ $item->ruangan?->nama ?? '-' }}</p>
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <div class="border rounded p-2 h-100 bg-light">
                                    <small class="text-muted d-block"><i class="bx bx-buildings me-1"></i>Program Studi</small>
                                    <span class="fw-semibold">{{ $item->programStudi?->nama ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-2 h-100 bg-light">
                                    <small class="text-muted d-block"><i class="bx bx-book-open me-1"></i>Semester Mata Kuliah</small>
                                    <span class="fw-semibold">Semester {{ $item->kurikulum?->mataKuliah?->smt ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-2 h-100 bg-light">
                                    <small class="text-muted d-block"><i class="bx bx-group me-1"></i>Jenis Kelas</small>
                                    <span class="fw-semibold text-capitalize">{{ $item->jenis_kelas ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-2 h-100 bg-light">
                                    <small class="text-muted d-block"><i class="bx bx-map me-1"></i>Ruangan</small>
                                    <span class="fw-semibold">{{ $item->ruangan?->nama ?? 'Belum diatur' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 rounded bg-label-primary p-2 mb-3">
                            <span><i class="bx bx-calendar-check me-1"></i>Tahun Akademik <strong>{{ $activeTa->nama ?? '-' }}</strong></span>
                            <span class="badge bg-primary">{{ $item->pertemuan->count() }} pertemuan</span>
                        </div>
                        <h6 class="fw-bold border-bottom pb-2">Riwayat Pertemuan</h6>
                        @forelse($item->pertemuan as $pertemuan)
                            <a href="{{ route('dosen.absensi-praktik.show', $pertemuan) }}" class="d-flex justify-content-between align-items-center text-decoration-none border rounded p-3 mb-2">
                                <span><strong>{{ $pertemuan->topik ?: 'Tanpa topik' }}</strong><small class="d-block text-muted">{{ $pertemuan->tanggal_pertemuan->translatedFormat('d M Y') }} · {{ substr($pertemuan->jam_mulai,0,5) }} · {{ ucfirst($pertemuan->metode_pbm ?: 'offline') }}</small></span>
                                <span class="badge bg-label-success">{{ $pertemuan->absensi_count }} mahasiswa</span>
                            </a>
                        @empty
                            <p class="text-center text-muted py-3 mb-0">Belum ada pertemuan praktik.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="modal fade" id="{{ $modalId }}" tabindex="-1">
                <div class="modal-dialog modal-lg"><div class="modal-content">
                    <form method="POST" action="{{ route('dosen.absensi-praktik.pertemuan.store') }}">
                        @csrf
                        <input type="hidden" name="jadwal_praktik_id" value="{{ $item->id }}">
                        <div class="modal-header"><h5 class="modal-title">Pertemuan Praktik Baru</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <p class="fw-bold mb-1">{{ $item->kurikulum?->mataKuliah?->nama }}</p>
                            <p class="text-muted small">
                                Semester {{ $item->kurikulum?->mataKuliah?->smt ?? '-' }}
                                &middot; {{ $item->programStudi?->nama ?? '-' }}
                                &middot; <span class="text-capitalize">{{ $item->jenis_kelas ?? '-' }}</span>
                            </p>
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">Tanggal</label><input type="date" name="tanggal_pertemuan" class="form-control" value="{{ old('tanggal_pertemuan', now()->toDateString()) }}" required></div>
                                <div class="col-md-4"><label class="form-label">Jam mulai</label><input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai', substr($item->jam_mulai,0,5)) }}" required></div>
                                <div class="col-md-4"><label class="form-label">Jam selesai</label><input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai', substr($item->jam_selesai,0,5)) }}" required></div>
                                <div class="col-md-4"><label class="form-label">Metode PBM</label><select name="metode_pbm" class="form-select" required><option value="offline" @selected(old('metode_pbm', 'offline') === 'offline')>Offline / Tatap Muka</option><option value="online" @selected(old('metode_pbm') === 'online')>Online / Daring</option></select></div>
                                <div class="col-12"><label class="form-label">Topik</label><input name="topik" class="form-control" maxlength="255" required></div>
                                <div class="col-12"><label class="form-label">Subtopik / keterangan</label><textarea name="sub_topik" class="form-control" maxlength="255" rows="2"></textarea></div>
                            </div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Buat dan Isi Absensi</button></div>
                    </form>
                </div></div>
            </div>
        @empty
            <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body py-5 text-center text-muted"><i class="bx bx-calendar-x fs-1 d-block mb-2"></i>Tidak ada jadwal praktik yang ditugaskan kepada Anda pada tahun akademik aktif.</div></div></div>
        @endforelse
    </div>
</div>
@endsection
