@extends('layouts.master')
@section('title', 'Form Pengajuan Transkrip')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Detail Pengajuan Transkrip</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-primary text-uppercase">
                    <tr>
                        <th style="width: 30%">Field</th>
                        <th>Isi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Nama Mahasiswa</td>
                        <td>{{ $pengajuan->mahasiswa->nama }}</td>
                    </tr>
                    <tr>
                        <td>Jenis Permintaan</td>
                        <td>{{ ucfirst($pengajuan->jenis) }}</td>
                    </tr>
                    <tr>
                        <td>Keperluan</td>
                        <td>{{ $pengajuan->keperluan }}</td>
                    </tr>
                    <tr>
                        <td>Status Saat Ini</td>
                        <td>
                            @php
                            $badgeColor = [
                            'pending' => 'secondary',
                            'disetujui' => 'success',
                            'diproses' => 'info',
                            'selesai' => 'primary',
                            'ditolak' => 'danger',
                            ][$pengajuan->status] ?? 'dark';
                            @endphp
                            <span class="badge bg-{{ $badgeColor }} text-uppercase">{{ $pengajuan->status }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Komentar Admin</td>
                        <td>{{ $pengajuan->komentar_admin ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Lampiran</td>
                        <td>
                            @if ($pengajuan->file)
                            <a href="{{ asset('storage/' . $pengajuan->file) }}" class="btn btn-sm btn-outline-primary"
                                target="_blank">Lihat File</a>
                            @else
                            Tidak ada lampiran
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <hr class="my-4">

        <!-- Form untuk update status -->
        <form action="{{ route('admin.pengajuan.updateStatus', $pengajuan->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="status" class="form-label">Ubah Status</label>
                    <select name="status" class="form-select" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="pending" {{ $pengajuan->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="disetujui" {{ $pengajuan->status == 'disetujui' ? 'selected' : '' }}>Disetujui
                        </option>
                        <option value="diproses" {{ $pengajuan->status == 'diproses' ? 'selected' : '' }}>Diproses
                        </option>
                        <option value="selesai" {{ $pengajuan->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="ditolak" {{ $pengajuan->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div class="col-md-8">
                    <label for="komentar_admin" class="form-label">Komentar Admin</label>
                    <textarea name="komentar_admin" class="form-control" rows="3">{{ $pengajuan->catatan }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.transkrip.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

@endsection