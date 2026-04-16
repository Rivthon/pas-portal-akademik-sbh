@extends('layouts.app')

@section('content')
    <h4>Detail Absensi untuk Mata Kuliah: {{ $jadwal->mataKuliah->name }}</h4>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Mahasiswa</th>
                <th>Status Kehadiran</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($absensi as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->mahasiswa->name }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td>{{ $item->tanggal }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
