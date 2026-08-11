@extends('layouts.master')
@section('title', 'Detail Absensi - ' . ($jadwal?->kurikulum?->mataKuliah?->nama ?? 'Absensi'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Info Pertemuan Infographic Card --}}
   <div class="row mb-4">
        <div class="col-12">
            {{-- Menggunakan warna gradient primary khas Sneat --}}
            <div class="card shadow-sm border-0 position-relative overflow-hidden" style="background: linear-gradient(135deg, #696cff 0%, #5f61f4 100%); color: #fff;">
                <div class="card-body p-4">

                    {{-- Header Area --}}
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                {{-- Avatar disesuaikan dengan shadow tipis bergaya Sneat --}}
                                <span class="avatar-initial rounded bg-white text-primary shadow-sm">
                                    <i class="bx bxs-book fs-4"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="card-title text-white mb-0 fw-bold">Detail Kehadiran Mahasiswa</h4>
                                <span class="text-white-50" style="font-size: 0.85rem;">Monitoring data absensi pertemuan kelas</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.absensi.index') }}" class="btn btn-sm btn-outline-white rounded-pill d-flex align-items-center">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                    </div>

                    {{-- Detail Info Area (Dengan efek Glassmorphism & Grid Responsif) --}}
                    <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px);">
                        <div class="row gy-3">

                            {{-- Blok 1: Mata Kuliah --}}
                            <div class="col-sm-6 col-lg-3 border-end-md border-white border-opacity-25">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bx bx-book-bookmark text-white-50 me-2"></i>
                                    <span class="text-white-50 fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Mata Kuliah</span>
                                </div>
                                <h6 class="text-white fw-bold mb-0 text-truncate" title="{{ $jadwal?->kurikulum?->mataKuliah?->nama ?? '-' }}">
                                    {{ $jadwal?->kurikulum?->mataKuliah?->nama ?? '-' }}
                                </h6>
                                <small class="text-white-50 d-block text-truncate">{{ $jadwal?->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }}</small>
                            </div>

                            {{-- Blok 2: Topik & Sub Topik --}}
                            <div class="col-sm-6 col-lg-3 border-end-lg border-white border-opacity-25 ps-sm-3">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bx bx-bulb text-white-50 me-2"></i>
                                    <span class="text-white-50 fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Topik & Sub Topik</span>
                                </div>
                                <h6 class="text-white fw-bold mb-0 text-truncate" title="{{ $pertemuan?->topik ?? '-' }}">
                                    {{ $pertemuan?->topik ?? '-' }}
                                </h6>
                                <small class="text-white-50 d-block text-truncate">{{ $pertemuan?->sub_topik ?? '-' }}</small>
                            </div>

                            {{-- Blok 3: Tanggal --}}
                            <div class="col-sm-6 col-lg-3 border-end-md border-white border-opacity-25 ps-lg-3">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bx bx-calendar text-white-50 me-2"></i>
                                    <span class="text-white-50 fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tanggal</span>
                                </div>
                                <h6 class="text-white fw-bold mb-0 mt-1">
                                    {{ !empty($pertemuan->tanggal_pertemuan) ? \Carbon\Carbon::parse($pertemuan->tanggal_pertemuan)->translatedFormat('d F Y') : '-' }}
                                </h6>
                            </div>

                            {{-- Blok 4: Jam Mengajar --}}
                            <div class="col-sm-6 col-lg-3 ps-sm-3">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bx bx-time-five text-white-50 me-2"></i>
                                    <span class="text-white-50 fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Jam Mengajar</span>
                                </div>
                                <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                                    <h6 class="text-white fw-bold mb-0">
                                        {{ !empty($pertemuan->jam_mulai) ? \Carbon\Carbon::parse($pertemuan->jam_mulai)->format('H:i') : '-' }}
                                        -
                                        {{ !empty($pertemuan->jam_selesai) ? \Carbon\Carbon::parse($pertemuan->jam_selesai)->format('H:i') : '-' }}
                                    </h6>
                                    @if(!empty($pertemuan->jam_mulai) && !empty($pertemuan->jam_selesai))
                                        <span class="badge bg-white text-primary rounded-pill shadow-sm" style="font-size: 0.7rem; padding: 0.35em 0.65em;">
                                            {{ \Carbon\Carbon::parse($pertemuan->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($pertemuan->jam_selesai)) }} Mnt
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{--
      TIPS CSS TAMBAHAN (Opsional):
      Agar garis border responsifnya berfungsi sempurna di Bootstrap 5,
      letakkan CSS ini di dalam tag <style> di bagian atas halaman Anda:
    --}}
    @push('head')
    <style>
        @media (min-width: 576px) {
            .border-end-sm { border-right: 1px solid rgba(255,255,255,0.25) !important; }
        }
        @media (min-width: 768px) {
            .border-end-md { border-right: 1px solid rgba(255,255,255,0.25) !important; }
        }
        @media (min-width: 992px) {
            .border-end-lg { border-right: 1px solid rgba(255,255,255,0.25) !important; }
        }
    </style>
    @endpush
    {{-- Form Absensi Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white pt-4 pb-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <h5 class="mb-3 mb-md-0 fw-bold text-dark"><i class="bx bx-list-check text-success me-2"></i>Daftar Kehadiran</h5>
            <div class="d-flex gap-2">
                <span class="badge bg-label-success px-3 py-2"><i class="bx bx-check-circle me-1"></i>Hadir: {{ $absensi->where('status', 'hadir')->count() }}</span>
                <span class="badge bg-label-info px-3 py-2"><i class="bx bx-info-circle me-1"></i>Izin: {{ $absensi->where('status', 'izin')->count() }}</span>
                <span class="badge bg-label-warning px-3 py-2"><i class="bx bx-plus-medical me-1"></i>Sakit: {{ $absensi->where('status', 'sakit')->count() }}</span>
                <span class="badge bg-label-danger px-3 py-2"><i class="bx bx-x-circle me-1"></i>Alpha: {{ $absensi->where('status', 'tidak hadir')->count() }}</span>
            </div>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('admin.absensi.updateMassal') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="pertemuan_id" value="{{ $pertemuan?->pertemuan_id ?? '' }}">

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th style="min-width: 200px;">Nama Mahasiswa</th>
                                <th class="text-center" style="width: 80px;">SMT</th>
                                <th class="text-center px-2" style="width: 80px;"><span class="badge bg-success shadow-sm">Hadir</span></th>
                                <th class="text-center px-2" style="width: 80px;"><span class="badge bg-info shadow-sm">Izin</span></th>
                                <th class="text-center px-2" style="width: 80px;"><span class="badge bg-warning shadow-sm">Sakit</span></th>
                                <th class="text-center px-2" style="width: 80px;"><span class="badge bg-danger shadow-sm">Alpha</span></th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($absensi as $index => $item)
                                <tr>
                                    <td class="text-center text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $item->mahasiswa->nama ?? '-' }}</span>
                                        <br><small class="text-muted">{{ $item->mahasiswa->nim ?? '' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-secondary">SMT {{ $item->mahasiswa->semester ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input cursor-pointer" type="radio" style="transform: scale(1.3);"
                                            name="status[{{ $item->absensi_id }}]" value="hadir" {{ $item->status == 'hadir' ? 'checked' : '' }}
                                            @disabled(!auth()->user()->can('absensi-edit'))>
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input cursor-pointer" type="radio" style="transform: scale(1.3);"
                                            name="status[{{ $item->absensi_id }}]" value="izin" {{ $item->status == 'izin' ? 'checked' : '' }}
                                            @disabled(!auth()->user()->can('absensi-edit'))>
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input cursor-pointer" type="radio" style="transform: scale(1.3);"
                                            name="status[{{ $item->absensi_id }}]" value="sakit" {{ $item->status == 'sakit' ? 'checked' : '' }}
                                            @disabled(!auth()->user()->can('absensi-edit'))>
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input cursor-pointer" type="radio" style="transform: scale(1.3);"
                                            name="status[{{ $item->absensi_id }}]" value="tidak hadir" {{ $item->status == 'tidak hadir' ? 'checked' : '' }}
                                            @disabled(!auth()->user()->can('absensi-edit'))>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm border-0 bg-light"
                                            name="keterangan[{{ $item->absensi_id }}]"
                                            value="{{ $item->keterangan ?? '' }}" placeholder="Catatan opsional..."
                                            @disabled(!auth()->user()->can('absensi-edit'))>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bx bx-folder-open text-muted" style="font-size: 3rem;"></i>
                                        <h6 class="mt-3 fw-bold text-dark">Belum ada data absensi</h6>
                                        <p class="text-muted mb-0">Tidak ada mahasiswa yang terdaftar untuk pertemuan ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($absensi->count() > 0)
                    <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center p-3">
                        <small class="text-muted">Total: <strong>{{ $absensi->count() }}</strong> mahasiswa</small>
                        @can('absensi-edit')
                            <button type="submit" class="btn btn-primary rounded-pill shadow-sm px-4">
                                <i class="bx bx-save me-1"></i> Simpan Semua Perubahan
                            </button>
                        @endcan
                    </div>
                @endif
            </form>
        </div>
    </div>

</div>
@endsection
