@extends('layouts.mahasiswa')
@section('title', 'Formulir Permintaan Bantuan')

@section('content')
<div class="container">
    <h3 class="mb-4">Formulir Permintaan Bantuan / Saran</h3>

    <div class="card">
        <div class="card-body">

            <form id="form-permintaan" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="jenis_permintaan" class="form-label">
                                <i class="bx bxs-category"></i> Jenis Permintaan
                            </label>
                            <select name="jenis_permintaan" id="jenis_permintaan" class="form-select" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="bug">Laporan Bug</option>
                                <option value="fitur">Permintaan Fitur</option>
                                <option value="akses">Kesulitan Akses</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="judul" class="form-label">
                                <i class="bx bxs-edit"></i> Judul Permintaan
                            </label>
                            <input type="text" name="judul" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="prioritas" class="form-label">
                                <i class="bx bxs-flag"></i> Prioritas
                            </label>
                            <select name="prioritas" class="form-select" required>
                                <option value="">-- Pilih Prioritas --</option>
                                <option value="rendah">Rendah</option>
                                <option value="sedang">Sedang</option>
                                <option value="tinggi">Tinggi</option>
                                <option value="urgen">Sangat Mendesak</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">
                                <i class="bx bxs-message-detail"></i> Deskripsi
                            </label>
                            <textarea name="deskripsi" class="form-control" rows="5" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="file_lampiran" class="form-label">
                                <i class="bx bxs-file"></i> File Lampiran (Opsional)
                            </label>
                            <input type="file" name="file_lampiran" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        <i class="bx bxs-left-arrow"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bxs-send"></i> Kirim Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection