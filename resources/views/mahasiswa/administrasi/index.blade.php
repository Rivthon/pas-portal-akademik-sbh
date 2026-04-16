@extends('layouts.mahasiswa')
@section('title', 'Administrasi Keuangan')
@section('content')

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
            <div class="d-flex align-items-center row g-0">
                <div class="col-md-8">
                    <div class="card-body p-4">
                        <h4 class="card-title text-primary mb-2 fw-bold">Keuangan & Administrasi</h4>
                        <p class="mb-0 text-muted" style="line-height: 1.6;">
                            Pantau seluruh tagihan kuliah, riwayat pembayaran, dan sisa kewajiban administrasi Anda di sini. Pastikan membayar tepat waktu sebelum jatuh tempo.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 text-end d-none d-md-block">
                    <div class="p-3">
                        <img src="../assets/img/illustrations/calender.png" class="img-fluid opacity-75" alt="Finance Illustration" style="max-height: 150px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100 border-start border-primary border-4">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-wallet bx-sm"></i></span>
                        </div>
                        <div>
                            <span class="d-block text-muted fw-semibold">Total Kewajiban</span>
                            <h4 class="mb-0 fw-bold text-dark">Rp {{ number_format($totalTagihanGlobal ?? 0, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100 border-start border-success border-4">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-shield bx-sm"></i></span>
                        </div>
                        <div>
                            <span class="d-block text-muted fw-semibold">Telah Dibayar</span>
                            <h4 class="mb-0 fw-bold text-success">Rp {{ number_format($totalDibayarGlobal ?? 0, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100 border-start border-danger border-4">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-time-five bx-sm"></i></span>
                        </div>
                        <div>
                            <span class="d-block text-muted fw-semibold">Sisa Tagihan</span>
                            <h4 class="mb-0 fw-bold text-danger">Rp {{ number_format($sisaGlobal ?? 0, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Tagihan Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-list-ol pb-1"></i> Rincian Tagihan & Riwayat</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Smt</th>
                                <th>Periode / Tenor</th>
                                <th class="text-end">Jumlah Tagihan</th>
                                <th class="text-end">Telah Dibayar</th>
                                <th class="text-center">Jatuh Tempo</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pembayaran as $item)
                                @php
                                    $terbayar = $item->transaksi->where('status_verifikasi', 'diterima')->sum('nominal_bayar');
                                    $sisa = $item->jumlah_tagihan - $terbayar;
                                    $progress = $item->jumlah_tagihan > 0 ? ($terbayar / $item->jumlah_tagihan) * 100 : 0;
                                @endphp
                                <tr>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-label-dark fs-6">{{ $item->semester }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $item->tenorPembayaran->tenor ?? 'Undefinded' }}</div>
                                        <small class="text-muted">{{ $item->tahunAjaran->nama ?? '' }}</small>
                                        
                                        @if($item->transaksi->count() > 0)
                                            <div class="mt-2">
                                                <a class="text-info" data-bs-toggle="collapse" href="#trx-{{ $item->id }}" role="button" aria-expanded="false">
                                                    <i class="bx bx-history"></i> Lihat {{ $item->transaksi->count() }} Histori Bayar
                                                </a>
                                                <div class="collapse mt-2" id="trx-{{ $item->id }}">
                                                    <div class="card card-body p-2 bg-light border-0">
                                                        <ul class="list-unstyled mb-0" style="font-size: 0.85rem;">
                                                            @foreach($item->transaksi as $trx)
                                                                <li class="mb-1 border-bottom pb-1">
                                                                    <i class='bx bx-check-circle text-success'></i> 
                                                                    <strong>{{ \Carbon\Carbon::parse($trx->tanggal_bayar)->format('d/m/y') }}</strong>: 
                                                                    Rp {{ number_format($trx->nominal_bayar, 0, ',', '.') }} 
                                                                    <span class="text-muted">({{ $trx->keterangan ?? 'Pembayaran' }})</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end align-middle fw-semibold">
                                        Rp {{ number_format($item->jumlah_tagihan, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end align-middle">
                                        <div class="d-flex flex-column align-items-end">
                                            <span class="text-success fw-bold">Rp {{ number_format($terbayar, 0, ',', '.') }}</span>
                                            <div class="progress w-100 mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($sisa > 0)
                                            @php
                                                $isLate = \Carbon\Carbon::parse($item->jatuh_tempo)->isPast();
                                            @endphp
                                            <span class="{{ $isLate ? 'text-danger fw-bold' : 'text-dark' }}">
                                                {{ \Carbon\Carbon::parse($item->jatuh_tempo)->translatedFormat('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($sisa <= 0)
                                            <span class="badge bg-success p-2"><i class="bx bx-check-double"></i> LUNAS</span>
                                        @else
                                            <span class="badge bg-danger p-2">SISA: Rp {{ number_format($sisa, 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <img src="../assets/img/illustrations/page-misc-under-maintenance.png" alt="No Data" style="height: 120px;" class="mb-3 opacity-50">
                                        <h6 class="text-muted">Belum ada tagihan terdaftar.</h6>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection