@extends('layouts.dosen')
@section('title', 'Absensi Praktik')

@section('content')
<style>
    .semester-praktik-toggle::after { display: none; }
    .semester-praktik-toggle:hover { background-color: rgba(105, 108, 255, .04) !important; }
    .semester-praktik-chevron { transition: transform .25s ease; }
    .semester-praktik-toggle[aria-expanded="true"] .semester-praktik-chevron { transform: rotate(180deg); }
    .practice-course-card { transition: transform .2s ease, box-shadow .2s ease; }
    .practice-course-card:hover { box-shadow: 0 .5rem 1rem rgba(34, 48, 62, .12) !important; }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg,#1e3c72,#2a5298)">
        <div class="card-body p-4 text-white">
            <h4 class="text-white fw-bold mb-2"><i class="bx bx-test-tube me-2"></i>Absensi Praktik</h4>
            <p class="mb-0 text-white-50">Buat pertemuan praktik, lalu catat kehadiran mahasiswa pada kelas yang Anda ampu.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><strong>Data belum dapat disimpan.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @unless($activeTa)
        <div class="alert alert-warning">Belum ada tahun akademik aktif.</div>
    @endunless

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><small class="text-muted d-block mb-1 fw-semibold">Total Mata Kuliah Praktik</small><h3 class="mb-0 fw-bold text-primary">{{ $statistik['total'] }}</h3></div>
                    <i class="bx bx-test-tube fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><small class="text-muted d-block mb-1 fw-semibold">Kelas Reguler A</small><h3 class="mb-0 fw-bold text-success">{{ $statistik['reguler'] }}</h3></div>
                    <i class="bx bx-sun fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><small class="text-muted d-block mb-1 fw-semibold">Kelas Reguler B</small><h3 class="mb-0 fw-bold text-warning">{{ $statistik['karyawan'] }}</h3></div>
                    <i class="bx bx-moon fs-1 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" id="filterPraktikForm" class="row align-items-center gx-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="input-group input-group-merge shadow-sm rounded-pill border">
                        <span class="input-group-text bg-white border-0 rounded-pill-start"><i class="bx bx-search"></i></span>
                        <input name="search" id="filterPraktikSearch" value="{{ request('search') }}" class="form-control border-0 rounded-pill-end ps-0" placeholder="Cari Mata Kuliah atau Nama Dosen...">
                    </div>
                </div>
                <div class="col-md-6">
                    <select name="jenis_kelas" id="filterPraktikKelas" class="form-select shadow-sm rounded-pill border">
                        <option value="semua">-- Tampilkan Semua Jenis Kelas --</option>
                        <option value="reguler" @selected(request('jenis_kelas') === 'reguler')>Kelas Reguler A (Pagi/Siang)</option>
                        <option value="karyawan" @selected(request('jenis_kelas') === 'karyawan')>Kelas Reguler B (Malam/Eksekutif)</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if($jadwalPerSemester->isEmpty())
        <div class="card border-0 shadow-sm"><div class="card-body py-5 text-center text-muted"><i class="bx bx-calendar-x fs-1 d-block mb-2"></i>Tidak ada jadwal praktik yang sesuai dengan filter atau penugasan Anda.</div></div>
    @else
    <div class="accordion d-flex flex-column gap-3" id="semesterPraktikAccordion">
        @foreach($jadwalPerSemester as $semester => $jadwalSemester)
            @php
                $semesterCollapseId = 'semesterPraktik'.preg_replace('/[^A-Za-z0-9]/', '', (string) $semester);
                $totalSksSemester = $jadwalSemester->sum(fn ($jadwalItem) => (int) ($jadwalItem->kurikulum?->mataKuliah?->sks ?? 0));
            @endphp
            <div class="accordion-item border-0 rounded-3 shadow-sm overflow-hidden">
                <h2 class="accordion-header" id="heading{{ $semesterCollapseId }}">
                    <button class="accordion-button semester-praktik-toggle {{ $loop->first ? '' : 'collapsed' }} bg-white shadow-none px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $semesterCollapseId }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $semesterCollapseId }}">
                        <span class="avatar avatar-md me-3"><span class="avatar-initial rounded bg-label-primary"><i class="bx bx-layer"></i></span></span>
                        <span class="flex-grow-1"><span class="d-block fw-bold text-dark">Semester {{ $semester ?: '-' }}</span><small class="text-muted">{{ $activeTa->nama ?? 'Tahun Akademik Aktif' }}</small></span>
                        <span class="d-flex align-items-center gap-2 me-3"><span class="badge bg-label-primary">{{ $jadwalSemester->count() }} Mata Kuliah</span><span class="badge bg-label-secondary">{{ $totalSksSemester }} SKS</span></span>
                        <i class="bx bx-chevron-down fs-4 semester-praktik-chevron"></i>
                    </button>
                </h2>
                <div id="{{ $semesterCollapseId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading{{ $semesterCollapseId }}" data-bs-parent="#semesterPraktikAccordion">
                    <div class="accordion-body bg-light p-3 p-lg-4">
                        <div class="row g-3">
        @foreach($jadwalSemester as $item)
            @php
                $modalId = 'buat-pertemuan-'.$item->id;
                $historyCollapseId = 'riwayat-praktik-'.$item->id;
                $jumlahPertemuan = min($item->pertemuan->count(), 14);
                $persentasePertemuan = round(($jumlahPertemuan / 14) * 100);
                $pengampuPraktik = $item->kurikulum?->dosenToMatakuliah
                    ?->filter(fn ($assignment) => strtolower((string) $assignment->jenis_dosen) === 'praktik'
                        && App\Support\KrsClassResolver::normalize($assignment->jenis_kelas) === App\Support\KrsClassResolver::normalize($item->jenis_kelas))
                    ->pluck('dosen.nama')->filter()->unique()->values() ?? collect();
                $activeAsprak = $item->asprakAssignments ?? collect();
            @endphp
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card practice-course-card border-0 shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1 fw-bold">{{ $item->kurikulum?->mataKuliah?->nama ?? '-' }}</h5>
                            <span class="badge bg-label-primary">{{ $item->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }}</span>
                            <span class="badge bg-label-warning">Semester {{ $item->kurikulum?->mataKuliah?->smt ?? '-' }}</span>
                            <span class="badge bg-label-info">{{ jenis_kelas_label($item->jenis_kelas) }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted"><i class="bx bx-calendar me-1"></i>{{ $item->hari }}, {{ substr($item->jam_mulai,0,5) }}–{{ substr($item->jam_selesai,0,5) }} &nbsp; <i class="bx bx-map me-1"></i>{{ $item->ruangan?->nama ?? '-' }}</p>
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <div class="border rounded p-2 h-100 bg-light">
                                    <small class="text-muted d-block"><i class="bx bx-buildings me-1"></i>Program Studi</small>
                                    <span class="fw-semibold">{{ $item->programStudi?->nama ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-2 h-100 bg-light">
                                    <small class="text-muted d-block"><i class="bx bx-book-open me-1"></i>Semester Mata Kuliah</small>
                                    <span class="fw-semibold">Semester {{ $item->kurikulum?->mataKuliah?->smt ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-2 h-100 bg-light">
                                    <small class="text-muted d-block"><i class="bx bx-group me-1"></i>Jenis Kelas</small>
                                    <span class="fw-semibold">{{ jenis_kelas_label($item->jenis_kelas) }}</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-2 h-100 bg-light">
                                    <small class="text-muted d-block"><i class="bx bx-map me-1"></i>Ruangan</small>
                                    <span class="fw-semibold">{{ $item->ruangan?->nama ?? 'Belum diatur' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Dosen Pengampu Praktik</small>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($pengampuPraktik as $namaPengampu)
                                    <span class="badge bg-label-info text-wrap text-start"><i class="bx bx-user me-1"></i>{{ $namaPengampu }}</span>
                                @empty
                                    <span class="small text-muted">Belum ada data pengampu praktik.</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Asisten Praktikum</small>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($activeAsprak as $assignment)
                                    <span class="badge bg-label-success"><i class="bx bx-user-check me-1"></i>{{ $assignment->mahasiswa?->nama ?? '-' }}</span>
                                @empty
                                    <span class="small text-muted">Belum ada Asprak yang dipilih.</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="fw-semibold">Progres Pertemuan</small>
                            <small class="text-muted">{{ $jumlahPertemuan }} / 14</small>
                        </div>
                        <div class="progress mb-3" style="height:6px"><div class="progress-bar {{ $jumlahPertemuan >= 14 ? 'bg-success' : 'bg-primary' }}" style="width:{{ $persentasePertemuan }}%" role="progressbar" aria-valuenow="{{ $persentasePertemuan }}" aria-valuemin="0" aria-valuemax="100"></div></div>
                        <a href="{{ route('dosen.absensi-praktik.asprak.manage', $item) }}" class="btn btn-outline-success w-100 rounded-pill mb-2"><i class="bx bx-group me-1"></i>Kelola Asprak</a>
                        <button class="btn btn-primary w-100 rounded-pill mb-2" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}"><i class="bx bx-calendar-plus me-1"></i>Kelola Pertemuan &amp; Absensi</button>
                        <button class="btn btn-outline-secondary w-100 rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $historyCollapseId }}" aria-expanded="false" aria-controls="{{ $historyCollapseId }}"><i class="bx bx-history me-1"></i>Riwayat Pertemuan ({{ $item->pertemuan->count() }})</button>
                        <div class="collapse mt-3" id="{{ $historyCollapseId }}">
                        <h6 class="fw-bold border-bottom pb-2">Riwayat Pertemuan</h6>
                        @forelse($item->pertemuan as $pertemuan)
                            @php($editModalId = 'edit-pertemuan-praktik-'.$pertemuan->pertemuan_praktik_id)
                            @php($deleteFormId = 'hapus-pertemuan-praktik-'.$pertemuan->pertemuan_praktik_id)
                            <div class="d-flex align-items-center gap-2 border rounded p-2 mb-2">
                            <a href="{{ route('dosen.absensi-praktik.show', $pertemuan) }}" class="d-flex justify-content-between align-items-center text-decoration-none flex-grow-1 p-1">
                                <span><strong>{{ $pertemuan->topik ?: 'Tanpa topik' }}</strong><small class="d-block text-muted">{{ $pertemuan->tanggal_pertemuan->translatedFormat('d M Y') }} · {{ substr($pertemuan->jam_mulai,0,5) }} · {{ ucfirst($pertemuan->metode_pbm ?: 'offline') }}</small></span>
                                <span class="badge bg-label-success">{{ $pertemuan->absensi_count }} mahasiswa</span>
                            </a>
                                <div class="dropdown flex-shrink-0">
                                    <button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi pertemuan">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="{{ route('dosen.absensi-praktik.show', $pertemuan) }}"><i class="bx bx-user-check me-2"></i>Isi Absensi</a>
                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#{{ $editModalId }}"><i class="bx bx-edit me-2"></i>Edit Pertemuan</button>
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item text-danger btn-hapus-pertemuan-praktik" data-form-id="{{ $deleteFormId }}" data-topik="{{ $pertemuan->topik ?: 'Tanpa topik' }}"><i class="bx bx-trash me-2"></i>Hapus Pertemuan</button>
                                    </div>
                                </div>
                                <form id="{{ $deleteFormId }}" method="POST" action="{{ route('dosen.absensi-praktik.pertemuan.destroy', $pertemuan) }}" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </div>

                            <div class="modal fade" id="{{ $editModalId }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
                                    <form method="POST" action="{{ route('dosen.absensi-praktik.pertemuan.update', $pertemuan) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-header"><h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Pertemuan Praktik</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <p class="fw-bold mb-3">{{ $item->kurikulum?->mataKuliah?->nama ?? '-' }}</p>
                                            <div class="row g-3">
                                                <input type="hidden" name="asprak_selection_present" value="1">
                                                <div class="col-md-4"><label class="form-label">Tanggal</label><input type="date" name="tanggal_pertemuan" class="form-control" value="{{ $pertemuan->tanggal_pertemuan->format('Y-m-d') }}" required></div>
                                                <div class="col-md-4"><label class="form-label">Jam mulai</label><input type="time" name="jam_mulai" class="form-control" value="{{ substr($pertemuan->jam_mulai,0,5) }}" required></div>
                                                <div class="col-md-4"><label class="form-label">Jam selesai</label><input type="time" name="jam_selesai" class="form-control" value="{{ substr($pertemuan->jam_selesai,0,5) }}" required></div>
                                                <div class="col-md-4"><label class="form-label">Metode PBM</label><select name="metode_pbm" class="form-select" required><option value="offline" @selected(strtolower($pertemuan->metode_pbm ?: 'offline') === 'offline')>Offline / Tatap Muka</option><option value="online" @selected(strtolower($pertemuan->metode_pbm ?: '') === 'online')>Online / Daring</option></select></div>
                                                <div class="col-12"><label class="form-label">Topik</label><input name="topik" class="form-control" value="{{ $pertemuan->topik }}" maxlength="255" required></div>
                                                <div class="col-12"><label class="form-label">Subtopik / keterangan</label><textarea name="sub_topik" class="form-control" maxlength="255" rows="2">{{ $pertemuan->sub_topik }}</textarea></div>
                                                 <div class="col-12">
                                                     <label class="form-label fw-semibold">Asprak Bertugas</label>
                                                     <div class="border rounded p-3">
                                                         @forelse($activeAsprak->concat($pertemuan->asprakAttendances->pluck('assignment')->filter())->unique('id') as $assignment)
                                                             <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="asprak_ids[]" value="{{ $assignment->id }}" id="edit-asprak-{{ $pertemuan->pertemuan_praktik_id }}-{{ $assignment->id }}" @checked($pertemuan->asprakAttendances->contains('asprak_penugasan_id', $assignment->id))><label class="form-check-label" for="edit-asprak-{{ $pertemuan->pertemuan_praktik_id }}-{{ $assignment->id }}">{{ $assignment->mahasiswa?->nama }} <small class="text-muted">({{ $assignment->mahasiswa?->nim }})</small></label></div>
                                                         @empty
                                                             <span class="text-muted small">Atur Asprak pada tombol Kelola Asprak terlebih dahulu.</span>
                                                         @endforelse
                                                     </div>
                                                 </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan Perubahan</button></div>
                                    </form>
                                </div></div>
                            </div>
                        @empty
                            <p class="text-center text-muted py-3 mb-0">Belum ada pertemuan praktik.</p>
                        @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold text-dark" id="{{ $modalId }}-label">
                                <i class="bx bx-calendar-plus text-primary me-2"></i>Buat Pertemuan Praktik Baru
                            </h5>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form method="POST" action="{{ route('dosen.absensi-praktik.pertemuan.store') }}">
                                @csrf
                                <input type="hidden" name="jadwal_praktik_id" value="{{ $item->id }}">

                                <div class="mb-3">
                                    <label class="form-label">Mata Kuliah</label>
                                    <input type="text" class="form-control" value="{{ $item->kurikulum?->mataKuliah?->nama ?? '-' }}" readonly>
                                    <small class="text-muted">Semester {{ $item->kurikulum?->mataKuliah?->smt ?? '-' }} &middot; {{ $item->programStudi?->nama ?? '-' }} &middot; {{ jenis_kelas_label($item->jenis_kelas) }}</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tanggal Pertemuan</label>
                                    <input type="date" name="tanggal_pertemuan" class="form-control" value="{{ old('tanggal_pertemuan', now()->toDateString()) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Jam Mulai</label>
                                    <input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai', substr($item->jam_mulai,0,5)) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Jam Selesai</label>
                                    <input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai', substr($item->jam_selesai,0,5)) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Metode PBM</label>
                                    <select name="metode_pbm" class="form-select" required>
                                        <option value="offline" @selected(old('metode_pbm', 'offline') === 'offline')>Offline / Tatap Muka</option>
                                        <option value="online" @selected(old('metode_pbm') === 'online')>Online / Daring</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Topik Pertemuan</label>
                                    <textarea name="topik" class="form-control" maxlength="255" rows="3" required>{{ old('topik') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Sub Topik</label>
                                    <textarea name="sub_topik" class="form-control" maxlength="255" rows="2" placeholder="Detail bahasan...">{{ old('sub_topik') }}</textarea>
                                </div>

                                 <div class="mb-3">
                                     <label class="form-label fw-semibold">Asprak Bertugas</label>
                                     <div class="border rounded p-3">
                                         @forelse($activeAsprak as $assignment)
                                             <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="asprak_ids[]" value="{{ $assignment->id }}" id="new-asprak-{{ $item->id }}-{{ $assignment->id }}"><label class="form-check-label" for="new-asprak-{{ $item->id }}-{{ $assignment->id }}">{{ $assignment->mahasiswa?->nama }} <small class="text-muted">({{ $assignment->mahasiswa?->nim }})</small></label></div>
                                         @empty
                                             <span class="text-muted small">Belum ada Asprak. Gunakan tombol Kelola Asprak pada kartu mata kuliah.</span>
                                         @endforelse
                                     </div>
                                     <small class="text-muted">Pilih sesuai giliran Asprak pada pertemuan ini.</small>
                                 </div>

                                <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary rounded-pill flex-grow-1">
                                        <i class="bx bx-save me-1"></i>Simpan Pertemuan &amp; Lanjut Absensi
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
                                        <i class="bx bx-x me-1"></i>Batal
                                    </button>
                                </div>
                            </form>

                            <div class="mt-5">
                                <h6 class="fw-bold text-muted border-bottom pb-2 mb-3">
                                    <i class="bx bx-list-ul me-1"></i>Riwayat Pertemuan Tersimpan
                                </h6>
                                <ul class="list-group list-group-flush border rounded">
                                    @forelse($item->pertemuan as $pertemuan)
                                        <li class="list-group-item d-flex justify-content-between align-items-center gap-2 py-3">
                                            <a href="{{ route('dosen.absensi-praktik.show', $pertemuan) }}" class="text-decoration-none flex-grow-1">
                                                <strong class="d-block text-dark">{{ $pertemuan->topik ?: 'Tanpa topik' }}</strong>
                                                <small class="text-muted">{{ $pertemuan->tanggal_pertemuan->translatedFormat('d M Y') }} &middot; {{ substr($pertemuan->jam_mulai,0,5) }}&ndash;{{ substr($pertemuan->jam_selesai,0,5) }}</small>
                                            </a>
                                            <span class="badge bg-label-success">{{ $pertemuan->absensi_count }} mahasiswa</span>
                                        </li>
                                    @empty
                                        <li class="list-group-item text-muted text-center py-4 bg-light">Belum ada pertemuan praktik.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

@push('script')
<script>
const filterPraktikForm = document.getElementById('filterPraktikForm');
const filterPraktikSearch = document.getElementById('filterPraktikSearch');
let filterPraktikTimer;

filterPraktikSearch?.addEventListener('input', function () {
    clearTimeout(filterPraktikTimer);
    filterPraktikTimer = setTimeout(function () {
        filterPraktikForm?.submit();
    }, 500);
});

document.getElementById('filterPraktikKelas')?.addEventListener('change', function () {
    filterPraktikForm?.submit();
});

document.querySelectorAll('.btn-hapus-pertemuan-praktik').forEach(function (button) {
    button.addEventListener('click', function () {
        const form = document.getElementById(this.dataset.formId);
        const topik = this.dataset.topik || 'pertemuan ini';

        Swal.fire({
            title: 'Hapus pertemuan praktik?',
            text: 'Pertemuan "' + topik + '" beserta seluruh data absensinya akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed && form) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
