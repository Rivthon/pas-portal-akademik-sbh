@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Rekap Absensi - {{ $jadwal->mataKuliah->nama }}</h3>
    <p>Jadwal: {{ $jadwal->hari }} - {{ $jadwal->jam }}</p>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Mahasiswa</th>
                @for ($i = 1; $i <= 14; $i++)
                    <th>Pertemuan {{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($mahasiswa as $mhs)
                <tr>
                    <td>{{ $mhs->nama }}</td>
                    @for ($i = 1; $i <= 14; $i++)
                        <td>
                            @php
                                // Cari data absensi mahasiswa berdasarkan pertemuan
                                $absensi = $rekapAbsensi[$mhs->id] ?? null;
                                $pertemuan = $absensi->skip($i - 1)->first();
                            @endphp
                            @if ($pertemuan)
                                {{ $pertemuan->status }} <br>
                                <small>{{ $pertemuan->tanggal->format('d-m-Y') }}</small>
                            @else
                                -
                            @endif
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
