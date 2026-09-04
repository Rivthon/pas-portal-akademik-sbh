@extends('layouts.dosen')
@section('title', 'Verifikasi Absensi Kaprodi')

@section('content')
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-4 text-white" style="background: linear-gradient(135deg, #3156a3 0%, #5a72d8 100%);">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-25" style="width:56px;height:56px;">
                            <i class="bx bx-check-shield fs-2"></i>
                        </span>
                        <div>
                            <h4 class="text-white mb-1">Verifikasi Rekap Absensi</h4>
                            <p class="mb-0 text-white-50">Periksa dan verifikasi rekap pengajaran pada program studi yang Anda pimpin.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <form method="GET" class="bg-white rounded p-2 shadow-sm">
                        <label for="ta_id" class="form-label small text-muted fw-semibold mb-1">Tahun Akademik</label>
                        <input type="hidden" name="search" value="{{ $search }}">
                        <select id="ta_id" name="ta_id" class="form-select border-0 bg-light" onchange="this.form.submit()">
                            @foreach ($tahunAjaran as $ta)
                                <option value="{{ $ta->ta_id }}" @selected($taId == $ta->ta_id)>{{ $ta->nama }} - {{ ucfirst($ta->semester) }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bx bx-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('dosen.kaprodi.absensi.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="ta_id" value="{{ $taId }}">
                <div class="col-md">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                        <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama atau kode mata kuliah..." maxlength="100" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-auto d-grid d-md-block"><button type="submit" class="btn btn-primary"><i class="bx bx-search me-1"></i>Cari</button></div>
                @if ($search !== '')
                    <div class="col-md-auto d-grid d-md-block"><a href="{{ route('dosen.kaprodi.absensi.index', ['ta_id' => $taId]) }}" class="btn btn-outline-secondary"><i class="bx bx-reset me-1"></i>Reset</a></div>
                @endif
            </form>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h5 class="mb-1">Daftar Mata Kuliah</h5>
            <span class="text-muted small">
                {{ $jadwals->total() }} kelas ditemukan
                @if ($search !== '')
                    untuk "{{ $search }}"
                @endif
            </span>
        </div>
        <div class="d-flex gap-2 small">
            <span class="badge bg-label-success px-3 py-2"><i class="bx bx-check me-1"></i>Terverifikasi</span>
            <span class="badge bg-label-warning px-3 py-2"><i class="bx bx-time me-1"></i>Menunggu</span>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th class="ps-4">Mata Kuliah</th><th>Program Studi</th><th>Pengajar</th><th class="text-center">Pertemuan</th><th>Status</th><th class="text-end pe-4">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($jadwals as $jadwal)
                        @php
                            $verifikasi = $verifications->get($jadwal->id);
                            $perubahanTerakhir = $latestUpdates->get($jadwal->id);
                            $perluUlang = $verifikasi && $perubahanTerakhir && $verifikasi->verified_at->lt(\Carbon\Carbon::parse($perubahanTerakhir));
                            $pengajar = $jadwal->kurikulum?->dosenToMatakuliah?->pluck('dosen.nama')->filter()->unique()->join(', ') ?: '-';
                        @endphp
                        <tr>
                            <td class="ps-4"><div class="fw-semibold text-dark">{{ $jadwal->kurikulum?->mataKuliah?->nama ?? '-' }}</div><div class="small text-muted mt-1"><i class="bx bx-book me-1"></i>Semester {{ $jadwal->kurikulum?->mataKuliah?->smt ?? '-' }}</div></td>
                            <td><div>{{ $jadwal->programStudi?->nama ?? '-' }}</div><span class="badge bg-label-info mt-1">{{ jenis_kelas_label($jadwal->jenis_kelas ?? 'Reguler') }}</span></td>
                            <td><span class="d-inline-block text-truncate" style="max-width:220px;" title="{{ $pengajar }}">{{ $pengajar }}</span></td>
                            <td class="text-center"><span class="fw-bold fs-5">{{ $jadwal->pertemuan_count }}</span><small class="d-block text-muted">pertemuan</small></td>
                            <td>
                                @if (! $verifikasi)<span class="badge bg-label-warning"><i class="bx bx-time me-1"></i>Belum diverifikasi</span>
                                @elseif ($perluUlang)<span class="badge bg-label-danger"><i class="bx bx-refresh me-1"></i>Perlu verifikasi ulang</span>
                                @else<span class="badge bg-label-success"><i class="bx bx-check me-1"></i>Terverifikasi</span><small class="d-block text-muted mt-1">{{ $verifikasi->verified_at->format('d/m/Y H:i') }}</small>@endif
                            </td>
                            <td class="text-end pe-4"><a class="btn btn-sm btn-primary text-nowrap" href="{{ route('dosen.kaprodi.absensi.show', $jadwal) }}"><i class="bx bx-show me-1"></i>Lihat Rekap</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5"><i class="bx bx-calendar-x d-block text-muted mb-2" style="font-size:3rem;"></i><h6>Belum ada jadwal</h6><p class="text-muted mb-0">Tidak ada mata kuliah pada tahun akademik ini.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-md-none p-3">
            @forelse ($jadwals as $jadwal)
                @php $verifikasi = $verifications->get($jadwal->id); $perubahanTerakhir = $latestUpdates->get($jadwal->id); $perluUlang = $verifikasi && $perubahanTerakhir && $verifikasi->verified_at->lt(\Carbon\Carbon::parse($perubahanTerakhir)); @endphp
                <div class="border rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between gap-2 mb-2"><h6 class="mb-0">{{ $jadwal->kurikulum?->mataKuliah?->nama ?? '-' }}</h6><span class="badge bg-label-info align-self-start">{{ jenis_kelas_label($jadwal->jenis_kelas ?? 'Reguler') }}</span></div>
                    <div class="small text-muted mb-3">{{ $jadwal->programStudi?->nama ?? '-' }}  -  Semester {{ $jadwal->kurikulum?->mataKuliah?->smt ?? '-' }}  -  {{ $jadwal->pertemuan_count }} pertemuan</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">@if(!$verifikasi)<span class="badge bg-label-warning">Belum diverifikasi</span>@elseif($perluUlang)<span class="badge bg-label-danger">Verifikasi ulang</span>@else<span class="badge bg-label-success">Terverifikasi</span>@endif<a class="btn btn-sm btn-primary" href="{{ route('dosen.kaprodi.absensi.show', $jadwal) }}">Lihat Rekap</a></div>
                </div>
            @empty
                <div class="text-center text-muted py-5">Belum ada jadwal.</div>
            @endforelse
        </div>

        @if ($jadwals->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center py-3">{{ $jadwals->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
