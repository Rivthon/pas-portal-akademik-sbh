@extends('layouts.mahasiswa')
@section('content')
<div class="container">
    <h2>Absensi Mata Kuliah: {{ $jadwal->mataKuliah->name }}</h2>
    <h4>Pertemuan Ke: {{ $pertemuan->pertemuan_id ?? 'Belum Ada' }}</h4>
    <p><strong>Jadwal:</strong> {{ $jadwal->hari }} - {{ $jadwal->jam_mulai }} s/d {{ $jadwal->jam_selesai }}</p>

    <!-- Tampilkan pesan -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Form Absensi -->
    @if($pertemuan)
        @if($absensiHariIni)
            <div class="alert alert-info">
                Anda sudah melakukan absensi untuk hari ini.
            </div>
        @else
            <form action="{{ route('mahasiswa.absensi.store') }}" method="POST">
                @csrf
                <input type="hidden" name="pertemuan_id" value="{{ $pertemuan->pertemuan_id }}">
                <input type="hidden" name="jadwal_id" value="{{ $jadwal->jadwal_id }}">
                <input type="hidden" name="mahasiswa_id" value="{{ $mahasiswa->mahasiswa_id }}">

                <div class="mb-3">
                    <label for="status" class="form-label">Status Kehadiran</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="hadir">Hadir</option>
                        <option value="izin">Izin</option>
                        <option value="tidak hadir">Tidak Hadir</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                    <textarea id="keterangan" name="keterangan" class="form-control" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Kirim Absensi</button>
            </form>
        @endif
    @else
        <div class="alert alert-warning">
            Absensi belum dapat dilakukan karena pertemuan belum tersedia. Silakan hubungi dosen terkait.
        </div>
    @endif

    <!-- Riwayat Absensi -->
    <h3 class="mt-4">Riwayat Absensi</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Pertemuan</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayatAbsensi as $absensi)
                <tr>
                    <td>{{ $absensi->pertemuan_id }}</td>
                    <td>{{ \Carbon\Carbon::parse($absensi->tanggal)->format('d-m-Y') }}</td>
                    <td>
                        <span class="badge
                            @if($absensi->status == 'hadir') bg-success
                            @elseif($absensi->status == 'izin') bg-warning
                            @else bg-danger
                            @endif">
                            {{ ucfirst($absensi->status) }}
                        </span>
                    </td>
                    <td>{{ $absensi->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada riwayat absensi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="container">
        <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-secondary">
    Kembali
</a>
    </div>
</div>
@endsection
