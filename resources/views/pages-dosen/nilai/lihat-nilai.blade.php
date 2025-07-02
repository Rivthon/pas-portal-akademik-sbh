@extends('layouts.dosen')

@section('title', 'Daftar Mahasiswa')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card p-4 shadow-sm mb-5">
        <div class="row g-4 align-items-center">
            <!-- Content Section -->
            <div class="col-md-7">
                <div class="card-body">
                    <h5 class="card-title text-primary fw-bold mb-3">Input Nilai Mahasiswa</h5>
                    <p class="text-muted" style="line-height: 1.6;">
                        Pastikan Anda telah memilih program studi dan mata kuliah yang bersangkutan.
                        Setelah memilih mata kuliah, daftar mahasiswa yang mengambil mata kuliah tersebut akan muncul.
                    </p>
                </div>
            </div>
            <!-- Image Section -->
            <div class="col-md-5 text-center">
                <img src="{{ asset('assets/img/illustrations/nilai.png') }}" class="img-fluid" alt="Illustration"
                    style="max-height: 200px;">
            </div>
        </div>
    </div>
    <div class="row g-4">
        @forelse ($mahasiswaList as $mahasiswa)
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body text-center">

                    <div class="mx-auto mb-3">
                        <div class="avatar avatar-xl">
                            {{-- Cek jika mahasiswa punya avatar, jika tidak, gunakan UI Avatars --}}
                            @if(isset($mahasiswa->avatar) && $mahasiswa->avatar)
                            <img src="{{ asset('storage/' . $mahasiswa->avatar) }}" alt="{{ $mahasiswa->nama }}"
                                class="rounded-circle">
                            @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($mahasiswa->nama) }}&background=random&color=fff&size=128"
                                alt="{{ $mahasiswa->nama }}" class="rounded-circle">
                            @endif
                        </div>
                    </div>

                    <h5 class="mb-1 card-title">{{ $mahasiswa->nama }}</h5>
                    <p class="text-muted">{{ $mahasiswa->nim }}</p>

                    <a href="{{ route('dosen.mahasiswa.transkrip', ['mahasiswa' => $mahasiswa->mahasiswa_id]) }}"
                        class="btn btn-primary d-flex align-items-center justify-content-center">
                        <i class="bx bx-show me-1"></i>
                        <span>Lihat Nilai</span>
                    </a>

                </div>
            </div>
        </div>
        @empty
        {{-- Tampilan jika tidak ada mahasiswa ditemukan --}}
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <h6 class="alert-heading mb-1">Informasi</h6>
                <span>Tidak ada data mahasiswa yang dapat ditampilkan.</span>
            </div>
        </div>
        @endforelse
    </div>
    <div class="d-flex justify-content-center mt-4">
        {{ $mahasiswaList->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
    </div>

</div>
@endsection