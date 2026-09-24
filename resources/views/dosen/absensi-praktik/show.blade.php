@extends('layouts.dosen')
@section('title', 'Isi Absensi Praktik')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0"><i class="bx bx-user-check text-primary me-2"></i>Isi Absensi Praktik</h4>
        <a href="{{ route('dosen.absensi-praktik.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1e3c72,#2a5298)">
        <div class="card-body text-white p-4">
            <div class="row g-3">
                <div class="col-md-4"><small class="text-white-50">Mata Kuliah</small><h5 class="text-white mb-0">{{ $pertemuan->jadwal->kurikulum?->mataKuliah?->nama ?? '-' }}</h5></div>
                <div class="col-md-4"><small class="text-white-50">Topik</small><h6 class="text-white mb-0">{{ $pertemuan->topik ?: '-' }}</h6><span class="text-white-50">{{ $pertemuan->sub_topik }}</span></div>
                <div class="col-md-4"><small class="text-white-50">Waktu</small><h6 class="text-white mb-1">{{ $pertemuan->tanggal_pertemuan->translatedFormat('d F Y') }}, {{ substr($pertemuan->jam_mulai,0,5) }}–{{ substr($pertemuan->jam_selesai,0,5) }}</h6><span class="badge bg-white text-primary"><i class="bx {{ strtolower($pertemuan->metode_pbm ?: 'offline') === 'online' ? 'bx-wifi' : 'bx-building' }} me-1"></i>{{ ucfirst($pertemuan->metode_pbm ?: 'offline') }}</span></div>
            </div>
        </div>
    </div>

    <div class="alert alert-light border shadow-sm d-flex align-items-start gap-2">
        <i class="bx bx-info-circle text-primary fs-4"></i>
        <div>
            Seluruh mahasiswa kelas ditampilkan agar status pesertanya jelas.
            Hanya mahasiswa dengan KRS yang sudah disetujui dosen pembimbing yang dapat diabsen.
        </div>
    </div>

    <form method="POST" action="{{ route('dosen.absensi-praktik.update', $pertemuan) }}">
        @csrf @method('PUT')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Daftar Mahasiswa</h6>
                <button type="button" id="hadirSemua" class="btn btn-sm btn-success"><i class="bx bx-check-double me-1"></i>Hadir Semua</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Mahasiswa</th><th class="text-center">Hadir</th><th class="text-center">Izin</th><th class="text-center">Sakit</th><th class="text-center">Alpha</th><th>Keterangan</th></tr></thead>
                    <tbody>
                        @forelse($daftarPeserta as $peserta)
                            @php
                                $mahasiswa = $peserta['mahasiswa'];
                                $item = $peserta['absensi'];
                                $statusAbsensi = $item?->status ?? 'belum diabsen';
                            @endphp
                            <tr class="{{ $peserta['boleh_diabsen'] ? '' : 'table-light text-muted' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $mahasiswa->nama }}</strong>
                                    <small class="d-block text-muted">{{ $mahasiswa->nim }}</small>
                                    @if($peserta['status_krs'] === 'belum')
                                        <span class="badge bg-label-danger mt-1"><i class="bx bx-x-circle me-1"></i>Belum Mengambil KRS</span>
                                    @elseif($peserta['status_krs'] === 'menunggu')
                                        <span class="badge bg-label-warning mt-1"><i class="bx bx-time-five me-1"></i>Menunggu ACC Dospem</span>
                                    @elseif($statusAbsensi === 'belum diabsen')
                                        <span class="badge bg-label-secondary mt-1">Belum Diabsen</span>
                                    @endif
                                </td>
                                @foreach(['hadir','izin','sakit','tidak hadir'] as $status)
                                    <td class="text-center"><input class="form-check-input status-praktik" type="radio" name="status[{{ $mahasiswa->mahasiswa_id }}]" value="{{ $status }}" @checked(old('status.'.$mahasiswa->mahasiswa_id, $statusAbsensi) === $status) @required($peserta['boleh_diabsen']) @disabled(!$peserta['boleh_diabsen'])></td>
                                @endforeach
                                <td><input class="form-control form-control-sm" name="keterangan[{{ $mahasiswa->mahasiswa_id }}]" value="{{ old('keterangan.'.$mahasiswa->mahasiswa_id, $item?->keterangan) }}" maxlength="500" placeholder="{{ $peserta['boleh_diabsen'] ? 'Opsional' : 'Tidak dapat diabsen' }}" @disabled(!$peserta['boleh_diabsen'])></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">Tidak ada mahasiswa aktif yang sesuai dengan prodi, semester, dan kelas ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($daftarPeserta->contains('boleh_diabsen', true))
                <div class="card-footer bg-white text-end"><button class="btn btn-primary px-4"><i class="bx bx-save me-1"></i>Simpan Absensi Praktik</button></div>
            @endif
        </div>
    </form>
</div>
@endsection

@push('script')
<script>
document.getElementById('hadirSemua')?.addEventListener('click', function () {
    document.querySelectorAll('.status-praktik[value="hadir"]:not(:disabled)').forEach(function (radio) { radio.checked = true; });
});
</script>
@endpush
