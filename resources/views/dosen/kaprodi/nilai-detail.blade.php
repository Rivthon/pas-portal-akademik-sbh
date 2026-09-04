@extends('layouts.dosen')
@section('title','Rincian Nilai')
@section('content')
<div class="d-flex justify-content-between align-items-start mb-4"><div><a href="{{route('dosen.kaprodi.nilai.index')}}">&larr; Kembali</a><h4 class="mt-2 mb-1">{{$submission->jadwal?->kurikulum?->mataKuliah?->nama}}</h4><span class="text-muted">{{$submission->jadwal?->programStudi?->nama}} - {{jenis_kelas_label($submission->jadwal?->jenis_kelas??'Reguler')}} - {{$submission->submitter?->nama}}</span></div>@if($submission->status==='submitted')<form method="POST" action="{{route('dosen.kaprodi.nilai.approve',$submission)}}">@csrf<button class="btn btn-success" onclick="return confirm('Setujui seluruh nilai ini?')"><i class="bx bx-check"></i> ACC Nilai</button></form>@endif</div>
<div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>NIM</th><th>Mahasiswa</th><th>UTS</th><th>UAS</th><th>Tugas</th><th>Absensi</th><th>Praktik</th><th>Absolut</th><th>Huruf</th></tr></thead><tbody>
@forelse($nilai as $item)<tr><td>{{$item->mahasiswa?->nim}}</td><td>{{$item->mahasiswa?->nama}}</td><td>{{$item->uts}}</td><td>{{$item->uas}}</td><td>{{$item->tugas}}</td><td>{{$item->absen}}</td><td>{{$item->praktik}}</td><td><b>{{$item->akhir}}</b></td><td><span class="badge bg-label-primary">{{$item->khs}}</span></td></tr>@empty<tr><td colspan="9" class="text-center py-5">Tidak ada mahasiswa pada kelas ini.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
