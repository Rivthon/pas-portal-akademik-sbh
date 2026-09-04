@extends('layouts.master')
@section('title', 'Detail BAP Pengajaran')

@section('content')
<div class="mb-3">
    <div class="d-flex flex-wrap justify-content-between gap-2">
        <a href="{{ route('admin.bap-pengajaran.index', ['ta_id' => $selectedTa?->ta_id]) }}"
            class="btn btn-sm btn-label-secondary">
            <i class="bx bx-arrow-back me-1"></i>Kembali
        </a>
        <a href="{{ route('admin.bap-pengajaran.pdf', ['dosen' => $dosen, 'ta_id' => $selectedTa?->ta_id]) }}"
            class="btn btn-sm btn-danger">
            <i class="bx bxs-file-pdf me-1"></i>Download BAP PDF
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="badge bg-label-success mb-2">DETAIL BAP DOSEN</span>
                <h3 class="fw-bold mb-1">{{ $dosen->nama }}</h3>
                <p class="text-muted mb-1">NIDN {{ $dosen->nidn ?: '-' }} &bull; {{ $dosen->kd_dosen ?: '-' }}</p>
                <small>{{ $selectedTa?->nama ?? '-' }} - {{ ucfirst($selectedTa?->semester ?? '-') }}</small>
            </div>
            <div class="text-end">
                <small class="text-muted d-block">Total Pengajaran Diakui</small>
                <h2 class="text-success fw-bold mb-0">{{ $jumlahTeori + $jumlahPraktik }} kali</h2>
                <small class="text-muted">Maksimal {{ $maksimalPertemuan }} per kelas</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <small class="text-muted">Pengajaran Teori</small><h3 class="text-primary mb-0">{{ $jumlahTeori }} kali</h3>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <small class="text-muted">Pengajaran Praktik</small><h3 class="text-warning mb-0">{{ $jumlahPraktik }} kali</h3>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <small class="text-muted">Total Kelas Diampu</small><h3 class="mb-0">{{ $jadwalTeori->count() + $jadwalPraktik->count() }}</h3>
        </div></div>
    </div>
</div>

@foreach([
    ['key' => 'teori', 'title' => 'Pengajaran Teori', 'items' => $jadwalTeori, 'color' => 'primary', 'label' => 'Teori'],
    ['key' => 'praktik', 'title' => 'Pengajaran Praktik', 'items' => $jadwalPraktik, 'color' => 'warning', 'label' => 'Praktik'],
] as $section)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bx bx-book-open text-{{ $section['color'] }} me-2"></i>{{ $section['title'] }}
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Mata Kuliah</th>
                        <th>Program Studi</th>
                        <th>Kelas / Jadwal</th>
                        <th class="text-center">Tercatat</th>
                        <th class="text-center">Diakui</th>
                        <th>Terakhir Mengajar</th>
                        <th class="text-center">Rincian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($section['items'] as $jadwal)
                        @php
                            $collapseId = 'rincian-'.$section['key'].'-'.$jadwal->id;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $jadwal->kurikulum?->mataKuliah?->nama ?? '-' }}</strong>
                                <small class="d-block text-muted">{{ $jadwal->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }}</small>
                            </td>
                            <td>{{ $jadwal->kurikulum?->programStudi?->nama ?? '-' }}</td>
                            <td>
                                <span class="badge bg-label-{{ strtolower((string) $jadwal->jenis_kelas) === 'karyawan' ? 'warning' : 'primary' }}">
                                    {{ jenis_kelas_label($jadwal->jenis_kelas ?: '-') }}
                                </span>
                                <small class="d-block text-muted mt-1">
                                    {{ $jadwal->hari ?: '-' }},
                                    {{ $jadwal->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : '-' }} -
                                    {{ $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : '-' }}
                                </small>
                            </td>
                            <td class="text-center">{{ $jadwal->jumlah_pertemuan }} kali</td>
                            <td class="text-center">
                                <span class="badge bg-label-success">{{ $jadwal->jumlah_diakui }} / {{ $maksimalPertemuan }}</span>
                            </td>
                            <td>
                                {{ $jadwal->terakhir_mengajar
                                    ? \Carbon\Carbon::parse($jadwal->terakhir_mengajar)->translatedFormat('d F Y')
                                    : '-' }}
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-label-{{ $section['color'] }}"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                    aria-expanded="false" aria-controls="{{ $collapseId }}">
                                    <i class="bx bx-chevron-down me-1"></i>Detail Sesi
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="{{ $collapseId }}">
                            <td colspan="8" class="bg-light p-3">
                                <div class="table-responsive rounded border bg-white">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Ke</th>
                                                <th>Hari</th>
                                                <th>Tanggal</th>
                                                <th>Jam Mulai</th>
                                                <th>Jam Selesai</th>
                                                <th>Durasi</th>
                                                <th>Metode PBM</th>
                                                <th>Topik</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($jadwal->pertemuan as $pertemuan)
                                                @php
                                                    $tanggal = $pertemuan->tanggal_pertemuan
                                                        ? \Carbon\Carbon::parse($pertemuan->tanggal_pertemuan)
                                                        : null;
                                                    $jamMulai = $pertemuan->jam_mulai ?: $jadwal->jam_mulai;
                                                    $jamSelesai = $pertemuan->jam_selesai ?: $jadwal->jam_selesai;
                                                    $durasiMenit = ($jamMulai && $jamSelesai)
                                                        ? \Carbon\Carbon::parse($jamMulai)->diffInMinutes(\Carbon\Carbon::parse($jamSelesai), false)
                                                        : null;
                                                    $durasiJam = $durasiMenit > 0 ? intdiv((int) $durasiMenit, 60) : 0;
                                                    $sisaMenit = $durasiMenit > 0 ? (int) $durasiMenit % 60 : 0;
                                                    $durasi = $durasiMenit > 0
                                                        ? ($durasiJam > 0
                                                            ? $durasiJam.' jam'.($sisaMenit > 0 ? ' '.$sisaMenit.' menit' : '')
                                                            : (int) $durasiMenit.' menit')
                                                        : '-';
                                                @endphp
                                                <tr>
                                                    <td class="text-center">
                                                        <span class="badge bg-label-{{ $loop->iteration <= $maksimalPertemuan ? 'success' : 'secondary' }}">
                                                            {{ $loop->iteration }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $tanggal?->translatedFormat('l') ?? '-' }}</td>
                                                    <td>{{ $tanggal?->translatedFormat('d F Y') ?? '-' }}</td>
                                                    <td>{{ $jamMulai ? substr($jamMulai, 0, 5) : '-' }}</td>
                                                    <td>{{ $jamSelesai ? substr($jamSelesai, 0, 5) : '-' }}</td>
                                                    <td><span class="badge bg-label-info">{{ $durasi }}</span></td>
                                                    <td>
                                                        <span class="badge bg-label-{{ strtolower($pertemuan->metode_pbm ?: 'offline') === 'online' ? 'primary' : 'secondary' }}">
                                                            <i class="bx {{ strtolower($pertemuan->metode_pbm ?: 'offline') === 'online' ? 'bx-wifi' : 'bx-building' }} me-1"></i>
                                                            {{ ucfirst($pertemuan->metode_pbm ?: 'offline') }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $pertemuan->topik ?: '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-3">
                                                        Belum ada sesi mengajar yang tercatat.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada kelas {{ strtolower($section['label']) }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@endsection
