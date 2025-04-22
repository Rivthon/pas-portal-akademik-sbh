@extends('layouts.master')
@section('title', 'Form Pengajuan Transkrip')
@section('content')

<div class="card">
    <div class="card-header">
        <h4 class="card-title">Form Edit Pengajuan Transkrip</h4>
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

        <form action="{{ route('admin.pengajuan.update', $pengajuan->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Pilihan Jenis Transkrip -->
            <div class="mb-3">
                <label for="jenis" class="form-label">Jenis Transkrip:</label>
                <select name="jenis" id="jenis" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="sementara" {{ $pengajuan->jenis == 'sementara' ? 'selected' : '' }}>Transkrip
                        Sementara</option>
                    {{-- <option value="akhir" {{ $pengajuan->jenis == 'akhir' ? 'selected' : '' }}>Transkrip Akhir
                    </option> --}}
                </select>
            </div>

            <!-- Keperluan Pengajuan -->
            <div class="mb-3">
                <label for="keperluan" class="form-label">Keperluan Pengajuan:</label>
                <input type="text" name="keperluan" id="keperluan" class="form-control"
                    value="{{ $pengajuan->keperluan }}" placeholder="Contoh: Seminar Usulan Penelitian" required>
            </div>

            <!-- Upload Bukti (opsional) -->
            <div class="mb-3">
                <label for="bukti" class="form-label">Upload Bukti (opsional):</label>
                <input type="file" name="bukti" id="bukti" class="form-control" accept="image/*">
                @if ($pengajuan->bukti)
                <p class="mt-2">Bukti saat ini: <a href="{{ asset('storage/' . $pengajuan->bukti) }}"
                        target="_blank">Lihat Bukti</a></p>
                @endif
            </div>
            <label for="jenis" class="form-label">Status Transkrip:</label>
            <select name="jenis" id="jenis" class="form-select" required>
                <option value="">-- Pilih Status --</option>
                <option value="pending" {{ $pengajuan->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="disetujui" {{ $pengajuan->status == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                <option value="diproses" {{ $pengajuan->status == 'diproses' ? 'selected' : '' }}>Diproses</option>
                <option value="selesai" {{ $pengajuan->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="ditolak" {{ $pengajuan->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
            </select>
            <button type="submit" class="btn btn-primary">Update Transkrip</button>
        </form>
    </div>
</div>

@endsection