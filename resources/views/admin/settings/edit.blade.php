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
@endsection