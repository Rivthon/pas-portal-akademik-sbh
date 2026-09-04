@extends('layouts.master')
@section('title', 'LMS Semua Mata Kuliah')

@section('content')
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#696cff,#4f52d9)">
        <div class="d-flex justify-content-between align-items-center">
            <div><span class="badge bg-white text-primary mb-2">ADMIN LMS</span><h3 class="text-white fw-bold mb-1">LMS Semua Mata Kuliah</h3><p class="text-white-50 mb-0">Pantau pertemuan, materi, tugas, dan quiz seluruh program studi.</p></div>
            <i class="bx bx-book-content d-none d-md-block" style="font-size:5rem;opacity:.3"></i>
        </div>
    </div>
</div>

<x-lms-calendar
    :events="$calendarEvents"
    :store-route="route('admin.lms.calendar.notes.store')"
    :update-route-template="route('admin.lms.calendar.notes.update', ['note' => '__NOTE__'])"
    :destroy-route-template="route('admin.lms.calendar.notes.destroy', ['note' => '__NOTE__'])"
    calendar-id="adminLmsCalendar"
/>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Tahun Akademik</label><select name="ta_id" class="form-select">
                @foreach($tahunAkademik as $ta)<option value="{{ $ta->ta_id }}" @selected((string)$selectedTaId === (string)$ta->ta_id)>{{ $ta->nama }} - {{ ucfirst($ta->semester) }}</option>@endforeach
            </select></div>
            <div class="col-md-3"><label class="form-label">Program Studi</label><select name="prodi_id" class="form-select"><option value="">Semua Program Studi</option>
                @foreach($programStudi as $prodi)<option value="{{ $prodi->jurusan_id }}" @selected(request('prodi_id') == $prodi->jurusan_id)>{{ $prodi->nama }}</option>@endforeach
            </select></div>
            <div class="col-md-2"><label class="form-label">Kelas</label><select name="jenis_kelas" class="form-select"><option value="">Semua Kelas</option><option value="reguler" @selected(request('jenis_kelas')==='reguler')>Reguler A</option><option value="karyawan" @selected(request('jenis_kelas')==='karyawan')>Reguler B</option></select></div>
            <div class="col-md-3"><label class="form-label">Pencarian</label><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Mata kuliah atau dosen"></div>
            <div class="col-md-1"><button class="btn btn-primary w-100"><i class="bx bx-search"></i></button></div>
        </form>
    </div>
</div>

<div class="row g-4">
    @forelse($jadwalList as $jadwal)
        @php
            $mk=$jadwal->kurikulum?->mataKuliah; $prodi=$jadwal->kurikulum?->programStudi;
            $dosen=$jadwal->kurikulum?->dosenToMatakuliah?->pluck('dosen.nama')->filter()->unique()->implode(', ');
        @endphp
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 admin-lms-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3"><span class="avatar-initial rounded bg-label-primary p-3"><i class="bx bx-book-open fs-3"></i></span><span class="badge bg-label-{{ strtolower((string)$jadwal->jenis_kelas)==='karyawan'?'warning':'primary' }}">{{ jenis_kelas_label($jadwal->jenis_kelas ?: '-') }}</span></div>
                    <h5 class="fw-bold mb-1">{{ $mk?->nama ?? '-' }}</h5>
                    <small class="text-muted d-block mb-2">{{ $mk?->matakuliah_id }} &bull; {{ $prodi?->nama ?? '-' }}</small>
                    <p class="small mb-3"><i class="bx bx-user me-1"></i>{{ $dosen ?: 'Dosen belum ditentukan' }}</p>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge bg-label-secondary">{{ $jadwal->pertemuan_count }} Pertemuan</span>
                        <span class="badge bg-label-info">{{ $jadwal->materi_count }} Materi</span>
                        <span class="badge bg-label-warning">{{ $jadwal->tugas_count }} Tugas</span>
                        <span class="badge bg-label-primary">{{ $jadwal->quiz_count }} Quiz</span>
                    </div>
                    <a href="{{ route('admin.lms.show',$jadwal) }}" class="btn btn-primary w-100">Buka Kelas <i class="bx bx-right-arrow-alt ms-1"></i></a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bx bx-folder-open fs-1 text-muted"></i><h5 class="mt-2">Tidak ada kelas ditemukan</h5></div></div></div>
    @endforelse
</div>
@if($jadwalList->hasPages())
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body d-flex justify-content-center py-3">
            {{ $jadwalList->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endif
<style>.admin-lms-card{transition:.2s}.admin-lms-card:hover{transform:translateY(-4px);box-shadow:0 .6rem 1.5rem rgba(67,89,113,.16)!important}</style>
@endsection
