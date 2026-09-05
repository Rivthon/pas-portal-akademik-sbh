@extends('layouts.dosen')
@section('title', 'Monitoring Dosen')

@section('content')
<style>
    .monitoring-hero { background: linear-gradient(135deg, #435ebe 0%, #6979f8 100%); color: #fff; border: 0; }
    .monitoring-hero .text-muted { color: rgba(255,255,255,.78) !important; }
    .metric-card { border: 0; box-shadow: 0 .25rem 1rem rgba(34,48,62,.08); height: 100%; }
    .metric-icon { width: 44px; height: 44px; display: grid; place-items: center; border-radius: 12px; font-size: 1.35rem; }
    .monitoring-tabs { gap: .35rem; border-bottom: 0; }
    .monitoring-tabs .nav-link { border: 0; border-radius: .6rem; color: #697a8d; font-weight: 600; }
    .monitoring-tabs .nav-link.active { color: #435ebe; background: rgba(67,94,190,.1); }
    .progress-thin { height: 7px; min-width: 110px; }
    .comment-box { background: #f6f7fb; border-left: 3px solid #8592a3; border-radius: .35rem; padding: .65rem .8rem; }
</style>

<div class="card monitoring-hero shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-white text-primary mb-2">DASHBOARD KAPRODI</span>
                <h3 class="text-white mb-1">Monitoring Dosen Seprodi</h3>
                <p class="text-muted mb-0">Pantau pelaksanaan pengajaran, RPS, KRS, dan EDOM dalam satu halaman.</p>
            </div>
            <div class="text-md-end">
                <small class="text-muted d-block">Tahun akademik</small>
                <strong>{{ $tahunAkademikTerpilih?->nama ?? 'Belum dipilih' }} {{ $tahunAkademikTerpilih?->semester ? '- '.$tahunAkademikTerpilih->semester : '' }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('dosen.kaprodi.monitoring.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Program Studi</label>
                <select name="program_studi_id" class="form-select">
                    @foreach($programStudiDipimpin as $prodi)
                        <option value="{{ $prodi->jurusan_id }}" @selected((int) $programStudiId === (int) $prodi->jurusan_id)>{{ $prodi->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun Akademik</label>
                <select name="ta_id" class="form-select">
                    @foreach($tahunAjaran as $ta)
                        <option value="{{ $ta->ta_id }}" @selected((int) $taId === (int) $ta->ta_id)>
                            {{ $ta->nama }}{{ $ta->semester ? ' - '.$ta->semester : '' }}{{ $ta->status_ta ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Cari dosen atau mata kuliah</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama dosen, NIDN, kode, mata kuliah">
                </div>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl">
        <div class="card metric-card"><div class="card-body d-flex gap-3 align-items-center">
            <span class="metric-icon bg-label-primary"><i class="bx bx-group"></i></span>
            <div><small class="text-muted">Dosen</small><h4 class="mb-0">{{ $summary->dosen }}</h4></div>
        </div></div>
    </div>
    <div class="col-6 col-xl">
        <div class="card metric-card"><div class="card-body d-flex gap-3 align-items-center">
            <span class="metric-icon bg-label-info"><i class="bx bx-chalkboard"></i></span>
            <div><small class="text-muted">Pertemuan</small><h4 class="mb-0">{{ $summary->pertemuan }}</h4></div>
        </div></div>
    </div>
    <div class="col-6 col-xl">
        <div class="card metric-card"><div class="card-body d-flex gap-3 align-items-center">
            <span class="metric-icon bg-label-success"><i class="bx bx-file"></i></span>
            <div><small class="text-muted">RPS tersedia</small><h4 class="mb-0">{{ $summary->rps_tersedia }}/{{ $summary->rps_total }}</h4></div>
        </div></div>
    </div>
    <div class="col-6 col-xl">
        <div class="card metric-card"><div class="card-body d-flex gap-3 align-items-center">
            <span class="metric-icon bg-label-warning"><i class="bx bx-check-square"></i></span>
            <div><small class="text-muted">KRS disetujui</small><h4 class="mb-0">{{ $summary->krs_disetujui }}/{{ $summary->mahasiswa }}</h4></div>
        </div></div>
    </div>
    <div class="col-6 col-xl">
        <div class="card metric-card"><div class="card-body d-flex gap-3 align-items-center">
            <span class="metric-icon bg-label-danger"><i class="bx bx-star"></i></span>
            <div><small class="text-muted">Rata-rata EDOM</small><h4 class="mb-0">{{ number_format($summary->edom, 2) }}</h4></div>
        </div></div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-transparent border-bottom pt-3">
        <ul class="nav nav-tabs monitoring-tabs" id="monitoringTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#progress-pane" type="button"><i class="bx bx-line-chart me-1"></i>Progress Pengajaran</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rps-pane" type="button"><i class="bx bx-file me-1"></i>RPS</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#krs-pane" type="button"><i class="bx bx-list-check me-1"></i>KRS</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#edom-pane" type="button"><i class="bx bx-message-square-detail me-1"></i>EDOM</button></li>
        </ul>
    </div>
    <div class="tab-content p-0">
        <div class="tab-pane fade show active" id="progress-pane">
            <div class="p-3 border-bottom"><small class="text-muted">Target monitoring adalah maksimal 14 pertemuan per dosen, mata kuliah, kelas, dan jenis pengajaran. Pertemuan masa depan tidak dihitung.</small></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Dosen</th><th>Mata Kuliah</th><th>Kelas</th><th>Jenis</th><th>Progress</th><th>PBM</th><th>Terakhir</th></tr></thead>
                    <tbody>
                    @forelse($progressRows as $row)
                        <tr>
                            <td><strong>{{ $row->dosen }}</strong><small class="d-block text-muted">{{ $row->nidn ?: '-' }}</small></td>
                            <td><strong>{{ $row->mata_kuliah }}</strong><small class="d-block text-muted">{{ $row->kode }} &bull; Semester {{ $row->semester ?: '-' }}</small></td>
                            <td><span class="badge bg-label-info">{{ jenis_kelas_label($row->jenis_kelas) }}</span></td>
                            <td><span class="badge bg-label-{{ $row->jenis === 'praktik' ? 'success' : 'primary' }}">{{ ucfirst($row->jenis) }}</span></td>
                            <td>
                                <div class="d-flex justify-content-between small mb-1"><strong>{{ $row->jumlah }}/14</strong><span>{{ $row->persen }}%</span></div>
                                <div class="progress progress-thin"><div class="progress-bar bg-{{ $row->jumlah >= 14 ? 'success' : ($row->jumlah ? 'primary' : 'danger') }}" style="width: {{ $row->persen }}%"></div></div>
                            </td>
                            <td><small class="d-block"><i class="bx bx-wifi me-1"></i>Online: {{ $row->online }}</small><small class="d-block"><i class="bx bx-building me-1"></i>Offline: {{ $row->offline }}</small></td>
                            <td>{{ $row->terakhir ? date('d/m/Y', strtotime($row->terakhir)) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bx bx-info-circle fs-2 d-block mb-2"></i>Belum ada penugasan dosen yang sesuai.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="rps-pane">
            <div class="p-3 border-bottom"><small class="text-muted">Status RPS ditampilkan per mata kuliah dan kelas agar kelas berbeda tidak saling tertukar.</small></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Mata Kuliah</th><th>Kelas</th><th>Dosen Pengampu</th><th>Status</th><th>Pengunggah / Pembaruan</th></tr></thead>
                    <tbody>
                    @forelse($rpsRows as $row)
                        <tr>
                            <td><strong>{{ $row->mata_kuliah }}</strong><small class="d-block text-muted">{{ $row->kode }} &bull; Semester {{ $row->semester ?: '-' }}</small></td>
                            <td><span class="badge bg-label-info">{{ jenis_kelas_label($row->jenis_kelas) }}</span></td>
                            <td>{{ $row->dosen ?: '-' }}</td>
                            <td><span class="badge bg-label-{{ $row->tersedia ? 'success' : 'danger' }}"><i class="bx bx-{{ $row->tersedia ? 'check' : 'x' }} me-1"></i>{{ $row->tersedia ? 'Sudah upload' : 'Belum upload' }}</span></td>
                            <td>@if($row->tersedia){{ $row->pengunggah ?: '-' }}<small class="d-block text-muted">{{ optional($row->diperbarui)->format('d/m/Y H:i') }}</small>@else - @endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada data RPS untuk periode ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="krs-pane">
            <div class="p-3 border-bottom"><small class="text-muted">Ringkasan mahasiswa aktif dikelompokkan berdasarkan dosen pembimbing akademik.</small></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Dosen Pembimbing</th><th>Total Mahasiswa</th><th>Sudah Ambil</th><th>Belum Ambil</th><th>Menunggu ACC</th><th>Disetujui</th></tr></thead>
                    <tbody>
                    @forelse($krsRows as $row)
                        <tr>
                            <td><strong>{{ $row->dosen }}</strong></td>
                            <td>{{ $row->total }}</td>
                            <td><span class="badge bg-label-info">{{ $row->sudah }}</span></td>
                            <td><span class="badge bg-label-{{ $row->belum ? 'danger' : 'success' }}">{{ $row->belum }}</span></td>
                            <td><span class="badge bg-label-{{ $row->menunggu ? 'warning' : 'secondary' }}">{{ $row->menunggu }}</span></td>
                            <td><span class="badge bg-label-success">{{ $row->disetujui }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada mahasiswa aktif yang sesuai.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="edom-pane">
            <div class="p-3 border-bottom"><small class="text-muted"><i class="bx bx-lock-alt me-1"></i>Komentar ditampilkan anonim. Nama dan identitas mahasiswa tidak ditampilkan kepada Kaprodi.</small></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Dosen</th><th>Mata Kuliah</th><th>Rata-rata</th><th>Responden</th><th>Komentar Anonim</th></tr></thead>
                    <tbody>
                    @forelse($edomRows as $row)
                        <tr>
                            <td><strong>{{ $row->dosen }}</strong></td>
                            <td>{{ $row->mata_kuliah }}<small class="d-block text-muted">{{ $row->kode }}</small></td>
                            <td><span class="badge bg-label-{{ $row->rata_rata >= 4 ? 'success' : ($row->rata_rata >= 3 ? 'warning' : 'danger') }}"><i class="bx bx-star me-1"></i>{{ number_format($row->rata_rata, 2) }}</span></td>
                            <td>{{ $row->responden }} mahasiswa</td>
                            <td style="min-width:260px">
                                @if($row->komentar->isNotEmpty())
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#comments-{{ $loop->index }}">
                                        Lihat {{ $row->komentar->count() }} komentar
                                    </button>
                                    <div class="collapse mt-2" id="comments-{{ $loop->index }}">
                                        @foreach($row->komentar as $isi)<div class="comment-box small mb-2">{{ $isi }}</div>@endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">Belum ada komentar</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada hasil EDOM untuk periode ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
