@extends('layouts.master')
@section('title', isset($dosen) ? 'Edit Dosen' : 'Tambah Dosen')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">{{ isset($dosen) ? 'Edit' : 'Tambah' }} Data Dosen</h4>
            <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <form
                action="{{ isset($dosen) ? route('admin.dosen.update', $dosen->dosen_id) : route('admin.dosen.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($dosen))
                @method('PUT')
                @endif

                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-md-6">
                        <!-- Nama -->
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama<span class="text-danger">*</label>
                            <input type="text" name="nama" id="nama" class="form-control"
                                value="{{ old('nama', $dosen->nama ?? '') }}" required>
                        </div>

                        {{-- <div class="mb-3">
                            <label for="kd_dosen" class="form-label">Kode Dosen</label>
                            <input type="text" name="kd_dosen" id="kd_dosen" class="form-control"
                                value="{{ old('kd_dosen', $dosen->kd_dosen ?? '') }}" required>
                        </div> --}}

                        <!-- Jenis Kelamin -->
                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin<span
                                    class="text-danger">*</label>
                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                                <option value="L" {{ old('jenis_kelamin', $dosen->jenis_kelamin ?? '') == 'L' ?
                                    'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $dosen->jenis_kelamin ?? '') == 'P' ?
                                    'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <!-- Jurusan -->
                        <div class="mb-3">
                            <label for="jurusan_id" class="form-label">Jurusan<span class="text-danger">*</label>
                            <select name="jurusan_id" id="jurusan_id" class="form-select" required>
                                <option value="" disabled selected>Pilih Jurusan</option>
                                @foreach($programStudi as $j)
                                <option value="{{ $j->jurusan_id }}" {{ old('jurusan_id', $dosen->jurusan_id ?? '') ==
                                    $j->jurusan_id ? 'selected' : '' }}>
                                    {{ $j->nama }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email<span class="text-danger">*</label>
                            <input type="email" name="email" id="email" class="form-control"
                                value="{{ old('email', $dosen->email ?? '') }}" required>
                        </div>

                        <!-- Avatar -->
                        <div class="mb-3">
                            <label for="avatar" class="form-label">Avatar</label>
                            <input type="file" name="avatar" id="avatar" class="form-control">
                            @if(isset($dosen) && $dosen->avatar)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $dosen->avatar) }}" alt="Avatar" class="img-thumbnail"
                                    width="150">
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="col-md-6">
                        <!-- NIDN -->
                        <div class="mb-3">
                            <label for="nidn" class="form-label">NIDN <span class="text-danger">*</span></label>
                            <input type="text" name="nidn" id="nidn" class="form-control"
                                value="{{ old('nidn', $dosen->nidn ?? '') }}" required>
                        </div>

                        <!-- Tempat Lahir -->
                        <div class="mb-3">
                            <label for="tempat" class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat" id="tempat" class="form-control"
                                value="{{ old('tempat', $dosen->tempat ?? '') }}">
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control"
                                value="{{ old('tanggal_lahir', isset($dosen) && $dosen->tanggal_lahir ? \Carbon\Carbon::parse($dosen->tanggal_lahir)->format('Y-m-d') : '') }}">
                        </div>

                        <!-- Alamat -->
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-control"
                                rows="3">{{ old('alamat', $dosen->alamat ?? '') }}</textarea>
                        </div>

                        <!-- No Telp -->
                        <div class="mb-3">
                            <label for="no_telp" class="form-label">Nomor Telepon</label>
                            <input type="tel" name="no_telp" id="no_telp" maxlength="13" inputmode="tel" class="form-control"
                                value="{{ old('no_telp', $dosen->no_telp ?? '') }}">
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password<span class="text-danger">*</label>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Masukkan password baru (kosongkan jika tidak ingin mengubah)">
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> {{ isset($dosen) ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
