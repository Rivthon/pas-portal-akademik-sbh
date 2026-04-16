@extends('layouts.master')
@section('title', 'Edit Data Dosen')
@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h4>Edit Data Mahasiswa</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.mahasiswa.update', $mahasiswa->mahasiswa_id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="container mt-2">
                    <h5>
                        Data Akademik
                    </h5>
                    <hr>
                    <div class="row g-3">
                        <!-- Kolom Kiri -->
                        <div class="col-md-6">
                            <!-- Nama Lengkap -->
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-user"></i></span>
                                    <input type="text" class="form-control" id="nama" name="nama"
                                        value="{{ old('nama', $mahasiswa->nama) }}" required>
                                </div>
                                @error('nama')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ old('email', $mahasiswa->email) }}" required>
                                </div>
                                @error('email')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- NIM -->
                            <div class="mb-3">
                                <label for="nim" class="form-label">NIM</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                    <input type="text" class="form-control" id="nim" name="nim"
                                        value="{{ old('nim', $mahasiswa->nim) }}" required>
                                </div>
                                @error('nim')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Tempat Lahir -->
                            <div class="mb-3">
                                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-map"></i></span>
                                    <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir"
                                        value="{{ old('tempat_lahir', $mahasiswa->tempat_lahir) }}" required>
                                </div>
                                @error('tempat_lahir')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-md-6">
                            <!-- Tanggal Lahir -->
                            <div class="mb-3">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                                        value="{{ old('tanggal_lahir', $mahasiswa->tanggal_lahir) }}" required>
                                </div>
                                @error('tanggal_lahir')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Jenis Kelamin -->
                            <div class="mb-3">
                                <label for="jk" class="form-label">Jenis Kelamin</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-gender"></i></span>
                                    <select class="form-select" id="jk" name="jk" required>
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L" {{ old('jk', $mahasiswa->jk) == 'L' ? 'selected' : ''
                                            }}>Laki-laki</option>
                                        <option value="P" {{ old('jk', $mahasiswa->jk) == 'P' ? 'selected' : ''
                                            }}>Perempuan</option>
                                    </select>
                                </div>
                                @error('jk')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Nomor HP -->
                            <div class="mb-3">
                                <label for="hp" class="form-label">Nomor HP</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                    <input type="text" class="form-control" id="hp" name="hp"
                                        value="{{ old('hp', $mahasiswa->hp) }}" required>
                                </div>
                                @error('hp')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Alamat -->
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-home"></i></span>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3"
                                        required>{{ old('alamat', $mahasiswa->alamat) }}</textarea>
                                </div>
                                @error('alamat')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection