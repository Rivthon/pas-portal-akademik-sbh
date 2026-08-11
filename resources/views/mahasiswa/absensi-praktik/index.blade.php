@extends('layouts.mahasiswa')
@section('title', 'Riwayat Absensi Praktik')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1e3c72,#2a5298)">
        <div class="card-body p-4 text-white">
            <h4 class="text-white fw-bold"><i class="bx bx-test-tube me-2"></i>Riwayat Absensi Praktik</h4>
            <p class="text-white-50 mb-0">Menampilkan mata kuliah di KRS Anda yang memiliki kelas praktik pada tahun akademik aktif.</p>
        </div>
    </div>

    <div class="row g-4">
        @forelse($jadwal as $item)
            @php($persentase = $item->pertemuan_count > 0 ? round(($item->hadir_count / $item->pertemuan_count) * 100) : 0)
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <span class="badge bg-label-primary mb-2">{{ $item->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }}</span>
                        <h5 class="fw-bold">{{ $item->kurikulum?->mataKuliah?->nama ?? '-' }}</h5>
                        <p class="text-muted mb-3"><i class="bx bx-calendar me-1"></i>{{ $item->hari }}, {{ substr($item->jam_mulai,0,5) }}–{{ substr($item->jam_selesai,0,5) }}<br><i class="bx bx-map me-1"></i>{{ $item->ruangan?->nama ?? '-' }}</p>
                        <div class="d-flex justify-content-between"><span>Total pertemuan</span><strong>{{ $item->pertemuan_count }}</strong></div>
                        <div class="d-flex justify-content-between mb-3"><span>Kehadiran</span><strong class="text-success">{{ $persentase }}%</strong></div>
                        <a href="{{ route('mahasiswa.absensi-praktik.show', $item) }}" class="btn btn-outline-primary w-100">Lihat Riwayat</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5"><i class="bx bx-folder-open fs-1 d-block mb-2"></i>Belum ada mata kuliah KRS yang memiliki kelas praktik pada tahun akademik aktif.</div></div></div>
        @endforelse
    </div>
</div>
@endsection
