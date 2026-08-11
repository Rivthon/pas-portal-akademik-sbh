@props(['jadwalList', 'role'])

@php
    $semesterGroups = $jadwalList
        ->groupBy(fn ($jadwal) => $jadwal->kurikulum?->mataKuliah?->smt ?? 'Lainnya')
        ->sortKeysUsing(function ($a, $b) {
            if (is_numeric($a) && is_numeric($b)) {
                return (int) $a <=> (int) $b;
            }

            return is_numeric($a) ? -1 : (is_numeric($b) ? 1 : strnatcasecmp((string) $a, (string) $b));
        });
    $accordionId = $role.'LmsSemesterAccordion';
@endphp

<div class="accordion d-flex flex-column gap-3" id="{{ $accordionId }}">
    @forelse($semesterGroups as $semester => $semesterItems)
        @php
            $collapseId = $role.'LmsSemester'.preg_replace('/[^A-Za-z0-9]/', '', (string) $semester);
            $semesterIndex = $loop->index;
            $totalSks = $semesterItems->sum(fn ($jadwal) => (int) ($jadwal->kurikulum?->mataKuliah?->sks ?? 0));
        @endphp

        <div class="accordion-item lms-semester-group border-0 rounded-3 shadow-sm overflow-hidden">
            <h2 class="accordion-header" id="heading{{ ucfirst($collapseId) }}">
                <button class="accordion-button lms-semester-toggle {{ $semesterIndex === 0 ? '' : 'collapsed' }} bg-white shadow-none px-3 px-md-4 py-3"
                    type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                    aria-expanded="{{ $semesterIndex === 0 ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                    <span class="avatar avatar-md me-3 d-none d-sm-inline-flex">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-layer"></i></span>
                    </span>
                    <span class="flex-grow-1 text-start">
                        <span class="d-block fw-bold text-dark">Semester {{ $semester }}</span>
                        <small class="text-muted">Kelas LMS tahun akademik aktif</small>
                    </span>
                    <span class="lms-semester-summary d-flex align-items-center gap-2 me-2 me-md-3">
                        <span class="badge bg-label-primary">{{ $semesterItems->count() }} Mata Kuliah</span>
                        <span class="badge bg-label-secondary d-none d-sm-inline-flex">{{ $totalSks }} SKS</span>
                    </span>
                    <i class="bx bx-chevron-down fs-4 lms-semester-chevron"></i>
                </button>
            </h2>

            <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $semesterIndex === 0 ? 'show' : '' }}"
                aria-labelledby="heading{{ ucfirst($collapseId) }}" data-bs-parent="#{{ $accordionId }}">
                <div class="accordion-body bg-light p-3 p-lg-4">
                    <div class="row g-3">
                        @foreach($semesterItems as $item)
                            @php
                                $mataKuliah = $item->kurikulum?->mataKuliah;
                                $programStudi = $item->kurikulum?->programStudi;
                                $jenisKelas = strtolower((string) $item->jenis_kelas);
                                $dosenPengampu = collect($item->kurikulum?->dosenToMatakuliah)
                                    ->filter(fn ($assignment) => strtolower((string) $assignment->jenis_dosen) === 'teori'
                                        && strtolower((string) $assignment->jenis_kelas) === $jenisKelas)
                                    ->pluck('dosen.nama')->filter()->unique()->values();
                                $jamMulai = $item->jam_mulai ? substr((string) $item->jam_mulai, 0, 5) : '-';
                                $jamSelesai = $item->jam_selesai ? substr((string) $item->jam_selesai, 0, 5) : '-';
                                $progress = min(100, (int) round(((int) $item->pertemuan_count / 14) * 100));
                            @endphp

                            <div class="col-12 col-md-6 col-xl-4 lms-card-item">
                                <div class="card border-0 shadow-sm h-100 lms-course-card bg-white overflow-hidden">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                            <span class="badge bg-label-primary">{{ $mataKuliah?->matakuliah_id ?? '-' }}</span>
                                            <span class="badge {{ $jenisKelas === 'karyawan' ? 'bg-label-warning' : 'bg-label-success' }} text-capitalize">
                                                <i class="bx {{ $jenisKelas === 'karyawan' ? 'bx-moon' : 'bx-sun' }} me-1"></i>{{ $item->jenis_kelas ?? '-' }}
                                            </span>
                                        </div>

                                        <h5 class="fw-bold text-dark mb-2 lh-base">{{ $mataKuliah?->nama ?? '-' }}</h5>
                                        <div class="text-muted small mb-3">
                                            <i class="bx bx-buildings me-1 text-primary"></i>{{ $programStudi?->nama ?? '-' }}
                                            <span class="mx-1">•</span>Semester {{ $mataKuliah?->smt ?? '-' }}
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-7">
                                                <div class="lms-info-box">
                                                    <small class="text-muted d-block mb-1">Jadwal Kuliah</small>
                                                    <span class="fw-semibold small d-block"><i class="bx bx-calendar me-1 text-primary"></i>{{ $item->hari ?? '-' }}</span>
                                                    <span class="small text-muted">{{ $jamMulai }}–{{ $jamSelesai }}</span>
                                                </div>
                                            </div>
                                            <div class="col-5">
                                                <div class="lms-info-box">
                                                    <small class="text-muted d-block mb-1">Ruang / SKS</small>
                                                    <span class="fw-semibold small d-block text-truncate" title="{{ $item->ruangan?->nama_ruangan ?? $item->ruangan?->nama ?? '-' }}">
                                                        {{ $item->ruangan?->nama_ruangan ?? $item->ruangan?->nama ?? '-' }}
                                                    </span>
                                                    <span class="small text-muted">{{ $mataKuliah?->sks ?? 0 }} SKS</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-1">Dosen Pengampu</small>
                                            @forelse($dosenPengampu as $namaDosen)
                                                <span class="badge bg-label-info text-wrap text-start me-1 mb-1"><i class="bx bx-user me-1"></i>{{ $namaDosen }}</span>
                                            @empty
                                                <span class="small text-muted">Belum ditentukan</span>
                                            @endforelse
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            <span class="badge bg-label-success"><i class="bx bx-file me-1"></i>{{ $item->materi_count }} Materi</span>
                                            <span class="badge bg-label-warning"><i class="bx bx-task me-1"></i>{{ $item->tugas_count }} Tugas</span>
                                            <span class="badge bg-label-primary"><i class="bx bx-help-circle me-1"></i>{{ $item->quiz_count }} Quiz</span>
                                        </div>

                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="fw-semibold">Progres Pertemuan</small>
                                                <small class="text-muted">{{ $item->pertemuan_count }} / 14</small>
                                            </div>
                                            <div class="progress mb-3" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $progress }}%"
                                                    aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>

                                            @if($role === 'dosen')
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('dosen.lms.kelola', $item->id) }}" class="btn btn-primary rounded-pill flex-grow-1"><i class="bx bx-cog me-1"></i>Kelola</a>
                                                    <a href="{{ route('dosen.lms.gradebook', $item->id) }}" class="btn btn-outline-primary rounded-pill flex-grow-1"><i class="bx bx-table me-1"></i>Gradebook</a>
                                                    <a href="{{ route('dosen.lms.quiz.index', $item->id) }}" class="btn btn-outline-primary rounded-circle px-3" title="Kelola Quiz"><i class="bx bx-question-mark"></i></a>
                                                </div>
                                            @else
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('mahasiswa.lms.show', $item) }}" class="btn btn-primary rounded-pill flex-grow-1"><i class="bx bx-folder-open me-1"></i>Buka Kelas</a>
                                                    <a href="{{ route('mahasiswa.lms.gradebook.show', $item) }}" class="btn btn-outline-primary rounded-circle px-3" title="Lihat Nilai"><i class="bx bx-bar-chart-square"></i></a>
                                                    <a href="{{ route('mahasiswa.lms.quiz.index', $item) }}" class="btn btn-outline-primary rounded-circle px-3" title="Buka Quiz"><i class="bx bx-question-mark"></i></a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm" id="emptyRow">
            <div class="card-body text-center py-5">
                <i class="bx bx-book-content text-muted mb-3" style="font-size:4rem"></i>
                <h5 class="fw-bold">Belum Ada Mata Kuliah LMS</h5>
                <p class="text-muted mb-0">Mata kuliah pada tahun akademik aktif akan tampil di sini.</p>
            </div>
        </div>
    @endforelse
</div>

<style>
    .lms-semester-toggle::after { display: none; }
    .lms-semester-chevron { transition: transform .25s ease; }
    .lms-semester-toggle[aria-expanded="true"] .lms-semester-chevron { transform: rotate(180deg); }
    .lms-info-box { background: #f8f9fa; border-radius: .5rem; padding: .65rem .75rem; height: 100%; }
</style>
