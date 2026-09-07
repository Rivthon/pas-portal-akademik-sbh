@extends('layouts.master')
@section('title', 'Edit Mahasiswa')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Mahasiswa /</span> Perbarui Data</h4>
        </div>
    </div>

    <form action="{{ route('admin.mahasiswa.update', $mahasiswa->mahasiswa_id) }}" method="POST">
        @csrf
        @method('PUT')
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <!-- Biodata -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Biodata</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Nama Lengkap -->
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                            <input type="text" class="form-control" id="nama" name="nama"
                                value="{{ old('nama', $mahasiswa->nama) }}" required>
                        </div>
                    </div>
                    <!-- Email -->
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email', $mahasiswa->email) }}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- NISN -->
                    <div class="col-md-6 mb-3">
                        <label for="nisn" class="form-label">NISN</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                            <input type="text" class="form-control" id="nisn" name="nisn"
                                value="{{ old('nisn', $mahasiswa->nisn) }}">
                        </div>
                    </div>
                    <!-- NIK -->
                    <div class="col-md-6 mb-3">
                        <label for="nik" class="form-label">NIK</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                            <input type="text" class="form-control" id="nik" name="nik"
                                value="{{ old('nik', $mahasiswa->nik) }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Jenis Kelamin -->
                    <div class="col-md-6 mb-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih</option>
                            <option value="Laki-Laki" {{ old('jenis_kelamin', $mahasiswa->jenis_kelamin) ==
                                'Laki-Laki' ? 'selected' : ''
                                }}>Laki-Laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin', $mahasiswa->jenis_kelamin) ==
                                'Perempuan' ? 'selected' : ''
                                }}>Perempuan</option>
                        </select>
                    </div>

                    <!-- Tanggal Lahir -->
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                                value="{{ old('tanggal_lahir', $mahasiswa->tanggal_lahir) }}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Alamat Ortu -->
                    <div class="col-md-12 mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-home"></i></span>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"
                                required>{{ old('alamat', $mahasiswa->alamat) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                <option value="{{ $j->jurusan_id }}" {{ old('jurusan_id', $mahasiswa->jurusan_id) ==
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
                                @for ($i = 1; $i <= 8; $i++) <option value="{{ $i }}" {{ old('semester', $mahasiswa->
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
                                <option value="aktif" {{ old('status_mhs', $mahasiswa->status_mhs) == 'aktif' ?
                                    'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ old('status_mhs', $mahasiswa->status_mhs) == 'nonaktif' ?
                                    'selected' : '' }}>Nonaktif</option>
                                <option value="lulus" {{ old('status_mhs', $mahasiswa->status_mhs) == 'lulus' ?
                                    'selected' : '' }}>Lulus</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="kelas" class="form-label">Kelas Mahasiswa</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-user-check"></i></span>
                            <select class="form-select" id="kelas" name="kelas" required>
                                <option value="">Pilih Kelas</option>
                                <option value="karyawan" {{ old('kelas', $mahasiswa->kelas) == 'karyawan' ? 'selected' :
                                    '' }}>Reguler B</option>
                                <option value="pagi" {{ in_array(strtolower((string) old('kelas', $mahasiswa->kelas)), ['pagi', 'reguler', 'regular'], true) ? 'selected' : ''
                                    }}>Reguler A</option>
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
                                <option value="{{ $g->id }}" {{ old('gelombang_id', $mahasiswa->gelombang_id) == $g->id
                                    ? 'selected' : '' }}>
                                    {{ $g->nama }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="dosen_id" class="form-label">Pilih Dosen</label>
                        <div class="input-group">
                            {{-- <span class="input-group-text"><i class="bx bx-user"></i></span> --}}
                            <select class="form-select select2" id="dosen_id" name="dosen_id" required>
                                <option value="">-- Cari & Pilih Dosen --</option>
                                @foreach($dosen as $ds)
                                <option value="{{ $ds->dosen_id }}" {{ old('dosen_id', $mahasiswa->dosen_id ?? '') ==
                                    $ds->dosen_id ? 'selected' :
                                    '' }}>
                                    {{ $ds->nama }}
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
                            <select class="form-select" id="pendapatan_ortu" name="pendapatan_ortu" required>
                                <option value="">Pilih Pendapatan Orang Tua</option>
                                <option value="Rp 500.000,00 - Rp 1.000.000,00" {{ old('pendapatan_ortu', $mahasiswa->
                                    pendapatan_ortu) == 'Rp 500.000,00 - Rp 1.000.000,00' ? 'selected' : '' }}>
                                    Rp 500.000,00 - Rp 1.000.000,00
                                </option>
                                <option value="Rp 1.000.000,00 - Rp 2.000.000,00" {{ old('pendapatan_ortu', $mahasiswa->
                                    pendapatan_ortu) == 'Rp 1.000.000,00 - Rp 2.000.000,00' ? 'selected' : '' }}>
                                    Rp 1.000.000,00 - Rp 2.000.000,00
                                </option>
                                <option value="Rp 2.000.000,00 - Rp 3.500.000,00" {{ old('pendapatan_ortu', $mahasiswa->
                                    pendapatan_ortu) == 'Rp 2.000.000,00 - Rp 3.500.000,00' ? 'selected' : '' }}>
                                    Rp 2.000.000,00 - Rp 3.500.000,00
                                </option>
                                <option value="Rp 3.500.000,00" {{ old('pendapatan_ortu', $mahasiswa->pendapatan_ortu)
                                    == 'Rp 3.500.000,00' ? 'selected' : '' }}>
                                    Rp 3.500.000,00
                                </option>
                                <option value="Rp 5.000.000,00 - Rp 7.000.000,00" {{ old('pendapatan_ortu', $mahasiswa->
                                    pendapatan_ortu) == 'Rp 5.000.000,00 - Rp 7.000.000,00' ? 'selected' : '' }}>
                                    Rp 5.000.000,00 - Rp 7.000.000,00
                                </option>
                                <option value="Rp 7.000.000,00 - Rp 10.000.000,00" {{ old('pendapatan_ortu',
                                    $mahasiswa->pendapatan_ortu) == 'Rp 7.000.000,00 - Rp 10.000.000,00' ? 'selected' :
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

        <div class="d-flex justify-content-between align-items-center mt-4">
            <!-- Tombol Kembali -->
            <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>

            <!-- Tombol Simpan -->
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save"></i> Simpan Perubahan
            </button>
        </div>

    </form>
</div>

@endsection
<script>
    $(function () {
                    $('#dosen_id').select2({
                      placeholder: "Cari nama dosen...",
                      allowClear: true,
                      width: '100%' // penting supaya select2 full lebar di Sneat
                    });
                  });
</script>
