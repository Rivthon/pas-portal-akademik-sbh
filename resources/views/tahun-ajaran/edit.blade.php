@extends('layouts.master')
@section('content')
<div class="row">
    <div class="col-lg-12 col-md-12 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Tahun Ajaran</h5>
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
                <form action="{{ route('admin.tahun-ajaran.update', $tahunAjaran->ta_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="nama">Nama Tahun Ajaran</label>
                        <input type="text" id="nama" name="nama" class="form-control"
                            value="{{ old('nama', $tahunAjaran->nama) }}" required>
                        @error('nama')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <input type="text" id="semester" name="semester" class="form-control"
                            value="{{ old('semester', $tahunAjaran->semester) }}" required>
                        @error('semester')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Buttons -->
                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan
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