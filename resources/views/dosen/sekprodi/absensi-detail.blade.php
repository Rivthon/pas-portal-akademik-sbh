@extends('layouts.dosen')
@section('title', 'Detail Absensi Dosen')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <a href="{{ route('dosen.sekprodi.absensi.index', ['program_studi_id'=>$jadwal->jurusan_id,'ta_id'=>$jadwal->ta_id,'jenis'=>$jenis]) }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
        <h4 class="mb-1">{{ $jadwal->kurikulum?->mataKuliah?->nama }}</h4>
        <span class="text-muted">{{ $jadwal->programStudi?->nama }} · {{ jenis_kelas_label($jadwal->jenis_kelas ?? 'Reguler') }} · {{ $jadwal->tahunAjaran?->nama }} {{ $jadwal->tahunAjaran?->semester }}</span>
    </div>
    @if($pertemuan->isNotEmpty())
        <a href="{{ route('dosen.sekprodi.absensi.download', [$jenis, $jadwal->id]) }}" class="btn btn-primary"><i class="bx bx-download me-1"></i>Download Rekap PDF</a>
    @endif
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><h5 class="mb-0">Riwayat Pertemuan {{ ucfirst($jenis) }}</h5></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Tanggal</th><th>Topik</th><th>Pengajar</th><th class="text-center text-success">H</th><th class="text-center">S</th><th class="text-center">I</th><th class="text-center text-danger">A</th></tr></thead>
            <tbody>
                @forelse($pertemuan as $item)
                    <tr>
                        <td>P{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_pertemuan)->format('d/m/Y') }}<br><small class="text-muted">{{ $item->jam_mulai ? substr($item->jam_mulai,0,5) : '--:--' }}–{{ $item->jam_selesai ? substr($item->jam_selesai,0,5) : '--:--' }}</small></td>
                        <td><div class="fw-semibold">{{ $item->topik ?: '-' }}</div><small class="text-muted">{{ $item->sub_topik }}</small></td>
                        <td>{{ $item->dosen?->nama ?: '-' }}</td>
                        <td class="text-center text-success fw-semibold">{{ $item->hadir }}</td>
                        <td class="text-center">{{ $item->sakit }}</td>
                        <td class="text-center">{{ $item->izin }}</td>
                        <td class="text-center text-danger fw-semibold">{{ $item->alfa }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">Belum ada pertemuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
