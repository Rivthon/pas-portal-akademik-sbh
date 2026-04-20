@extends('layouts.dosen')

@section('title', 'Daftar Mahasiswa Bimbingan Akademik')

@section('content')
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">
        {{-- Hero Card --}}
        <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col-md-7 text-white p-3">
                        <h4 class="card-title mb-3 fw-bold text-white"><i class="bx bx-group me-2"></i>Mahasiswa Bimbingan Akademik</h4>
                        <p class="mb-0 text-white-50" style="line-height: 1.6;">
                            Berikut adalah daftar mahasiswa yang berada di bawah bimbingan akademik (PA) Anda.<br/>
                            Anda dapat memantau profil, melihat detail nilai, dan transkrip akademik mereka.
                        </p>
                    </div>
                    <div class="col-md-5 text-center d-none d-md-block">
                        <img src="{{ asset('assets/img/illustrations/undraw_studying_re_deca.svg') }}" class="img-fluid" onerror="this.src='{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}'" alt="Illustration" style="max-height: 150px; opacity: 0.9;">
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white pt-4 pb-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <h5 class="mb-3 mb-md-0 fw-bold text-dark"><i class="bx bx-list-ul text-primary me-2"></i>Daftar Anak Didik</h5>
                
                {{-- Form Search --}}
                <form action="{{ route('dosen.nilai-dosen.lihat') }}" method="GET" class="d-flex" style="max-width: 400px; width: 100%;">
                    <div class="input-group input-group-merge shadow-sm rounded-pill border">
                        <span class="input-group-text bg-white border-0 rounded-pill-start"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" class="form-control border-0 rounded-pill-end ps-0" placeholder="Cari nama atau NIM..." value="{{ old('search', $search) }}">
                        @if($search)
                            <a href="{{ route('dosen.nilai-dosen.lihat') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center rounded-pill-end" style="border: none; padding-right: 1.5rem;">
                                <i class="bx bx-x"></i>
                            </a>
                        @else
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill-end px-3">Cari</button>
                        @endif
                    </div>
                </form>
            </div>
            
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px;">#</th>
                            <th style="min-width: 250px;">BIMBINGAN / INFO</th>
                            <th class="text-center">PRODI & SMT</th>
                            <th class="text-center">KONTAK (HP)</th>
                            <th class="text-center">STATUS</th>
                            <th class="text-center" style="width: 150px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($mahasiswaList as $index => $mahasiswa)
                            <tr>
                                <td class="text-center">{{ $mahasiswaList->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md me-3">
                                            @if($mahasiswa->avatar)
                                                <img src="{{ asset('storage/' . $mahasiswa->avatar) }}" alt="Avatar" class="rounded-circle object-fit-cover" style="width: 40px; height: 40px;">
                                            @else
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($mahasiswa->nama) }}&background=696cff&color=fff" alt="Avatar" class="rounded-circle">
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $mahasiswa->nama }}</h6>
                                            <span class="text-muted d-block" style="font-size: 0.8rem;"><i class="bx bx-id-card me-1"></i>{{ $mahasiswa->nim }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="d-block fw-semibold text-primary" style="font-size: 0.85rem;">{{ $mahasiswa->programStudi->nama_program_studi ?? '-' }}</span>
                                    <span class="badge bg-label-info mt-1">Semester {{ $mahasiswa->semester }}</span>
                                </td>
                                <td class="text-center text-muted">
                                    <span style="font-size: 0.85rem;"><i class="bx bx-phone me-1"></i>{{ $mahasiswa->no_telp ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if(strtolower($mahasiswa->status_mhs) == 'aktif')
                                        <span class="badge bg-success shadow-sm">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary shadow-sm">{{ ucfirst($mahasiswa->status_mhs) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('dosen.mahasiswa.transkrip', ['mahasiswa' => $mahasiswa->mahasiswa_id]) }}" class="btn btn-sm btn-primary rounded-pill shadow-sm px-3">
                                        <i class="bx bx-show me-1"></i> Transkrip
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    @if(isset($search) && $search != '')
                                        <div class="text-muted mb-2"><i class="bx bx-search fs-1"></i></div>
                                        <h6 class="fw-semibold">Tidak ditemukan</h6>
                                        <p class="text-muted mb-0">Mahasiswa bimbingan dengan kata kunci "{{ $search }}" tidak ditemukan.</p>
                                    @else
                                        <div class="text-muted mb-2"><i class="bx bx-group fs-1"></i></div>
                                        <h6 class="fw-semibold">Belum Ada Bimbingan</h6>
                                        <p class="text-muted mb-0">Anda saat ini belum ditugaskan sebagai dosen Pembimbing Akademik (PA) untuk mahasiswa manapun.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white border-top border-0 pt-4 pb-2">
                <div class="d-flex justify-content-center">
                    {{ $mahasiswaList->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection