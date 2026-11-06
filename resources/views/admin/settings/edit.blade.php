@extends('layouts.master')
@section('title', 'Pengaturan Aplikasi')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Edit Pengaturan Aplikasi
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Silakan edit pengaturan aplikasi di sini.
                </p>
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/berkas_img.jpg') }}" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>


    </div>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Aplikasi:</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ $setting->name }}"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo:</label>
                        <input type="file" name="logo" id="logo" class="form-control">
                        @if($setting->logo)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $setting->logo) }}" alt="Logo" class="img-thumbnail"
                                width="100">
                        </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="favicon" class="form-label">Favicon:</label>
                        <input type="file" name="favicon" id="favicon" class="form-control">
                        @if($setting->favicon)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $setting->favicon) }}" alt="Favicon" class="img-thumbnail"
                                width="50">
                        </div>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="footer_name" class="form-label">Footer Name:</label>
                        <input type="text" name="footer_name" id="footer_name" class="form-control"
                            value="{{ $setting->footer_name }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="copyright" class="form-label">Copyright:</label>
                        <textarea name="copyright" id="copyright" class="form-control"
                            required>{{ $setting->copyright }}</textarea>
                    </div>
                </div>
            </div>
            <div class="between">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>


        </form>
    </div>
</div>

@can('website-maintenance-toggle')
<div class="card shadow-sm mt-4 border-{{ $setting->website_down ? 'danger' : 'success' }}">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bx bx-power-off fs-3 text-{{ $setting->website_down ? 'danger' : 'success' }}"></i>
                    <h5 class="mb-0">Website Down</h5>
                    <span class="badge bg-label-{{ $setting->website_down ? 'danger' : 'success' }}">
                        {{ $setting->website_down ? 'AKTIF - WEBSITE DITUTUP' : 'NONAKTIF - WEBSITE ONLINE' }}
                    </span>
                </div>
                <p class="text-muted mb-0">
                    Saat Website Down aktif, mahasiswa, dosen, aplikasi mobile, API, dan pengunjung akan menerima halaman pemeliharaan HTTP 503. Admin tetap dapat masuk untuk mengaktifkan website kembali.
                </p>
            </div>
            <form action="{{ route('admin.settings.website-availability') }}" method="POST"
                onsubmit="return confirm('{{ $setting->website_down ? 'Aktifkan kembali PAS untuk seluruh pengguna?' : 'Yakin ingin menonaktifkan seluruh akses publik PAS?' }}')">
                @csrf
                @method('PATCH')
                <input type="hidden" name="website_down" value="{{ $setting->website_down ? 0 : 1 }}">
                <button type="submit" class="btn btn-{{ $setting->website_down ? 'success' : 'danger' }} px-4">
                    <i class="bx bx-power-off me-1"></i>
                    {{ $setting->website_down ? 'Aktifkan Website' : 'Website Down' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
