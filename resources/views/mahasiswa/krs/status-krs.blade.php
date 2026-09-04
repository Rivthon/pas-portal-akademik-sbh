@extends('layouts.mahasiswa')
@section('title', 'Jadwal Kuliah')
@section('content')

<div class="row mt-4">
    @php
    $user = auth()->guard('mahasiswa')->user();
    $krs_status = $user->status_krs;
    @endphp

    @if($krs_status == 0)
    <div class="col-md-12">
        <div class="card shadow-sm mb-4 border-top border-5 border-danger">
            <div class="d-flex align-items-center row g-0">
                <div class="col-md-5 text-center">
                    <div class="p-4">
                        <img src="{{ asset('assets/img/illustrations/error-404.png') }}" class="img-fluid"
                            alt="Restricted Access" style="max-height: 180px;">
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card-body">
                        <h5 class="card-title text-danger mb-3 fw-bold">
                            <i class="bx bx-lock me-1"></i> Akses Status KRS Dikunci
                        </h5>
                        <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                            <i class="bx bx-error-circle fs-4 me-2 border-danger"></i>
                            <div>
                                <strong>Perhatian:</strong> Anda belum menyelesaikan proses KRS.
                            </div>
                        </div>
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Detail dan fasilitas cetak Kartu Rencana Studi (KRS) tidak dapat diakses saat ini. Harap selesaikan verifikasi administrasi dan pembayaran kepada bagian <strong>Keuangan/BAUK</strong> terlebih dahulu.
                        </p>
                        <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Kembali ke Dasbor
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-md-12">
        @if(session('error'))
            <div class="alert alert-danger shadow-sm border-0"><i class="bx bx-error-circle me-2"></i>{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success shadow-sm border-0"><i class="bx bx-check-circle me-2"></i>{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger shadow-sm border-0"><i class="bx bx-error-circle me-2"></i>{{ $errors->first() }}</div>
        @endif
        <div class="card shadow-sm mb-4">
            <div class="d-flex align-items-center row g-0">
                <!-- Content Section -->
                <div class="col-md-7">
                    <div class="card-body">
                        <!-- Title -->
                        <h5 class="card-title text-primary mb-3 fw-bold">
                            Kartu Rencana Studi (Mahasiswa)
                        </h5>
                        <!-- Description -->
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Status Kartu Rencana Studi Tahun Akademik {{ $activeTA->nama ?? '-' }}
                        </p>
                        <div class="d-flex align-items-center gap-3 p-3 mb-3 rounded border bg-label-primary">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded-circle bg-primary">
                                    <i class="bx bx-user-check"></i>
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Dosen Pembimbing Akademik</small>
                                <span class="fw-semibold text-dark">
                                    {{ $mahasiswa->dosen?->nama ?? 'Belum ditentukan' }}
                                </span>
                            </div>
                        </div>
                        @if($krs->isEmpty())
                            <div class="alert alert-warning border-0 mb-3">
                                <i class="bx bx-time-five me-1"></i>KRS belum diambil.
                            </div>
                        @elseif($krsDisetujui)
                            @php($persetujuan = $krs->first())
                            <div class="alert alert-success border-0 mb-3">
                                <div class="fw-bold"><i class="bx bx-check-shield me-1"></i>KRS telah disetujui Dosen Pembimbing</div>
                                <small>
                                    {{ $persetujuan->disetujuiOleh?->nama ?? 'Dosen Pembimbing' }}
                                    • {{ $persetujuan->disetujui_pada?->translatedFormat('d F Y H:i') }}
                                </small>
                            </div>
                        @else
                            <div class="alert alert-warning border-0 mb-3">
                                <div class="fw-bold"><i class="bx bx-hourglass me-1"></i>Menunggu ACC Dosen Pembimbing</div>
                                <small>KRS sudah diajukan dan belum dapat dianggap final sebelum disetujui.</small>
                            </div>
                        @endif
                        <!-- CTA Button -->
                        @if($krsDisetujui)
                            <div class="mb-3 d-flex flex-wrap gap-2">
                                <a href="{{ route('mahasiswa.krs.cetak-kapro') }}" class="btn btn-primary">
                                    Cetak KAPRO
                                </a>
                                <a href="{{ route('mahasiswa.krs.cetak-krs-baak') }}" class="btn btn-primary">
                                    Cetak BAAK
                                </a>
                                <a href="{{ route('mahasiswa.krs.cetak-krs-dospem') }}" class="btn btn-primary">
                                    Cetak DOSPEM
                                </a>
                                <a href="{{ route('mahasiswa.krs.cetak-krs-mahasiswa') }}" class="btn btn-primary">
                                    Cetak Mahasiswa
                                </a>
                            </div>
                        @elseif($krs->isNotEmpty())
                            <div class="alert alert-info border-0 mb-3">
                                <i class="bx bx-lock-alt me-1"></i>
                                Menu cetak KRS tersedia setelah KRS disetujui Dosen Pembimbing.
                            </div>
                        @endif

                    </div>
                </div>
                <!-- Image Section -->
                <div class="col-md-5 text-center">
                    <div class="p-3">
                        <img src="../assets/img/illustrations/kartu-study.png" class="img-fluid"
                            alt="Illustration of a schedule" style="max-height: 200px;">
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <x-krs-guidance-thread :messages="$guidanceMessages ?? collect()"
                    :action="route('mahasiswa.status.krs.comment.store')" viewer="mahasiswa"
                    title="Diskusi dengan Dosen Pembimbing" submit-label="Kirim Umpan Balik" />
            </div>
        </div>
        <div class="card bg-light">
            <div class="card-body">
                <h4 class="card-title">List Kartu Rencana Studi</h4>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Kode Mata Kuliah</th>
                                <th>Nama Mata Kuliah</th>
                                <th>SKS</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($krs as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->kurikulum->mataKuliah->matakuliah_id }}</td>
                                <td>{{ $item->kurikulum->mataKuliah->nama }}</td>
                                <td>{{ $item->kurikulum->mataKuliah->sks }}</td>
                                <td>
                                    @if($item->disetujui_pada)
                                        <span class="badge bg-label-success"><i class="bx bx-lock-alt me-1"></i>Disetujui</span>
                                    @else
                                        <form action="{{ route('mahasiswa.hapus.krs', $item->krs_id) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada mata kuliah yang diambil.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
    @endif
</div>
@endsection
