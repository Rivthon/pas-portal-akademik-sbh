@extends('layouts.mahasiswa') {{-- atau layouts.app sesuai struktur kamu --}}

@section('content')
<div class="card">
    <div class="card-header bg-primary ">
        <h5 class="mb-0 text-white">Nilai Ujian Akhir Program (UAP)</h5>
    </div>
    <div class="card-body">
        @if($nilaiUap->isEmpty())
        <div class="alert alert-warning mt-5">Belum ada nilai UAP yang tersedia.</div>
        @else
        <table class="table table-bordered mb-0 mt-5">
            <thead class="table-primary text-dark">
                <tr>
                    {{-- <th>Tahun Ajaran</th> --}}
                    <th>UAP Tulis</th>
                    <th>UAP Praktik</th>
                    {{-- <th>Keterangan</th> --}}
                    <th>Tanggal Input</th>
                </tr>
            </thead>
            <tbody>
                @foreach($nilaiUap as $nilai)
                <tr>
                    {{-- <td>{{ $nilai->tahunAjaran->nama ?? '-' }}</td> --}}
                    <td>{{ $nilai->uap_tulis ?? '-' }}</td>
                    <td>{{ $nilai->uap_praktik ?? '-' }}</td>
                    {{-- <td>{{ $nilai->keterangan ?? '-' }}</td> --}}
                    <td>{{ \Carbon\Carbon::parse($nilai->tanggal_input)->isoFormat('dddd, D MMMM Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
