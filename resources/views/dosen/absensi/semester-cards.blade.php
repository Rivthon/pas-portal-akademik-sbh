<style>
    .semester-toggle { transition: background-color .2s ease; }
    .semester-toggle::after { display: none; }
    .semester-toggle:hover { background-color: rgba(105, 108, 255, .04); }
    .semester-chevron { transition: transform .25s ease; }
    .semester-toggle[aria-expanded="true"] .semester-chevron { transform: rotate(180deg); }
    .attendance-course-card { transition: transform .2s ease, box-shadow .2s ease; }
    .attendance-course-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(34, 48, 62, .12) !important;
    }
    .attendance-info-box {
        background: #f8f9fa;
        border-radius: .5rem;
        padding: .65rem .75rem;
        height: 100%;
    }
</style>

@if ($absensiList->isEmpty())
    <div class="card border-0 shadow-none bg-transparent">
        <div class="card-body text-center text-muted py-5">
            <span class="avatar avatar-xl mb-3">
                <span class="avatar-initial rounded-circle bg-label-secondary">
                    <i class="bx bx-calendar-x fs-2"></i>
                </span>
            </span>
            <h6 class="fw-bold mb-1">Jadwal Tidak Ditemukan</h6>
            <p class="mb-0">Tidak ada jadwal teori yang sesuai dengan filter atau penugasan Anda.</p>
        </div>
    </div>
@else
    <div class="accordion d-flex flex-column gap-3" id="semesterAbsensiAccordion">
        @foreach ($absensiList as $semester => $jadwalSemester)
            @php
                $collapseId = 'semesterAbsensi'.preg_replace('/[^A-Za-z0-9]/', '', (string) $semester);
                $totalSks = collect($jadwalSemester)->sum('sks');
            @endphp

            <div class="accordion-item border-0 rounded-3 shadow-sm overflow-hidden">
                <h2 class="accordion-header" id="heading{{ $collapseId }}">
                    <button class="accordion-button semester-toggle {{ $loop->first ? '' : 'collapsed' }} bg-white shadow-none px-4 py-3"
                        type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                        <span class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-layer"></i></span>
                        </span>
                        <span class="flex-grow-1">
                            <span class="d-block fw-bold text-dark">Semester {{ $semester }}</span>
                            <small class="text-muted">{{ $activeTA->nama ?? 'Tahun Akademik Aktif' }}</small>
                        </span>
                        <span class="semester-summary d-flex align-items-center gap-2 me-3">
                            <span class="badge bg-label-primary">{{ $jadwalSemester->count() }} Mata Kuliah</span>
                            <span class="badge bg-label-secondary">{{ $totalSks }} SKS</span>
                        </span>
                        <i class="bx bx-chevron-down fs-4 semester-chevron"></i>
                    </button>
                </h2>

                <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                    aria-labelledby="heading{{ $collapseId }}" data-bs-parent="#semesterAbsensiAccordion">
                    <div class="accordion-body bg-light p-3 p-lg-4">
                        <div class="row g-3">
                            @foreach ($jadwalSemester as $jadwal)
                                @php
                                    $jumlahPertemuan = min((int) $jadwal['jumlah_pertemuan'], 14);
                                    $persentasePertemuan = round(($jumlahPertemuan / 14) * 100);
                                    $isKaryawan = strtolower((string) $jadwal['jenis_kelas']) === 'karyawan';
                                @endphp

                                <div class="col-12 col-md-6 col-xl-4 jadwal-card-item">
                                    <div class="card attendance-course-card h-100 border-0 shadow-sm overflow-hidden">
                                        <div class="card-body p-4 d-flex flex-column">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                                                <span class="badge bg-label-primary">{{ $jadwal['kode_matakuliah'] ?? '-' }}</span>
                                                <span class="badge {{ $isKaryawan ? 'bg-label-warning' : 'bg-label-success' }} text-capitalize">
                                                    <i class="bx {{ $isKaryawan ? 'bx-moon' : 'bx-sun' }} me-1"></i>{{ jenis_kelas_label($jadwal['jenis_kelas']) }}
                                                </span>
                                            </div>

                                            <h5 class="fw-bold text-dark mb-2 lh-base">{{ $jadwal['nama_matakuliah'] }}</h5>
                                            <div class="text-muted small mb-3">
                                                <i class="bx bx-buildings me-1 text-primary"></i>{{ $jadwal['program_studi'] }}
                                            </div>

                                            <div class="row g-2 mb-3">
                                                <div class="col-7">
                                                    <div class="attendance-info-box">
                                                        <small class="text-muted d-block mb-1">Jadwal Kuliah</small>
                                                        <span class="fw-semibold small d-block">
                                                            <i class="bx bx-calendar me-1 text-primary"></i>{{ $jadwal['hari'] }}
                                                        </span>
                                                        <span class="small text-muted">{{ substr($jadwal['jam_mulai'], 0, 5) }}–{{ substr($jadwal['jam_selesai'], 0, 5) }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-5">
                                                    <div class="attendance-info-box">
                                                        <small class="text-muted d-block mb-1">Ruang / SKS</small>
                                                        <span class="fw-semibold small d-block">{{ $jadwal['ruangan'] }}</span>
                                                        <span class="small text-muted">{{ $jadwal['sks'] }} SKS</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">Dosen Pengampu</small>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @forelse ($jadwal['dosen'] as $pengampu)
                                                        <span class="badge bg-label-info text-wrap text-start">
                                                            <i class="bx bx-user me-1"></i>{{ $pengampu['nama'] }}
                                                        </span>
                                                    @empty
                                                        <span class="small text-muted">Belum ada data pengampu</span>
                                                    @endforelse
                                                </div>
                                            </div>

                                            <div class="mt-auto">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="fw-semibold">Progres Pertemuan</small>
                                                    <small class="text-muted">{{ $jumlahPertemuan }} / 14</small>
                                                </div>
                                                <div class="progress mb-3" style="height: 6px;">
                                                    <div class="progress-bar {{ $jumlahPertemuan >= 14 ? 'bg-success' : 'bg-primary' }}"
                                                        role="progressbar" style="width: {{ $persentasePertemuan }}%"
                                                        aria-valuenow="{{ $persentasePertemuan }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <button type="button" class="btn btn-primary w-100 rounded-pill"
                                                    data-jadwal-id="{{ $jadwal['jadwal_id'] }}"
                                                    data-nama="{{ $jadwal['nama_matakuliah'] }}"
                                                    data-kode="{{ $jadwal['kode_matakuliah'] ?? '-' }}"
                                                    onclick="openPertemuanModal(this.dataset.jadwalId, this.dataset.nama, this.dataset.kode)">
                                                    <i class="bx bx-calendar-plus me-1"></i>Kelola Pertemuan & Absensi
                                                </button>
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
