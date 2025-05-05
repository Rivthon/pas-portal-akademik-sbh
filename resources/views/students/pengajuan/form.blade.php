@extends('layouts.mahasiswa')
@section('title', 'Form Pengajuan Transkrip')
@section('content')

<div class="card">
    <div class="card-header">
        <h4 class="card-title">Form Pengajuan Transkrip</h4>
    </div>
    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('mahasiswa.pengajuan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Pilihan Jenis Transkrip -->
            <div class="mb-3">
                <label for="jenis" class="form-label">Jenis Transkrip:</label>
                <select name="jenis" id="jenis" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="sementara">Transkrip Sementara</option>
                    {{-- <option value="akhir">Transkrip Akhir</option> --}}
                </select>
            </div>

            <!-- Keperluan Pengajuan -->
            <div class="mb-3">
                <label for="keperluan" class="form-label">Keperluan Pengajuan:</label>
                <input type="text" name="keperluan" id="keperluan" class="form-control"
                    placeholder="Contoh: Seminar Usulan Penelitian" required>
            </div>

            <!-- Upload Bukti (opsional) -->
            <div class="mb-3">
                <label for="bukti" class="form-label">Upload Bukti (opsional):</label>
                <input type="file" name="bukti" id="bukti" class="form-control" accept="image/*">
            </div>

            <!-- Catatan -->
            {{-- <div class="mb-3">
                <label for="catatan" class="form-label">Catatan (opsional):</label>
                <textarea name="catatan" id="catatan" class="form-control" rows="3"
                    placeholder="Tambahkan catatan jika diperlukan"></textarea>
            </div> --}}

            <div class="d-flex justify-content-between">
                <a href="{{ route('mahasiswa.pengajuan.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Ajukan Transkrip</button>
            </div>

        </form>
    </div>
</div>

@endsection