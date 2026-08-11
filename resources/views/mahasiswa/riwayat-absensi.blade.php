@extends('layouts.mahasiswa')

@section('content')

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>
            <i class="fas fa-clipboard-check"></i>
            Riwayat Absensi
        </h3>

        <a href="{{ route('mahasiswa.rekap.absensi') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Informasi Mata Kuliah --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            Informasi Mata Kuliah
        </div>

        <div class="card-body">

            <table class="table table-borderless mb-0">

                <tr>
                    <th width="220">Mata Kuliah</th>
                    <td>
                        {{ $jadwal->mataKuliah->nama ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <th>Program Studi</th>
                    <td>
                        {{ $jadwal->programStudi->nama ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <th>Dosen Pengampu</th>
                    <td>
                        {{ $jadwal->dosen->nama ?? '-' }}
                    </td>
                </tr>

            </table>

        </div>

    </div>


    {{-- Statistik --}}
    <div class="row mb-4">

        <div class="col-md-2">

            <div class="card border-success shadow-sm">

                <div class="card-body text-center">

                    <h2 class="text-success">{{ $hadir }}</h2>

                    <strong>Hadir</strong>

                </div>

            </div>

        </div>

        <div class="col-md-2">

            <div class="card border-warning shadow-sm">

                <div class="card-body text-center">

                    <h2 class="text-warning">{{ $izin }}</h2>

                    <strong>Izin</strong>

                </div>

            </div>

        </div>

        <div class="col-md-2">

            <div class="card border-info shadow-sm">

                <div class="card-body text-center">

                    <h2 class="text-info">{{ $sakit }}</h2>

                    <strong>Sakit</strong>

                </div>

            </div>

        </div>

        <div class="col-md-2">

            <div class="card border-danger shadow-sm">

                <div class="card-body text-center">

                    <h2 class="text-danger">{{ $alpha }}</h2>

                    <strong>Alpha</strong>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card bg-success text-white shadow">

                <div class="card-body text-center">

                    <h2>{{ $persentase }}%</h2>

                    <strong>Persentase Kehadiran</strong>

                </div>

            </div>

        </div>

    </div>


    {{-- Detail Absensi --}}
    <div class="card shadow-sm">

        <div class="card-header bg-dark text-white">

            Detail Kehadiran

        </div>

        <div class="card-body p-0">

            <table class="table table-bordered table-hover mb-0">

                <thead class="table-light">

                    <tr>

                        <th width="80">Pertemuan</th>

                        <th width="150">Tanggal</th>

                        <th>Topik</th>

                        <th width="180">Status</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($pertemuan as $item)

                        @php

                            $status = optional($item->absensi->first())->status;

                        @endphp

                        <tr>

                            <td class="text-center">

                                {{ $item->pertemuan_ke }}

                            </td>

                            <td>

                                {{ \Carbon\Carbon::parse($item->tanggal_pertemuan)->format('d-m-Y') }}

                            </td>

                            <td>

                                <strong>{{ $item->topik ?? '-' }}</strong>

                                <br>

                                <small class="text-muted">

                                    {{ $item->sub_topik }}

                                </small>

                            </td>

                            <td>

                                @if($status=='hadir')

                                    <span class="badge bg-success">
                                        Hadir
                                    </span>

                                @elseif($status=='izin')

                                    <span class="badge bg-warning text-dark">
                                        Izin
                                    </span>

                                @elseif($status=='sakit')

                                    <span class="badge bg-info">
                                        Sakit
                                    </span>

                                @elseif($status=='tidak hadir')

                                    <span class="badge bg-danger">
                                        Alpha
                                    </span>

                                @else

                                    <span class="badge bg-secondary">

                                        Belum Diabsen

                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4" class="text-center p-5">

                                <img src="{{ asset('images/empty.svg') }}"
                                     width="120"
                                     class="mb-3">

                                <br>

                                Belum ada data pertemuan.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection