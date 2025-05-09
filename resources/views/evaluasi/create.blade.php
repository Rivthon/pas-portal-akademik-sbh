@extends('layouts.master')
@section('title', 'Evaluasi - Tambah Evaluasi')
@section('content')
<div class="row">
    <div class="col-lg-10 col-md-12 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tambah Evaluasi</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <form action="{{ route('admin.evaluasi.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <!-- Input untuk Nama Evaluasi -->
                        <div class="col-md-12 mb-3">
                            <label for="nama" class="form-label"><strong>Nama:</strong></label>
                            <input type="text" name="nama" id="nama"
                                class="form-control @error('nama') is-invalid @enderror"
                                placeholder="Nama Pertanyaan EDOM" value="{{ old('nama') }}">
                            @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- <div class="col-md-12 mb-3">
                            <label for="kategori" class="form-label"><strong>Kategori:</strong></label>
                            <select name="kategori" id="kategori"
                                class="form-select @error('kategori') is-invalid @enderror">
                                <option value="">Pilih Kategori</option>
                                <option value="teori">Teori</option>
                                <option value="praktik">Praktik</option>
                            </select>
                            @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> --}}
                        <!-- Buttons -->
                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fa-solid fa-floppy-disk"></i> Submit
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection