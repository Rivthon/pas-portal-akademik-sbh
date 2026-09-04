@extends('layouts.dosen')
@section('title','Detail Rekap Absensi')
@section('content')
<div class="d-flex justify-content-between mb-4"><div><a href="{{route('dosen.kaprodi.absensi.index')}}">&larr; Kembali</a><h4 class="mt-2">{{$jadwal->kurikulum?->mataKuliah?->nama}}</h4><span class="text-muted">{{$jadwal->programStudi?->nama}} - {{jenis_kelas_label($jadwal->jenis_kelas??'Reguler')}} - {{$jadwal->tahunAjaran?->nama}}</span></div><form method="POST" action="{{route('dosen.kaprodi.absensi.verify',$jadwal)}}">@csrf<button class="btn btn-success"><i class="bx bx-check-shield"></i> {{$verification?'Verifikasi Ulang':'Verifikasi Rekap'}}</button></form></div>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
<div class="card shadow-sm"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Pertemuan</th><th>Tanggal</th><th>Topik</th><th>Pengajar</th><th>H</th><th>S</th><th>I</th><th class="text-danger">A</th></tr></thead><tbody>
@forelse($pertemuan as $p)<tr><td>P{{$loop->iteration}}</td><td>{{\Carbon\Carbon::parse($p->tanggal_pertemuan)->format('d/m/Y')}}</td><td>{{$p->topik?:'-'}}</td><td>{{$p->dosen?->nama?:'-'}}</td><td class="text-success">{{$p->hadir}}</td><td>{{$p->sakit}}</td><td>{{$p->izin}}</td><td class="text-danger fw-bold">{{$p->alfa}}</td></tr>@empty<tr><td colspan="8" class="text-center py-5">Belum ada pertemuan.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
