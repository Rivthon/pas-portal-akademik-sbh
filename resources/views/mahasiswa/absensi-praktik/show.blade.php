@extends('layouts.mahasiswa')
@section('title', 'Detail Absensi Praktik')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1">{{ $jadwal->kurikulum?->mataKuliah?->nama ?? 'Absensi Praktik' }}</h4><span class="text-muted">Riwayat kehadiran praktik</span></div>
        <a href="{{ route('mahasiswa.absensi-praktik.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
    </div>

    <div class="row g-3 mb-4">
        @foreach([['Hadir',$statistik['hadir'],'success'],['Izin',$statistik['izin'],'warning'],['Sakit',$statistik['sakit'],'info'],['Alpha',$statistik['tidak hadir'],'danger']] as $card)
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">{{ $card[0] }}</small><h2 class="text-{{ $card[2] }} mb-0">{{ $card[1] }}</h2></div></div></div>
        @endforeach
    </div>
    <div class="alert alert-primary"><i class="bx bx-pie-chart-alt-2 me-2"></i>Persentase kehadiran praktik: <strong>{{ $persentase }}%</strong></div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Pertemuan</th><th>Tanggal</th><th>Topik</th><th>Waktu</th><th>Status</th><th>Keterangan</th></tr></thead>
                <tbody>
                    @forelse($pertemuan as $item)
                        @php($absen = $item->absensi->first())
                        @php($status = $absen?->status)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->tanggal_pertemuan->translatedFormat('d M Y') }}</td>
                            <td><strong>{{ $item->topik ?: '-' }}</strong><small class="d-block text-muted">{{ $item->sub_topik }}</small></td>
                            <td>{{ substr($item->jam_mulai,0,5) }}–{{ substr($item->jam_selesai,0,5) }}</td>
                            <td>
                                @if($status === 'hadir')<span class="badge bg-label-success">Hadir</span>
                                @elseif($status === 'izin')<span class="badge bg-label-warning">Izin</span>
                                @elseif($status === 'sakit')<span class="badge bg-label-info">Sakit</span>
                                @elseif($status === 'tidak hadir')<span class="badge bg-label-danger">Alpha</span>
                                @else<span class="badge bg-label-secondary">Belum dicatat</span>@endif
                            </td>
                            <td>{{ $absen?->keterangan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Dosen belum membuat pertemuan praktik.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
