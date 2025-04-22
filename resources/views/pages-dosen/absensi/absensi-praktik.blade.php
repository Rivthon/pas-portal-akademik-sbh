@extends('layouts.dosen')
@section('title', 'Lihat Absensi Praktik')
@section('content')
<div class="container">
    <!-- Info Pertemuan -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title text-primary fw-bold mb-4">
                <i class="bx bxs-book"></i> Informasi Absensi Praktik
            </h4>
            <div class="row">
                <!-- Kiri: Info Mata Kuliah -->
                <div class="col-lg-8">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Mata Kuliah:</strong> {{ $pertemuan->jadwal->kurikulum->mataKuliah->nama }}
                        </li>
                        <li class="list-group-item">
                            <strong>📅 Tanggal:</strong> {{ $pertemuan->tanggal_pertemuan }}
                        </li>
                        <li class="list-group-item">
                            <strong>📖 Topik:</strong> {{ $pertemuan->topik }}
                        </li>
                        <li class="list-group-item">
                            <strong>📑 Sub Topik:</strong> {{ $pertemuan->sub_topik }}
                        </li>
                        <li class="list-group-item">
                            <strong>⏰ Jam Mengajar:</strong>
                            <span class="badge bg-success p-2">
                                {{ $pertemuan->jam_mulai }} - {{ $pertemuan->jam_selesai }}
                                ({{
                                \Carbon\Carbon::parse($pertemuan->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($pertemuan->jam_selesai))
                                }} menit)
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- Kanan: Ilustrasi -->
                <div class="col-lg-4 text-center">
                    <img src="{{ asset('assets/img/illustrations/calender.png') }}" class="img-fluid"
                        alt="Jadwal Ilustrasi" style="max-height: 150px;">
                </div>
            </div>
        </div>
    </div>
    <!-- Form Tambah Mahasiswa yang Tidak Ada di Absensi -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Tambah Mahasiswa ke Absensi Praktik</h5>
            <form id="formTambahMahasiswa">
                @csrf
                <div class="row g-2">
                    <div class="col-md-8">
                        <select class="form-select select2" id="selectMahasiswa" name="mahasiswa_id">
                            <option value="">Pilih Mahasiswa</option>
                            @foreach ($mahasiswaTambahan as $mhs)
                            <option value="{{ $mhs->mahasiswa_id }}">{{ $mhs->nama }} - Semester {{ $mhs->semester }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-success w-100" id="btnTambahMahasiswa">
                            <i class="bx bx-user-plus"></i> Tambah ke Absensi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Form Absensi -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form id="praktikForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pertemuan_praktik_id" value="{{ $pertemuan->pertemuan_praktik_id }}">
                <div class="d-flex justify-content-between mb-3">
                    <button type="button" class="btn btn-success" id="markAllPresent">
                        <i class="bx bx-check-circle"></i> Tandai Semua Hadir
                    </button>
                    <button type="button" class="btn btn-secondary" id="resetAbsensi">
                        <i class="bx bx-refresh"></i> Reset Absensi
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Nama Mahasiswa</th>
                                <th>Semester</th>
                                <th class="text-center">Hadir</th>
                                <th class="text-center">Izin</th>
                                <th class="text-center">Sakit</th>
                                <th class="text-center">Alpha</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($absensi as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->mahasiswa->nama }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $item->mahasiswa->semester }}</span>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input status-absen" type="radio"
                                        name="status[{{ $item->mahasiswa_id }}]" value="hadir" {{ $item->status ==
                                    'hadir' ? 'checked' : '' }} required>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input status-absen" type="radio"
                                        name="status[{{ $item->mahasiswa_id }}]" value="izin" {{ $item->status == 'izin'
                                    ? 'checked' : '' }}>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input status-absen" type="radio"
                                        name="status[{{ $item->mahasiswa_id }}]" value="sakit" {{ $item->status ==
                                    'sakit' ? 'checked' : '' }}>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input status-absen" type="radio"
                                        name="status[{{ $item->mahasiswa_id }}]" value="tidak hadir" {{ $item->status ==
                                    'tidak hadir' ? 'checked' : '' }}>
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="keterangan[{{ $item->mahasiswa_id }}]"
                                        value="{{ $item->keterangan ?? '' }}" placeholder="Opsional">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3">
                    <i class="bx bx-save"></i> Simpan Absensi
                </button>
            </form>
        </div>
    </div>
</div>

<script>

</script>

@endsection