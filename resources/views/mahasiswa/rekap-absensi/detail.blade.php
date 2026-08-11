@extends('layouts.mahasiswa')

@section('content')
@php
    $mataKuliah = $jadwal->kurikulum?->mataKuliah;
    $statusConfig = [
        'hadir' => ['success', 'check', 'Hadir'],
        'izin' => ['warning', 'envelope', 'Izin'],
        'sakit' => ['info', 'plus-medical', 'Sakit'],
        'alpha' => ['danger', 'x', 'Alpha'],
        'tidak hadir' => ['danger', 'x', 'Alpha'],
    ];
@endphp
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <a href="{{ route('mahasiswa.rekap.absensi', ['semester' => $selectedSemester]) }}" class="btn btn-sm btn-label-secondary mb-3">
                <i class="bx bx-arrow-back me-1"></i>Kembali
            </a>
            <h4 class="fw-bold mb-1">Rekap Absensi</h4>
            <p class="text-muted mb-0">{{ $mataKuliah?->nama ?? '-' }}</p>
        </div>
        @if($lmsJadwal)
            <a href="{{ route('mahasiswa.lms.show', $lmsJadwal) }}" class="btn btn-primary">
                <i class="bx bx-book-reader me-1"></i>Buka LMS Mata Kuliah
            </a>
        @endif
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['Hadir', $hadir, 'success', 'check'],
            ['Izin', $izin, 'warning', 'envelope'],
            ['Sakit', $sakit, 'info', 'plus-medical'],
            ['Alpha', $alpha, 'danger', 'x']
        ] as [$label, $jumlah, $warna, $ikon])
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <span class="avatar-initial rounded bg-label-{{ $warna }} p-3 me-3"><i class="bx bx-{{ $ikon }} fs-3"></i></span>
                        <div><h4 class="mb-0 fw-bold">{{ $jumlah }}</h4><small class="text-muted">{{ $label }}</small></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
                <strong>Persentase Kehadiran</strong><strong class="text-primary">{{ $persentase }}%</strong>
            </div>
            <div class="progress" style="height:8px">
                <div class="progress-bar" role="progressbar" style="width:{{ min(100, $persentase) }}%" aria-valuenow="{{ $persentase }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0"><i class="bx bx-history text-primary me-2"></i>Riwayat Kehadiran</h6></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th class="text-center">Pertemuan</th><th>Tanggal</th><th>Topik</th><th class="text-center">Status</th></tr>
                </thead>
                <tbody>
                    @forelse($pertemuan as $item)
                        @php
                            $status = strtolower($item->absensi->first()?->status ?? '');
                            $config = $statusConfig[$status] ?? ['secondary', 'time', 'Belum Diabsen'];
                        @endphp
                        <tr>
                            <td class="text-center"><span class="badge bg-label-secondary">{{ $loop->iteration }}</span></td>
                            <td>{{ $item->tanggal_pertemuan ? \Carbon\Carbon::parse($item->tanggal_pertemuan)->translatedFormat('d M Y') : '-' }}</td>
                            <td><strong>{{ $item->topik ?: '-' }}</strong>@if($item->sub_topik)<small class="text-muted d-block">{{ $item->sub_topik }}</small>@endif</td>
                            <td class="text-center"><span class="badge bg-label-{{ $config[0] }}"><i class="bx bx-{{ $config[1] }} me-1"></i>{{ $config[2] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-5"><i class="bx bx-calendar-x fs-1 d-block mb-2"></i>Belum ada pertemuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
