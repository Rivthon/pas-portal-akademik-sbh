@extends('layouts.mahasiswa')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- User Profile Header -->
    <div class="col-12">
        <div class="card mb-4">
            <div class="user-profile-header-banner">
                <img src="{{ asset('dashboard_assets/assets/img/front-pages/backgrounds/cta-bg-light.png') }}"
                    alt="Banner image" class="rounded-top w-100">
            </div>
            <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                    <img src="{{ auth('mahasiswa')->user()->getProfileImageURL() }}"
                        onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'"
                        alt="Avatar of {{ auth('mahasiswa')->user()->name }}"
                        class="d-block h-100 ms-0 ms-sm-4 rounded user-profile-img bg-light shadow-sm"
                        id="avatar-profile">
                </div>
                <div class="flex-grow-1 mt-3 mt-sm-5">
                    <div
                        class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                        <div class="user-profile-info">
                            <h4>{{ auth()->user()->name }}</h4>
                            <ul
                                class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                {{-- <li class="list-inline-item fw-medium" style="text-transform: capitalize;">
                                    <i class='bx bx-pen'></i> @foreach (auth()->user()->getRoleNames() as $role)
                                    {{ $role }}
                                    @endforeach
                                </li> --}}
                                {{-- <li class="list-inline-item fw-medium">
                                    <i class='bx bx-calendar-alt'></i> Terdaftar {{
                                    auth()->user()->created_at?->translatedFormat('d F Y') }}
                                </li> --}}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Profile Header -->

    <!-- Edit Profile Form -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Biodata Mahasiswa</h5>
            </div>
            @if ($errors->any())
            <div class="alert alert-danger">
                <h6>Terjadi kesalahan:</h6>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="card-body">
                <form action="{{ route('mahasiswa.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Data Akun -->
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0"><i class="bx bx-user-circle me-2"></i>Data Akun</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Nama -->
                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama" id="nama" class="form-control"
                                        value="{{ old('nama', $mahasiswa->nama) }}" required>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="email" class="form-control"
                                        value="{{ old('email', $mahasiswa->email) }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Password -->
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password Baru</label>
                                    <input type="password" name="password" id="password" class="form-control">
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                                </div>

                                <!-- Konfirmasi Password -->
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control">
                                </div>
                            </div>

                            <!-- Avatar -->
                            <div class="mb-3">
                                <label for="avatar" class="form-label">Foto Profil</label>
                                <input type="file" name="avatar" id="avatar" class="form-control">
                                <small class="text-muted">Format: JPG/PNG, Maks 500KB.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Biodata -->
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0"><i class="bx bx-id-card me-2"></i>Biodata</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- NISN -->
                                <div class="col-md-6 mb-3">
                                    <label for="nisn" class="form-label">NISN</label>
                                    <input type="text" class="form-control" id="nisn" name="nisn"
                                        value="{{ old('nisn', $mahasiswa->nisn) }}">
                                </div>

                                <!-- NIK -->
                                <div class="col-md-6 mb-3">
                                    <label for="nik" class="form-label">NIK</label>
                                    <input type="text" class="form-control" id="nik" name="nik"
                                        value="{{ old('nik', $mahasiswa->nik) }}">
                                </div>
                            </div>

                            <div class="row">
                                <!-- Jenis Kelamin -->
                                <div class="col-md-6 mb-3">
                                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                        <option value="">Pilih</option>
                                        <option value="Laki-laki" {{ old('jenis_kelamin', $mahasiswa->jenis_kelamin) ==
                                            'Laki-laki'
                                            ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('jenis_kelamin', $mahasiswa->jenis_kelamin) ==
                                            'Perempuan'
                                            ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>

                                <!-- Tanggal Lahir -->
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                                        value="{{ old('tanggal_lahir', $mahasiswa->tanggal_lahir) }}" required>
                                </div>
                            </div>

                            <!-- Alamat -->
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3"
                                    required>{{ old('alamat', $mahasiswa->alamat) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Data Akademik & Data Pribadi (biarkan seperti sebelumnya dengan format yang sama) -->
                    <!-- Data Akademik -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">Data Akademik</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Jurusan -->
                                <div class="col-md-6 mb-3">
                                    <label for="jurusan_id" class="form-label">Jurusan</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-book"></i></span>
                                        <select class="form-select" id="jurusan_id" name="jurusan_id" required>
                                            <option value="">Pilih Jurusan</option>
                                            @foreach($programStudi as $j)
                                            <option value="{{ $j->jurusan_id }}" {{ old('jurusan_id', $mahasiswa->
                                                jurusan_id) ==
                                                $j->jurusan_id ? 'selected' : '' }}>
                                                {{ $j->nama }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <!-- NIM -->
                                <div class="col-md-6 mb-3">
                                    <label for="nim" class="form-label">NIM</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                        <input type="text" class="form-control" id="nim" name="nim"
                                            value="{{ old('nim', $mahasiswa->nim) }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Semester -->
                                <div class="col-md-6 mb-3">
                                    <label for="semester" class="form-label">Semester</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                        <select class="form-select" id="semester" name="semester" required>
                                            <option value="">Pilih Semester</option>
                                            @for ($i = 1; $i <= 8; $i++) <option value="{{ $i }}" {{ old('semester',
                                                $mahasiswa->
                                                semester) == $i ? 'selected' : '' }}>
                                                Semester {{ $i }}
                                                </option>
                                                @endfor
                                        </select>
                                    </div>
                                </div>
                                <!-- Tahun Masuk -->
                                <div class="col-md-6 mb-3">
                                    <label for="tahun_masuk" class="form-label">Tahun Masuk</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-calendar-event"></i></span>
                                        <input type="number" class="form-control" id="tahun_masuk" name="tahun_masuk"
                                            value="{{ old('tahun_masuk', $mahasiswa->tahun_masuk) }}" min="2000"
                                            max="{{ date('Y') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Status Mahasiswa -->
                                <div class="col-md-6 mb-3">
                                    <label for="status_mhs" class="form-label">Status Mahasiswa</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-user-check"></i></span>
                                        <select class="form-select" id="status_mhs" name="status_mhs" required>
                                            <option value="">Pilih Status</option>
                                            <option value="aktif" {{ old('status_mhs', $mahasiswa->status_mhs) ==
                                                'aktif' ?
                                                'selected' : '' }}>Aktif</option>
                                            <option value="nonaktif" {{ old('status_mhs', $mahasiswa->status_mhs) ==
                                                'nonaktif' ?
                                                'selected' : '' }}>Nonaktif</option>
                                            <option value="lulus" {{ old('status_mhs', $mahasiswa->status_mhs) ==
                                                'lulus' ?
                                                'selected' : '' }}>Lulus</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kelas" class="form-label">Kelas Mahasiswa</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-user-check"></i></span>
                                        <select class="form-select" id="kelas" name="kelas" required>
                                            <option value="">Pilih Status</option>
                                            <option value="karyawan" {{ old('kelas', $mahasiswa->kelas) == 'karyawan' ?
                                                'selected' :
                                                '' }}>karyawan</option>
                                            <option value="pagi" {{ old('kelas', $mahasiswa->kelas) == 'pagi' ?
                                                'selected' : ''
                                                }}>pagi</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Gelombang -->
                                <div class="col-md-6 mb-3">
                                    <label for="gelombang_id" class="form-label">Gelombang</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-layer"></i></span>
                                        <select class="form-select" id="gelombang_id" name="gelombang_id" required>
                                            <option value="">Pilih Gelombang</option>
                                            @foreach($gelombang as $g)
                                            <option value="{{ $g->id }}" {{ old('gelombang_id', $mahasiswa->
                                                gelombang_id) == $g->id
                                                ? 'selected' : ''
                                                }}>
                                                {{ $g->nama }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">Data Pribadi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Nama Ayah -->
                                <div class="col-md-6 mb-3">
                                    <label for="nama_ayah" class="form-label">Nama Ayah / Wali</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                        <input type="text" class="form-control" id="nama_ayah" name="nama_ayah"
                                            value="{{ old('nama_ayah', $mahasiswa->nama_ayah) }}" required>
                                    </div>
                                </div>
                                <!-- Nama Ibu -->
                                <div class="col-md-6 mb-3">
                                    <label for="nama_ibu" class="form-label">Nama Ibu</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                        <input type="text" class="form-control" id="nama_ibu" name="nama_ibu"
                                            value="{{ old('nama_ibu', $mahasiswa->nama_ibu) }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- No Telp Ortu -->
                                <div class="col-md-6 mb-3">
                                    <label for="no_telp_ortu" class="form-label">No Telp Orang Tua</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                        <input type="number" class="form-control" id="no_telp_ortu" name="no_telp_ortu"
                                            value="{{ old('no_telp_ortu', $mahasiswa->no_telp_ortu) }}" required>
                                    </div>
                                </div>
                                <!-- Pendapatan Ortu -->
                                <div class="col-md-6 mb-3">
                                    <label for="pendapatan_ortu" class="form-label">Pendapatan Orang Tua</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-wallet"></i></span>
                                        <select class="form-select" id="pendapatan_ortu" name="pendapatan_ortu"
                                            required>
                                            <option value="">Pilih Pendapatan Orang Tua</option>
                                            <option value="Rp 500.000,00 - Rp 1.000.000,00" {{ old('pendapatan_ortu',
                                                $mahasiswa->
                                                pendapatan_ortu) == 'Rp 500.000,00 - Rp 1.000.000,00' ? 'selected' : ''
                                                }}>
                                                Rp 500.000,00 - Rp 1.000.000,00
                                            </option>
                                            <option value="Rp 1.000.000,00 - Rp 2.000.000,00" {{ old('pendapatan_ortu',
                                                $mahasiswa->
                                                pendapatan_ortu) == 'Rp 1.000.000,00 - Rp 2.000.000,00' ? 'selected' :
                                                '' }}>
                                                Rp 1.000.000,00 - Rp 2.000.000,00
                                            </option>
                                            <option value="Rp 2.000.000,00 - Rp 3.500.000,00" {{ old('pendapatan_ortu',
                                                $mahasiswa->
                                                pendapatan_ortu) == 'Rp 2.000.000,00 - Rp 3.500.000,00' ? 'selected' :
                                                '' }}>
                                                Rp 2.000.000,00 - Rp 3.500.000,00
                                            </option>
                                            <option value="Rp 3.500.000,00" {{ old('pendapatan_ortu', $mahasiswa->
                                                pendapatan_ortu)
                                                == 'Rp 3.500.000,00' ? 'selected' : '' }}>
                                                Rp 3.500.000,00
                                            </option>
                                            <option value="Rp 5.000.000,00 - Rp 7.000.000,00" {{ old('pendapatan_ortu',
                                                $mahasiswa->
                                                pendapatan_ortu) == 'Rp 5.000.000,00 - Rp 7.000.000,00' ? 'selected' :
                                                '' }}>
                                                Rp 5.000.000,00 - Rp 7.000.000,00
                                            </option>
                                            <option value="Rp 7.000.000,00 - Rp 10.000.000,00" {{ old('pendapatan_ortu',
                                                $mahasiswa->
                                                pendapatan_ortu) == 'Rp 7.000.000,00 - Rp 10.000.000,00' ? 'selected' :
                                                '' }}>
                                                Rp 7.000.000,00 - Rp 10.000.000,00
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Alamat Ortu -->
                                <div class="col-md-12 mb-3">
                                    <label for="alamat_ortu" class="form-label">Alamat Orang Tua</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-home"></i></span>
                                        <textarea class="form-control" id="alamat_ortu" name="alamat_ortu" rows="3"
                                            required>{{ old('alamat_ortu', $mahasiswa->alamat_ortu) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Tombol Submit -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bx bx-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Edit Profile Form -->
</div>
{{--
<h3>Profil Saya</h3>

@if (session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<form action="{{ route('mahasiswa.profile.update') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">Nama</label>
        <input type="text" name="nama" id="nama" class="form-control" value="{{ $mahasiswa->nama }}" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control" value="{{ $mahasiswa->email }}" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password Baru (Opsional)</label>
        <input type="password" name="password" id="password" class="form-control">
    </div>
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
</form> --}}
@endsection