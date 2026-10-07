@extends('layouts.mahasiswa')
@section('title', 'Rekap Absensi Asprak')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#157347,#20a46b)">
        <div class="card-body p-4 text-white">
            <h4 class="text-white fw-bold mb-2"><i class="bx bx-test-tube me-2"></i>Rekap Absensi Asprak</h4>
            <p class="text-white-50 mb-0">Informasi pertemuan dan riwayat kehadiran Anda sebagai Asisten Praktikum.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Penugasan</small><h3 class="mb-0 text-primary">{{ $statistics['penugasan'] }}</h3></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Pertemuan Ditugaskan</small><h3 class="mb-0 text-info">{{ $statistics['pertemuan'] }}</h3></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Total Hadir</small><h3 class="mb-0 text-success">{{ $statistics['hadir'] }}</h3></div></div></div>
    </div>

    <div class="accordion" id="asprakAccordion">
        @foreach($assignments as $assignment)
            @php
                $jadwal = $assignment->jadwal;
                $collapseId = 'asprak-'.$assignment->id;
            @endphp
            <div class="accordion-item border-0 shadow-sm mb-3 rounded overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}">
                        <span class="flex-grow-1">
                            <strong>{{ $jadwal?->kurikulum?->mataKuliah?->nama ?? '-' }}</strong>
                            <small class="d-block text-muted">{{ $jadwal?->tahunAjaran?->nama ?? '-' }} {{ $jadwal?->tahunAjaran?->semester ? '('.$jadwal->tahunAjaran->semester.')' : '' }} · {{ jenis_kelas_label($jadwal?->jenis_kelas) }}</small>
                        </span>
                        <span class="badge {{ $assignment->aktif ? 'bg-label-success' : 'bg-label-secondary' }} me-3">{{ $assignment->aktif ? 'Aktif' : 'Selesai' }}</span>
                    </button>
                </h2>
                <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#asprakAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Tanggal</th><th>Informasi Pertemuan</th><th>Dosen</th><th class="text-center">Status</th><th>Keterangan</th></tr></thead>
                                <tbody>
                                    @forelse($assignment->absensi as $attendance)
                                        @php
                                            $meeting = $attendance->pertemuan;
                                            $badge = match($attendance->status) {
                                                'hadir' => 'success', 'izin' => 'info', 'sakit' => 'warning',
                                                'tidak hadir' => 'danger', default => 'secondary'
                                            };
                                        @endphp
                                        <tr>
                                            <td class="text-nowrap">{{ $meeting?->tanggal_pertemuan?->translatedFormat('d M Y') ?? '-' }}<small class="d-block text-muted">{{ $meeting ? substr($meeting->jam_mulai,0,5).'–'.substr($meeting->jam_selesai,0,5) : '-' }}</small></td>
                                            <td><strong>{{ $meeting?->topik ?: 'Tanpa topik' }}</strong><small class="d-block text-muted">{{ $meeting?->sub_topik ?: '-' }} · {{ ucfirst($meeting?->metode_pbm ?: 'offline') }}</small></td>
                                            <td>{{ $meeting?->dosen?->nama ?? '-' }}</td>
                                            <td class="text-center"><span class="badge bg-label-{{ $badge }}">{{ ucwords($attendance->status) }}</span></td>
                                            <td>{{ $attendance->keterangan ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada pertemuan yang ditugaskan kepada Anda.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
