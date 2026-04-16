@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Daftar Jadwal Absensi</h2>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Mata Kuliah</th>
                <th>Hari</th>
                <th>Jam</th>
                <th>Status Absensi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jadwal as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->mataKuliah->name }}</td>
                    <td>{{ $item->hari }}</td>
                    <td>{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</td>
                    <td>
                        @if ($item->status_absensi)
                            <span class="badge bg-success">Absensi Terbuka</span>
                        @else
                            <span class="badge bg-danger">Absensi Tertutup</span>
                        @endif
                    </td>
                    <td>
                        @can('matakuliah-edit')  <!-- Tambahkan hak akses sesuai role pengguna -->
                              </a>
                                  <a href="{{ route('admin.pertemuan.index', $item->jadwal_id) }}" class="btn btn-primary btn-sm">
                                      Lihat Detail
                                </a>
                            @if (!$item->status_absensi)
                                <a href="{{ route('admin.absensi.open', $item->jadwal_id) }}" class="btn btn-success btn-sm">
                                    Buka Absensi
                                </a>
                            @else
                                <a href="{{ route('admin.absensi.close', $item->jadwal_id) }}" class="btn btn-warning btn-sm">
                                    Tutup Absensi

                            @endif

                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {!! $jadwal->links() !!}
</div>
@endsection
