@extends('layouts.master')
@section('title', 'Edit Pedoman Akademik')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.pedoman-akademik.index') }}" class="btn btn-sm btn-label-secondary">
        <i class="bx bx-arrow-back me-1"></i>Kembali
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0"><i class="bx bx-edit text-primary me-2"></i>Edit Pedoman Akademik</h5>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form action="{{ route('admin.pedoman-akademik.update', $pedoman) }}" method="POST" enctype="multipart/form-data" class="row g-3">
            @csrf @method('PUT')
            <div class="col-md-7">
                <label class="form-label">Judul Pedoman</label>
                <input type="text" name="judul" value="{{ old('judul', $pedoman->judul) }}" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Tahun Berlaku</label>
                <input type="text" name="tahun_berlaku" value="{{ old('tahun_berlaku', $pedoman->tahun_berlaku) }}" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">File saat ini</label>
                <div class="border rounded p-3 bg-light"><i class="bx bxs-file-pdf text-danger me-2"></i>{{ $pedoman->nama_file }}</div>
            </div>
            <div class="col-12">
                <label class="form-label">Upload PDF pengganti <span class="text-muted">(opsional)</span></label>
                <input type="file" name="file" class="form-control" accept="application/pdf,.pdf">
                <small class="text-muted">Kosongkan jika tidak ingin mengganti file. Maksimal 20 MB.</small>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" value="1" id="statusPedoman"
                        @checked(old('status', $pedoman->status))>
                    <label class="form-check-label" for="statusPedoman">Aktif dan tampilkan kepada mahasiswa</label>
                </div>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
