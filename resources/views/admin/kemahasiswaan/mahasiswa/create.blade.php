@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-lg-12">
        <h2>Tambah Mahasiswa Baru</h2>
    </div>
</div>

{{-- Alert error global --}}
@if ($errors->any())
<div class="alert alert-danger">
    <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda:<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('admin.mahasiswa.store') }}" method="POST">
    @csrf
    <div class="row">
        {{-- Nama --}}
        <div class="col-md-6 mb-3">
            <label for="nama" class="form-label"><strong>Nama</strong></label>
            <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror"
                placeholder="Masukkan nama lengkap" value="{{ old('nama') }}">
            @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="col-md-6 mb-3">
            <label for="email" class="form-label"><strong>Email</strong></label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                placeholder="Masukkan email" value="{{ old('email') }}">
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        {{-- Password --}}
        <div class="col-md-6 mb-3">
            <label for="password" class="form-label"><strong>Password</strong></label>
            <input type="password" name="password" id="password"
                class="form-control @error('password') is-invalid @enderror" placeholder="Masukkan password">
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- NIM --}}
        <div class="col-md-6 mb-3">
            <label for="nim" class="form-label"><strong>NIM</strong></label>
            <input type="text" name="nim" id="nim" class="form-control @error('nim') is-invalid @enderror"
                placeholder="Masukkan NIM" value="{{ old('nim') }}">
            @error('nim')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        {{-- Program Studi --}}
        <div class="col-md-6 mb-3">
            <label for="jurusan_id" class="form-label"><strong>Program Studi</strong></label>
            <select name="jurusan_id" id="jurusan_id"
                class="form-select @error('jurusan_id') is-invalid @enderror">
                <option value="">-- Pilih Program Studi --</option>
                @foreach ($programStudi as $program)
                <option value="{{ $program->jurusan_id }}" {{ old('jurusan_id')==$program->jurusan_id
                    ? 'selected' : '' }}>
                    {{ $program->nama }}
                </option>
                @endforeach
            </select>
            @error('jurusan_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="kelas" class="form-label"><strong>Kelas</strong></label>
            <select name="kelas" id="kelas" class="form-select @error('kelas') is-invalid @enderror">
                <option value="">-- Pilih Kelas --</option>
                <option value="pagi" @selected(old('kelas') === 'pagi')>Reguler A</option>
                <option value="karyawan" @selected(old('kelas') === 'karyawan')>Reguler B</option>
            </select>
            @error('kelas')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Tombol submit --}}
    <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
        </button>
    </div>
</form>

<p class="text-center text-primary mt-4"><small>Absensi Online</small></p>
@endsection
