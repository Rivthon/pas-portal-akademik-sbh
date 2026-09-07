@extends('layouts.mahasiswa')
@section('title', 'Jadwal Kuliah')
@section('content')

<div class="row mt-4">
    @php
    $user = auth()->guard('mahasiswa')->user();
    $krs_status = $user->status_krs;
    @endphp
    @if($krs_status == 0)
    <div class="card shadow-sm mb-4 border-top border-5 border-danger">
        <div class="d-flex align-items-center row g-0">
            <!-- Image Section -->
            <div class="col-md-5 text-center">
                <div class="p-4">
                    <img src="{{ asset('assets/img/illustrations/error-404.png') }}" class="img-fluid"
                        alt="Restricted Access" style="max-height: 180px;">
                </div>
            </div>
            <!-- Content Section -->
            <div class="col-md-7">
                <div class="card-body">
                    <!-- Title -->
                    <h5 class="card-title text-danger mb-3 fw-bold">
                        <i class="bx bx-lock me-1"></i> Akses Kartu Rencana Studi Dikunci
                    </h5>
                    <!-- Conditional Alert -->
                    <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                        <i class="bx bx-error-circle fs-4 me-2 border-danger"></i>
                        <div>
                            <strong>Perhatian:</strong> Status pengisian KRS Anda masih dinonaktifkan.
                        </div>
                    </div>
                    <!-- Description -->
                    <p class="mb-4 text-muted" style="line-height: 1.6;">
                        Anda belum diizinkan untuk melihat dan mengisi Kartu Rencana Studi (KRS). Harap selesaikan verifikasi administrasi dan pembayaran kepada bagian <strong>Keuangan/BAUK</strong> untuk membuka akses KRS Anda di sistem.
                    </p>
                    <!-- CTA Button -->
                    <div class="mb-3">
                        <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Kembali ke Dasbor
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @else($krs_status == 1)
    <div class="col-md-12">
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
                            Sebelum Memilih Kartu Rencana Studi, Pastikan Anda Sudah Memilih Mata Kuliah Yang Akan
                            Diambil, dan Perhatikan Semester yang sedang di tempuh.
                        </p>
                        <!-- CTA Button -->
                        <div class="mb-3">
                            <a href="{{route('mahasiswa.status.krs.index')}}" class="btn btn-primary">
                                Lihat Status KRS
                            </a>
                        </div>
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
        <div class="card bg-light">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <form id="krsForm">

                        <table class="table table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>No</th>
                                    <th>Nama Mata Kuliah</th>
                                    <th>SKS</th>
                                    <th>Semester</th>
                                    <th>Kategori</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($krs->isNotEmpty())
                                    @php
                                        $kelompokKrs = [
                                            [
                                                'judul' => 'Mata Kuliah Wajib',
                                                'ikon' => 'bx-book-bookmark',
                                                'warna' => 'primary',
                                                'items' => $krs->filter(fn ($item) => (int) ($item->mataKuliah?->kategori_mk ?? -1) === 0),
                                            ],
                                            [
                                                'judul' => 'Mata Kuliah Pilihan',
                                                'ikon' => 'bx-list-check',
                                                'warna' => 'success',
                                                'items' => $krs->filter(fn ($item) => (int) ($item->mataKuliah?->kategori_mk ?? -1) === 1),
                                            ],
                                            [
                                                'judul' => 'Belum Dikategorikan',
                                                'ikon' => 'bx-help-circle',
                                                'warna' => 'secondary',
                                                'items' => $krs->filter(fn ($item) => ! in_array((int) ($item->mataKuliah?->kategori_mk ?? -1), [0, 1], true)),
                                            ],
                                        ];
                                        $nomor = 1;
                                    @endphp

                                    @foreach ($kelompokKrs as $kelompok)
                                        @continue($kelompok['items']->isEmpty())
                                        <tr class="table-{{ $kelompok['warna'] }}">
                                            <td colspan="6" class="py-3">
                                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                    <span class="fw-bold text-{{ $kelompok['warna'] }}">
                                                        <i class="bx {{ $kelompok['ikon'] }} me-1"></i>
                                                        {{ $kelompok['judul'] }}
                                                    </span>
                                                    <span class="badge bg-{{ $kelompok['warna'] }}">
                                                        {{ $kelompok['items']->count() }} Mata Kuliah &bull;
                                                        {{ $kelompok['items']->sum(fn ($item) => (int) ($item->mataKuliah?->sks ?? 0)) }} SKS
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>

                                        @foreach ($kelompok['items'] as $item)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="krs[]" value="{{ $item->kurikulum_id }}"
                                                        data-krs-id="{{ $item->krs_id ?? '' }}"
                                                        data-sks="{{ (int) ($item->mataKuliah?->sks ?? 0) }}">
                                                </td>
                                                <td>{{ $nomor++ }}</td>
                                                <td class="fw-semibold">{{ $item->mataKuliah->nama }}</td>
                                                <td>{{ $item->mataKuliah->sks }}</td>
                                                <td>{{ $item->mataKuliah->smt }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $kelompok['warna'] }}">
                                                        {{ (int) $item->mataKuliah->kategori_mk === 0 ? 'Wajib' : ((int) $item->mataKuliah->kategori_mk === 1 ? 'Pilihan' : 'Tidak Diketahui') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @else
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        @if ($hasExistingKrs)
                                            <i class="bx bx-check-circle text-success fs-3 d-block mb-2"></i>
                                            <strong class="text-success">KRS Sudah Diambil</strong>
                                        @else
                                            Tidak ada mata kuliah tersedia untuk semester ini.
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </form>
                    <div class="mt-3">
                        <button id="saveKrs" class="btn btn-primary">Simpan KRS</button>
                    </div>
                    <div class="mt-2">
                        <strong>Total SKS: <span id="totalSks">0</span></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
</div>
@endsection

@push('script')
<script>
    $(document).on('change', 'input[name="krs[]"]', function () {
        let totalSks = 0;
        $('input[name="krs[]"]:checked').each(function () {
            totalSks += Number($(this).data('sks')) || 0;
        });
        $('#totalSks').text(totalSks);
    });
</script>
@endpush
