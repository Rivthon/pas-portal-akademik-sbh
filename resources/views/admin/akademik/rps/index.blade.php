@extends('layouts.master')
@section('title', 'RPS Semua Mata Kuliah')

@section('content')
<div class="card border-0 shadow-sm mb-4 overflow-hidden"><div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#1e3c72,#2a5298)">
    <div class="d-flex justify-content-between align-items-center"><div><span class="badge bg-white text-primary mb-2">ADMIN RPS</span><h3 class="fw-bold text-white mb-1">RPS Semua Mata Kuliah</h3><p class="text-white-50 mb-0">Pantau kelengkapan dokumen RPS seluruh dosen dan program studi.</p></div><i class="bx bx-file d-none d-md-block" style="font-size:5rem;opacity:.3"></i></div>
</div></div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted">Total Mata Kuliah/Kelas</small><h3 class="mb-0">{{ $mataKuliah->count() }}</h3></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted">Sudah Upload</small><h3 class="text-success mb-0">{{ $mataKuliah->filter(fn($i)=>$i->rpsAdmin)->count() }}</h3></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted">Belum Upload</small><h3 class="text-danger mb-0">{{ $mataKuliah->reject(fn($i)=>$i->rpsAdmin)->count() }}</h3></div></div></div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Tahun Akademik</label><select name="ta_id" class="form-select">@foreach($tahunAkademik as $ta)<option value="{{ $ta->ta_id }}" @selected((string)$selectedTaId===(string)$ta->ta_id)>{{ $ta->nama }} - {{ ucfirst($ta->semester) }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Program Studi</label><select name="prodi_id" class="form-select"><option value="">Semua Program Studi</option>@foreach($programStudi as $prodi)<option value="{{ $prodi->jurusan_id }}" @selected(request('prodi_id')==$prodi->jurusan_id)>{{ $prodi->nama }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Kelas</label><select name="jenis_kelas" class="form-select"><option value="">Semua</option><option value="reguler" @selected(request('jenis_kelas')==='reguler')>Reguler</option><option value="karyawan" @selected(request('jenis_kelas')==='karyawan')>Karyawan</option></select></div>
            <div class="col-md-3"><label class="form-label">Pencarian</label><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Mata kuliah atau dosen"></div>
            <div class="col-md-1"><button class="btn btn-primary w-100"><i class="bx bx-search"></i></button></div>
        </form>
    </div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>No</th><th>Mata Kuliah</th><th>Program Studi</th><th>Dosen</th><th>Kelas</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($mataKuliah as $item)
            @php($rps=$item->rpsAdmin)
            <tr><td>{{ $loop->iteration }}</td><td><strong>{{ $item->kurikulum?->mataKuliah?->nama ?? '-' }}</strong><small class="d-block text-muted">{{ $item->kurikulum?->mataKuliah?->matakuliah_id }} &bull; Semester {{ $item->kurikulum?->mataKuliah?->smt }}</small></td><td>{{ $item->kurikulum?->programStudi?->nama ?? '-' }}</td><td>{{ $item->dosen_pengampu->implode(', ') ?: '-' }}</td><td><span class="badge bg-label-{{ strtolower((string)$item->jenis_kelas)==='karyawan'?'warning':'primary' }}">{{ strtoupper($item->jenis_kelas ?: '-') }}</span></td>
            <td>@if($rps)<span class="badge bg-label-success"><i class="bx bx-check me-1"></i>Sudah Upload</span><small class="d-block text-muted mt-1">{{ $rps->updated_at?->diffForHumans() }}</small>@else<span class="badge bg-label-danger">Belum Upload</span>@endif</td>
            <td>@if($rps)<a target="_blank" href="{{ route('admin.rps.show',$rps) }}" class="btn btn-sm btn-label-primary"><i class="bx bx-show me-1"></i>Lihat PDF</a>@else<span class="text-muted">-</span>@endif</td></tr>
        @empty<tr><td colspan="7" class="text-center text-muted py-5">Tidak ada mata kuliah yang sesuai filter.</td></tr>@endforelse
        </tbody>
    </table></div>
</div>
@endsection
