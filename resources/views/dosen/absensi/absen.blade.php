@extends('layouts.dosen')
@section('title', 'Isi Absensi Teori')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Info Pertemuan Infographic Card --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-primary text-white" style="background: linear-gradient(135deg, #696cff 0%, #5f61f4 100%);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-md me-2">
                            <span class="avatar-initial rounded-circle bg-white text-primary"><i class="bx bxs-book fs-4"></i></span>
                        </div>
                        <h4 class="card-title text-white mb-0 fw-bold">Informasi Absensi Teori</h4>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <p class="text-white-50 mb-1" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Mata Kuliah</p>
                            <h6 class="text-white fw-semibold lh-base">{{ $pertemuan->jadwal->kurikulum->mataKuliah->nama }}</h6>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <p class="text-white-50 mb-1" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Topik & Sub Topik</p>
                            <h6 class="text-white fw-semibold mb-0">{{ $pertemuan->topik }}</h6>
                            <small class="text-white-50">{{ $pertemuan->sub_topik }}</small>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <p class="text-white-50 mb-1" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Tanggal</p>
                            <h6 class="text-white fw-semibold"><i class="bx bx-calendar me-1"></i>{{ \Carbon\Carbon::parse($pertemuan->tanggal_pertemuan)->translatedFormat('d F Y') }}</h6>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <p class="text-white-50 mb-1" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Jam Mengajar</p>
                            <h6 class="text-white fw-semibold mb-1"><i class="bx bx-time-five me-1"></i>{{ substr($pertemuan->jam_mulai, 0, 5) }} - {{ substr($pertemuan->jam_selesai, 0, 5) }}</h6>
                            <span class="badge bg-white text-primary shadow-sm mt-1" style="font-size: 0.75rem;">
                                {{ \Carbon\Carbon::parse($pertemuan->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($pertemuan->jam_selesai)) }} Menit
                            </span>
                            <span class="badge bg-white text-primary shadow-sm mt-1" style="font-size: 0.75rem;">
                                <i class="bx {{ strtolower($pertemuan->metode_pbm ?: 'offline') === 'online' ? 'bx-wifi' : 'bx-building' }} me-1"></i>{{ ucfirst($pertemuan->metode_pbm ?: 'offline') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h5 class="fw-bold text-dark mb-0"><i class="bx bxs-user-check text-primary me-2"></i>Daftar Hadir Mahasiswa</h5>
    </div>

    @unless($canManagePertemuan)
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-start gap-2">
            <i class="bx bx-info-circle fs-4"></i>
            <div>
                <strong>Mode lihat saja</strong>
                <div>
                    Pertemuan ini dibuat oleh {{ $pertemuan->dosen?->nama ?? 'dosen pengampu lain' }}.
                    Anda dapat melihat absensi, tetapi hanya dosen pembuat yang dapat mengubahnya.
                </div>
            </div>
        </div>
    @endunless

    <div class="alert alert-light border shadow-sm d-flex align-items-start gap-2 mb-4">
        <i class="bx bx-info-circle text-primary fs-4"></i>
        <div>
            Seluruh mahasiswa kelas ditampilkan agar status pesertanya jelas.
            Hanya mahasiswa dengan KRS yang sudah disetujui dosen pembimbing yang dapat diabsen.
        </div>
    </div>

    {{-- Form Absensi Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white pt-4 pb-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <h6 class="mb-3 mb-md-0 fw-bold text-dark"><i class="bx bx-list-check text-success me-2"></i>Form Pengisian Kehadiran</h6>
            @if($canManagePertemuan)
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" id="resetAbsensi">
                    <i class="bx bx-refresh"></i> Ulang
                </button>
                <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm py-1 px-3" id="markAllPresent">
                    <i class="bx bx-check-double me-1"></i> Hadir Semua
                </button>
            </div>
            @endif
        </div>

        <div class="card-body p-0">
            <form id="absensiForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pertemuan_id" value="{{ $pertemuan->pertemuan_id }}">

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
                            @forelse ($daftarPeserta as $index => $peserta)
                            @php
                                $mahasiswa = $peserta['mahasiswa'];
                                $item = $peserta['absensi'];
                                $bolehDiabsen = $peserta['boleh_diabsen'] && $canManagePertemuan;
                                $statusAbsensi = $item?->status ?? 'belum diabsen';
                            @endphp
                            <tr class="{{ $peserta['boleh_diabsen'] ? '' : 'table-light text-muted' }}">
                                <td class="text-center text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <span class="fw-semibold {{ $peserta['boleh_diabsen'] ? 'text-dark' : 'text-muted' }}">{{ $mahasiswa->nama }}</span>
                                    <br><small class="text-muted">{{ $mahasiswa->nim ?? '' }}</small>
                                    @if($peserta['status_krs'] === 'belum')
                                        <span class="badge bg-label-danger ms-1"><i class="bx bx-x-circle me-1"></i>Belum Mengambil KRS</span>
                                    @elseif($peserta['status_krs'] === 'menunggu')
                                        <span class="badge bg-label-warning ms-1"><i class="bx bx-time-five me-1"></i>Menunggu ACC Dospem</span>
                                    @elseif($statusAbsensi === 'belum diabsen')
                                        <span class="badge bg-label-secondary ms-1">Belum Diabsen</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-secondary">SMT {{ $mahasiswa->semester ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input status-absen cursor-pointer" type="radio" style="transform: scale(1.3);"
                                        name="status[{{ $mahasiswa->mahasiswa_id }}]" value="hadir" {{ $statusAbsensi === 'hadir' ? 'checked' : '' }}
                                        @required($bolehDiabsen) @disabled(!$bolehDiabsen)>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input status-absen cursor-pointer" type="radio" style="transform: scale(1.3);"
                                        name="status[{{ $mahasiswa->mahasiswa_id }}]" value="izin" {{ $statusAbsensi === 'izin' ? 'checked' : '' }}
                                        @disabled(!$bolehDiabsen)>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input status-absen cursor-pointer" type="radio" style="transform: scale(1.3);"
                                        name="status[{{ $mahasiswa->mahasiswa_id }}]" value="sakit" {{ $statusAbsensi === 'sakit' ? 'checked' : '' }}
                                        @disabled(!$bolehDiabsen)>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input status-absen cursor-pointer" type="radio" style="transform: scale(1.3);"
                                        name="status[{{ $mahasiswa->mahasiswa_id }}]" value="tidak hadir" {{ $statusAbsensi === 'tidak hadir' ? 'checked' : '' }}
                                        @disabled(!$bolehDiabsen)>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm border-0 bg-light" name="keterangan[{{ $mahasiswa->mahasiswa_id }}]"
                                        value="{{ $item?->keterangan ?? '' }}" placeholder="{{ $peserta['boleh_diabsen'] ? 'Catatan opsional...' : 'Tidak dapat diabsen' }}"
                                        @disabled(!$bolehDiabsen)>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">Tidak ada mahasiswa aktif yang sesuai dengan prodi, semester, dan kelas ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($canManagePertemuan && $daftarPeserta->contains('boleh_diabsen', true))
                <div class="p-4 bg-white border-top border-0 text-center">
                    <button type="submit" class="btn btn-primary rounded-pill shadow-sm px-5 py-2 fw-bold" style="letter-spacing: 0.5px;">
                        <i class="bx bx-save fs-5 me-1" style="position: relative; top: -1px;"></i> Simpan Data Presensi
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
