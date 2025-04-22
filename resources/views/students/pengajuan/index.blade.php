@extends('layouts.mahasiswa')
@section('title', 'Pengajuan Transkrip')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center item g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Pengajuan Cetak Transkrip Mahasiswa
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Syarat dan Keperluan Untuk Mengajukan Transkrip Mahasiswa: Mahasiswa harus berada di semester akhir
                    dan telah menyelesaikan Seminar Usulan.

                </p>
                @php
                if (!function_exists('getBadgeClass')) {
                function getBadgeClass($status) {
                return match ($status) {
                'pending' => 'warning',
                'disetujui' => 'success',
                'diproses' => 'primary',
                'selesai' => 'dark',
                'ditolak' => 'danger',
                default => 'secondary',
                };
                }
                }
                @endphp
                <!-- CTA Button -->
                <div class="mb-3">
                    @if (!$pengajuanTerakhir || $pengajuanTerakhir->status === 'ditolak')
                    <div class="alert alert-danger mb-3">
                        <strong>Pengajuan Ditolak!</strong> Pastikan Anda telah memenuhi syarat dan ketentuan atau cek
                        ke
                        <strong>BAUK/BAAK</strong> untuk informasi lebih lanjut.
                        <a href="#" class="text-primary"
                            onclick="this.parentElement.style.display='none'; return false;">[Tutup]</a>
                    </div>
                    <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Buat Pengajuan Baru
                    </a>

                    @elseif ($pengajuanTerakhir->status === 'disetujui')

                    <a href="{{route('mahasiswa.krs.cetak-transkrip')}}" class="btn btn-primary">
                        Cetak Transkrip
                    </a>

                    @else
                    <div class="alert alert-warning">
                        <strong>Pengajuan dalam proses!</strong> Anda hanya dapat membuat satu pengajuan dalam satu
                        waktu.
                        Status pengajuan terakhir Anda:
                        <span class="badge bg-{{ getBadgeClass($pengajuanTerakhir->status) }}">
                            {{ ucfirst($pengajuanTerakhir->status) }}
                        </span>
                    </div>
                    @endif
                </div>



            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/kartu-study.png') }}" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>
    </div>
</div>


@endsection