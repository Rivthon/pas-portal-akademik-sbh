@extends('layouts.dosen')

@section('title', 'Transkrip Nilai Mahasiswa')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">


    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-start align-items-sm-center gap-4">
                <div class="avatar avatar-xl">
                    @if(isset($mahasiswa->foto) && $mahasiswa->foto)
                    <img src="{{ asset('storage/' . $mahasiswa->foto) }}" alt="{{ $mahasiswa->nama }}"
                        class="rounded-circle">
                    @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($mahasiswa->nama) }}&background=random&color=fff&size=128"
                        alt="{{ $mahasiswa->nama }}" class="rounded-circle">
                    @endif
                </div>
                <div class="d-flex flex-column">
                    <h4 class="mb-1">{{ $mahasiswa->nama }}</h4>
                    <p class="text-muted mb-2">{{ $mahasiswa->nim }}</p>
                    <div>
                        <span class="badge bg-label-primary me-2">IPK: {{ $ipk }}</span>
                        <span class="badge bg-label-info">Total SKS: {{ $totalSks }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <h5 class="card-header">Rincian Nilai Akademik</h5>

        @if(!$transkrip->isEmpty())
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th class="text-center">SMT</th>
                        <th class="text-center">SKS</th>
                        <th class="text-center">Nilai</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($transkrip as $nilai)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $nilai->kurikulum->mataKuliah->matakuliah_id ?? 'N/A' }}</strong></td>
                        <td>{{ $nilai->kurikulum->mataKuliah->nama ?? 'N/A' }}</td>
                        <td class="text-center">{{ $nilai->kurikulum->mataKuliah->smt ?? 'N/A' }}</td>
                        <td class="text-center">{{ $nilai->kurikulum->mataKuliah->sks ?? '0' }}</td>
                        <td class="text-center">
                            @php
                            $badgeColor = 'secondary';
                            if (in_array($nilai->khs, ['A', 'AB', 'BA'])) {
                            $badgeColor = 'success';
                            } elseif (in_array($nilai->khs, ['B', 'BC'])) {
                            $badgeColor = 'primary';
                            } elseif ($nilai->khs == 'C') {
                            $badgeColor = 'info';
                            } elseif ($nilai->khs == 'D') {
                            $badgeColor = 'warning';
                            } elseif ($nilai->khs == 'E') {
                            $badgeColor = 'danger';
                            }
                            @endphp
                            <span class="badge bg-label-{{ $badgeColor }}">{{ $nilai->khs }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        {{-- Tampilan jika transkrip kosong --}}
        <div class="card-body">
            <div class="alert alert-warning mb-0" role="alert">
                Belum ada data nilai yang dapat ditampilkan untuk mahasiswa ini.
            </div>
        </div>
        @endif
    </div>
</div>
@endsection