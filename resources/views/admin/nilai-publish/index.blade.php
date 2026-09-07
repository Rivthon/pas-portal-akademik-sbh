@extends('layouts.master')

@section('title', 'Penerbitan KHS')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-4 text-white" style="background: linear-gradient(135deg, #3448c5, #696cff);">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="text-white fw-bold mb-2"><i class="bx bx-paper-plane me-2"></i>Penerbitan KHS</h4>
                    <p class="mb-0 text-white-50">Mode sementara: nilai yang sudah diinput dosen dapat langsung diterbitkan oleh BAAK tanpa menunggu verifikasi Kaprodi.</p>
                </div>
                <span class="badge bg-white text-primary px-3 py-2">Kontrol BAAK</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success"><i class="bx bx-check-circle me-1"></i>{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><i class="bx bx-error-circle me-1"></i>{{ $errors->first() }}</div>
    @endif

    <div class="alert alert-warning border-0 shadow-sm d-flex gap-2 align-items-start">
        <i class="bx bx-info-circle fs-4"></i>
        <div><strong>Penerbitan sementara aktif.</strong> Nilai yang belum diverifikasi akan otomatis berstatus <em>Disahkan sementara oleh BAAK</em>. Data nilai lama yang sudah lengkap tetap dapat diterbitkan meskipun belum mempunyai pengajuan.</div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end" id="publication-filter">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tahun Ajaran</label>
                    <select name="ta_id" class="form-select" onchange="this.form.submit()">
                        @foreach($tahunAjaran as $ta)
                            <option value="{{ $ta->ta_id }}" @selected($taId == $ta->ta_id)>{{ $ta->nama }} - {{ $ta->semester }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Program Studi</label>
                    <select name="program_studi_id" class="form-select" onchange="this.form.submit()">
                        @foreach($programStudi as $prodi)
                            <option value="{{ $prodi->jurusan_id }}" @selected($selectedProdiId == $prodi->jurusan_id)>{{ $prodi->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Filter Semester</label>
                    <select name="semester" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Semester</option>
                        @foreach($semesterOptions as $semesterItem)
                            <option value="{{ $semesterItem }}" @selected($semester === $semesterItem)>Semester {{ $semesterItem }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if($selectedProdi)
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Siap Diterbitkan</small><h3 class="text-success mb-0">{{ $selectedProdi->approved_count }}</h3>@if($selectedProdi->temporary_count)<small class="text-info">{{ $selectedProdi->temporary_count }} disahkan sementara BAAK</small>@endif</div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Menunggu Verifikasi</small><h3 class="text-warning mb-0">{{ $selectedProdi->submitted_count }}</h3></div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Perlu Revisi</small><h3 class="text-danger mb-0">{{ $selectedProdi->revision_count }}</h3></div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Belum Diajukan</small><h3 class="text-secondary mb-0">{{ $selectedProdi->missing_count }}</h3></div></div></div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between gap-3 mb-3">
                            <div><h5 class="mb-1">Terbitkan Satu Tahun Ajaran</h5><small class="text-muted">Mencakup semua semester dan mata kuliah pada {{ $selectedProdi->nama }}.</small></div>
                            <i class="bx bx-calendar-check fs-2 text-primary"></i>
                        </div>
                        <div class="mt-auto">
                            @if($globalPublication)
                                <div class="alert alert-success py-2 mb-2">Terbit {{ $globalPublication->published_at->format('d/m/Y H:i') }}</div>
                                <form method="POST" action="{{ route('admin.nilai-publish.destroy', $globalPublication) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger" onclick="return confirm('Batalkan penerbitan seluruh tahun ajaran?')">Batalkan Penerbitan</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.nilai-publish.store') }}">
                                    @csrf
                                    <input type="hidden" name="ta_id" value="{{ $taId }}">
                                    <input type="hidden" name="program_studi_id" value="{{ $selectedProdiId }}">
                                    <input type="hidden" name="scope_type" value="all">
                                    <button class="btn btn-primary" @disabled(!$allPublishable) onclick="return confirm('{{ $allReady ? 'Terbitkan seluruh KHS tahun ajaran ini?' : 'Terbitkan sementara seluruh KHS? Nilai yang belum diverifikasi Kaprodi akan disahkan sementara oleh BAAK.' }}')"><i class="bx bx-paper-plane me-1"></i>{{ $allReady ? 'Terbitkan Semua' : 'Terbitkan Sementara' }}</button>
                                </form>
                                @unless($allPublishable)<small class="d-block text-danger mt-2">Masih ada kelas dengan nilai mahasiswa yang benar-benar belum lengkap. Gunakan penerbitan per mata kuliah.</small>@endunless
                                @if($allPublishable && !$allReady)<small class="d-block text-warning mt-2">Verifikasi Kaprodi yang belum selesai akan dilewati sementara.</small>@endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between gap-3 mb-3">
                            <div><h5 class="mb-1">Terbitkan Per Semester</h5><small class="text-muted">Pilih semester pada filter untuk menerbitkan seluruh mata kuliah semester tersebut.</small></div>
                            <i class="bx bx-layer fs-2 text-info"></i>
                        </div>
                        <div class="mt-auto">
                            @if($semester === null)
                                <div class="alert alert-info mb-0 py-2">Pilih semester terlebih dahulu.</div>
                            @elseif($globalPublication)
                                <div class="alert alert-success mb-0 py-2">Sudah tercakup penerbitan satu tahun ajaran.</div>
                            @elseif($semesterPublication)
                                <div class="alert alert-success py-2 mb-2">Semester {{ $semester }} terbit {{ $semesterPublication->published_at->format('d/m/Y H:i') }}</div>
                                <form method="POST" action="{{ route('admin.nilai-publish.destroy', $semesterPublication) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger" onclick="return confirm('Batalkan penerbitan semester ini?')">Batalkan Semester {{ $semester }}</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.nilai-publish.store') }}">
                                    @csrf
                                    <input type="hidden" name="ta_id" value="{{ $taId }}">
                                    <input type="hidden" name="program_studi_id" value="{{ $selectedProdiId }}">
                                    <input type="hidden" name="scope_type" value="semester">
                                    <input type="hidden" name="semester" value="{{ $semester }}">
                                    <button class="btn btn-info text-white" @disabled(!$semesterPublishable) onclick="return confirm('{{ $semesterReady ? 'Terbitkan seluruh KHS Semester '.$semester.'?' : 'Terbitkan sementara KHS Semester '.$semester.'? Nilai yang belum diverifikasi Kaprodi akan disahkan sementara oleh BAAK.' }}')"><i class="bx bx-paper-plane me-1"></i>{{ $semesterReady ? 'Terbitkan Semester '.$semester : 'Terbitkan Sementara' }}</button>
                                </form>
                                @unless($semesterPublishable)<small class="d-block text-danger mt-2">Masih ada kelas pada semester ini dengan nilai mahasiswa yang belum lengkap.</small>@endunless
                                @if($semesterPublishable && !$semesterReady)<small class="d-block text-warning mt-2">Verifikasi Kaprodi yang belum selesai akan dilewati sementara.</small>@endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div><h5 class="mb-1">Penerbitan Per Mata Kuliah</h5><small class="text-muted">Mahasiswa hanya melihat nilai mata kuliah yang sudah diterbitkan.</small></div>
                <span class="badge bg-label-primary">{{ $jadwalList->count() }} kelas</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Mata Kuliah</th><th>Semester / Kelas</th><th>Status Nilai</th><th>Status Terbit</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($jadwalList as $jadwal)
                            @php
                                $submission = $jadwal->nilaiSubmission;
                                $ready = $submission?->status === 'approved';
                                $temporaryApproval = $submission?->isTemporaryBaakApproval() ?? false;
                                $hasStoredGrades = (bool) $jadwal->stored_grades_complete;
                                $publication = $jadwal->effective_publication;
                            @endphp
                            <tr>
                                <td><strong>{{ $jadwal->kurikulum?->mataKuliah?->nama ?? '-' }}</strong><small class="d-block text-muted">{{ $jadwal->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }}</small></td>
                                <td><span class="badge bg-label-secondary">Semester {{ $jadwal->kurikulum?->mataKuliah?->smt ?? '-' }}</span><span class="badge bg-label-{{ strtolower((string) $jadwal->jenis_kelas) === 'karyawan' ? 'warning' : 'success' }} ms-1">{{ jenis_kelas_label($jadwal->jenis_kelas) }}</span></td>
                                <td>
                                    @if($temporaryApproval)<span class="badge bg-label-info">Disahkan Sementara BAAK</span>
                                    @elseif($ready)<span class="badge bg-label-success">Disetujui Kaprodi</span>
                                    @elseif(!$submission && $hasStoredGrades)<span class="badge bg-label-primary">Nilai Lama Siap Diterbitkan</span>
                                    @elseif(!$submission)<span class="badge bg-label-secondary">Belum Diajukan</span>
                                    @elseif($submission->status === 'revision')<span class="badge bg-label-danger">Perlu Revisi</span>
                                    @else<span class="badge bg-label-warning">Menunggu Kaprodi</span>@endif
                                </td>
                                <td>
                                    @if($publication)
                                        <span class="badge bg-label-success">Sudah Terbit</span>
                                        <small class="d-block text-muted mt-1">{{ $publication->scope_type === 'all' ? 'Satu tahun ajaran' : ($publication->scope_type === 'semester' ? 'Cakupan semester' : 'Per mata kuliah') }}</small>
                                    @else<span class="badge bg-label-secondary">Belum Terbit</span>@endif
                                </td>
                                <td class="text-end">
                                    @if($jadwal->exact_publication && !$globalPublication && !$semesterPublication)
                                        <form method="POST" action="{{ route('admin.nilai-publish.destroy', $jadwal->exact_publication) }}">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Batalkan penerbitan mata kuliah ini?')"><i class="bx bx-x me-1"></i>Batalkan</button>
                                        </form>
                                    @elseif(!$publication)
                                        <form method="POST" action="{{ route('admin.nilai-publish.store') }}">
                                            @csrf
                                            <input type="hidden" name="ta_id" value="{{ $taId }}">
                                            <input type="hidden" name="program_studi_id" value="{{ $selectedProdiId }}">
                                            <input type="hidden" name="scope_type" value="course">
                                            <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
                                            <button class="btn btn-sm {{ ($submission || $hasStoredGrades) ? 'btn-primary' : 'btn-secondary' }}" @disabled(!$submission && !$hasStoredGrades) onclick="return confirm('{{ $ready ? 'Terbitkan KHS mata kuliah ini?' : 'Terbitkan nilai ini sementara tanpa menunggu verifikasi Kaprodi?' }}')"><i class="bx bx-paper-plane me-1"></i>{{ $ready ? 'Terbitkan' : 'Terbitkan Sementara' }}</button>
                                        </form>
                                    @else<span class="text-muted small">Diatur dari cakupan {{ $publication->scope_type === 'all' ? 'tahun ajaran' : 'semester' }}</span>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5"><i class="bx bx-info-circle fs-3 d-block mb-2"></i>Tidak ada kelas berpeserta pada filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-warning">Program studi belum tersedia.</div>
    @endif
</div>
@endsection
