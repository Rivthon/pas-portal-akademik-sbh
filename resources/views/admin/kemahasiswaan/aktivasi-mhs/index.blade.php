@extends('layouts.master')

@section('content')
@push('head')
<style>
    /* ===== Aktivasi Dashboard Custom Styles ===== */
    .aktivasi-page .stat-card {
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        padding: 1.5rem;
        background: #fff;
        transition: box-shadow .2s ease, transform .15s ease;
    }
    .aktivasi-page .stat-card:hover {
        box-shadow: 0 4px 24px rgba(105,108,255,.12);
        transform: translateY(-2px);
    }
    .aktivasi-page .stat-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.3rem;
    }
    .aktivasi-page .stat-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #8592a3;
        margin-bottom: 4px;
    }
    .aktivasi-page .stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: #384551;
        line-height: 1;
    }
    .aktivasi-page .progress-thin {
        height: 5px;
        border-radius: 99px;
        background: #f0f0f5;
        overflow: hidden;
        margin-top: 1rem;
    }
    .aktivasi-page .progress-thin .bar {
        height: 100%;
        border-radius: 99px;
        transition: width .6s ease;
    }

    /* Filter Card */
    .aktivasi-page .filter-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        padding: 1.5rem;
    }
    .aktivasi-page .filter-card label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #8592a3;
        margin-bottom: 6px;
        display: block;
    }
    .aktivasi-page .filter-card .form-select,
    .aktivasi-page .filter-card .form-control {
        border: none;
        background: #f3f3f7;
        font-size: .85rem;
        border-radius: 8px;
        padding: .55rem .85rem;
    }
    .aktivasi-page .filter-card .form-select:focus,
    .aktivasi-page .filter-card .form-control:focus {
        box-shadow: 0 0 0 3px rgba(105,108,255,.12);
    }

    /* Table Modern */
    .aktivasi-page .table-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        overflow: hidden;
    }
    .aktivasi-page .table-modern thead th {
        background: #f3f3f7;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #8592a3;
        border: none;
        padding: .85rem 1.25rem;
        white-space: nowrap;
    }
    .aktivasi-page .table-modern tbody td {
        padding: .85rem 1.25rem;
        vertical-align: middle;
        border-color: rgba(0,0,0,.04);
        font-size: .875rem;
    }
    .aktivasi-page .table-modern tbody tr {
        transition: background .15s ease;
    }
    .aktivasi-page .table-modern tbody tr:hover {
        background: rgba(105,108,255,.04);
    }

    /* Toggle Switch Custom */
    .aktivasi-page .toggle-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .aktivasi-page .toggle-wrapper .form-check-input {
        width: 36px;
        height: 18px;
        cursor: pointer;
    }
    .aktivasi-page .toggle-wrapper .form-check-input:checked {
        background-color: #696cff;
        border-color: #696cff;
    }
    .aktivasi-page .toggle-label {
        font-size: .65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .aktivasi-page .toggle-label.aktif { color: #696cff; }
    .aktivasi-page .toggle-label.nonaktif { color: #a1acb8; }

    /* Badge Prodi */
    .aktivasi-page .badge-prodi {
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding: 3px 10px;
        border-radius: 99px;
    }
    .aktivasi-page .badge-prodi.farmasi { background: rgba(255,171,0,.12); color: #e69800; }
    .aktivasi-page .badge-prodi.gizi { background: rgba(113,221,55,.12); color: #56b026; }
    .aktivasi-page .badge-prodi.kebidanan { background: rgba(105,108,255,.12); color: #696cff; }
    .aktivasi-page .badge-prodi.default { background: rgba(133,146,163,.12); color: #8592a3; }

    /* Student info cell */
    .aktivasi-page .student-info .student-name {
        font-weight: 700;
        font-size: .875rem;
        color: #384551;
        margin-bottom: 1px;
    }
    .aktivasi-page .student-info .student-nim {
        font-size: .75rem;
        color: #a1acb8;
        font-weight: 500;
    }
    .aktivasi-page .student-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }

    /* Bulk Action Bar */
    .aktivasi-page .bulk-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 1.25rem;
        margin-top: 1.25rem;
        border-top: 1px solid rgba(0,0,0,.06);
    }

    /* Pagination Modern */
    .aktivasi-page .pagination-modern {
        background: #f3f3f7;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .aktivasi-page .pagination-modern .page-link {
        border: none;
        border-radius: 8px;
        padding: .35rem .7rem;
        font-size: .8rem;
        font-weight: 600;
        margin: 0 2px;
        color: #566a7f;
    }
    .aktivasi-page .pagination-modern .page-item.active .page-link {
        background: #696cff;
        color: #fff;
    }
    .aktivasi-page .pagination-modern .page-item .page-link:hover {
        background: #e7e7ff;
    }

    /* Breadcrumb */
    .aktivasi-page .breadcrumb-item a {
        color: #8592a3;
        text-decoration: none;
        font-size: .85rem;
    }
    .aktivasi-page .breadcrumb-item.active {
        color: #696cff;
        font-weight: 600;
        font-size: .85rem;
    }

    /* Btn Outline Modern */
    .btn-outline-aktivasi {
        border: 1px solid #e2e2e6;
        background: #f3f3f7;
        color: #566a7f;
        font-size: .8rem;
        font-weight: 600;
        border-radius: 8px;
        padding: .45rem .9rem;
        transition: all .15s ease;
    }
    .btn-outline-aktivasi:hover {
        background: #e7e7ff;
        color: #696cff;
        border-color: #c0c1ff;
    }
    .btn-danger-soft {
        background: rgba(255,62,29,.08);
        color: #ff3e1d;
        border: none;
        font-size: .8rem;
        font-weight: 600;
        border-radius: 8px;
        padding: .45rem .9rem;
    }
    .btn-danger-soft:hover {
        background: rgba(255,62,29,.16);
        color: #ff3e1d;
    }
</style>
@endpush

<div class="aktivasi-page">
    {{-- Breadcrumb & Page Header --}}
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.home') }}"><i class="bx bx-home-alt"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Mahasiswa</a></li>
                <li class="breadcrumb-item active">Aktivasi</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-1" style="letter-spacing: -.02em;">Aktivasi Mahasiswa</h4>
        <p class="text-muted mb-0" style="font-size: .875rem;">Kelola akses akademik dan izin mahasiswa untuk setiap modul perkuliahan.</p>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-4 mb-4">
        {{-- Total Mahasiswa Aktif --}}
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: rgba(105,108,255,.1); color: #696cff;">
                        <i class="bx bx-group"></i>
                    </div>
                </div>
                <div class="stat-label">Total Mahasiswa Aktif</div>
                <div class="stat-value" id="stat-total">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        {{-- KRS Active % --}}
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: rgba(255,171,0,.1); color: #ffab00;">
                        <i class="bx bx-check-double"></i>
                    </div>
                    <span class="text-muted" style="font-size: .75rem;">{{ $stats['krs_count'] }} mhs</span>
                </div>
                <div class="stat-label">KRS Teraktivasi</div>
                <div class="stat-value" id="stat-krs">{{ $stats['krs'] }}%</div>
                <div class="progress-thin">
                    <div class="bar" style="width: {{ $stats['krs'] }}%; background: #ffab00;"></div>
                </div>
            </div>
        </div>
        {{-- UTS Active % --}}
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: rgba(113,221,55,.1); color: #71dd37;">
                        <i class="bx bx-edit-alt"></i>
                    </div>
                    <span class="text-muted" style="font-size: .75rem;">{{ $stats['uts_count'] }} mhs</span>
                </div>
                <div class="stat-label">UTS Teraktivasi</div>
                <div class="stat-value" id="stat-uts">{{ $stats['uts'] }}%</div>
                <div class="progress-thin">
                    <div class="bar" style="width: {{ $stats['uts'] }}%; background: #71dd37;"></div>
                </div>
            </div>
        </div>
        {{-- UAS Active % --}}
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: rgba(105,108,255,.1); color: #696cff;">
                        <i class="bx bx-award"></i>
                    </div>
                    <span class="text-muted" style="font-size: .75rem;">{{ $stats['uas_count'] }} mhs</span>
                </div>
                <div class="stat-label">UAS Teraktivasi</div>
                <div class="stat-value" id="stat-uas">{{ $stats['uas'] }}%</div>
                <div class="progress-thin">
                    <div class="bar" style="width: {{ $stats['uas'] }}%; background: #696cff;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Toolbar --}}
    <div class="filter-card mb-4">
        <form id="search-aktivasi" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="filter-prodi">Program Studi</label>
                <select class="form-select" id="filter-prodi" name="jurusan_id">
                    <option value="">Semua Program Studi</option>
                    @foreach($programStudiList as $ps)
                    <option value="{{ $ps->jurusan_id }}" {{ request('jurusan_id') == $ps->jurusan_id ? 'selected' : '' }}>{{ $ps->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter-semester">Semester</label>
                <select class="form-select" id="filter-semester" name="semester">
                    <option value="">Semua</option>
                    @for($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter-kelas">Kelas</label>
                <select class="form-select" id="filter-kelas" name="kelas">
                    <option value="">Semua</option>
                    <option value="pagi" {{ request('kelas') == 'pagi' ? 'selected' : '' }}>Reguler A</option>
                    <option value="karyawan" {{ request('kelas') == 'karyawan' ? 'selected' : '' }}>Reguler B</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="search-input">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: #f3f3f7; border: none;"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" id="search-input" class="form-control"
                        placeholder="Nama, NIM..." value="{{ request()->get('search') }}" style="border: none; background: #f3f3f7;">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1" style="border-radius: 8px;">
                    <i class="bx bx-filter-alt me-1"></i> Filter
                </button>
            </div>
        </form>

        {{-- Bulk Actions Bar --}}
        <div class="bulk-bar">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @can('aktivasi-bulk-update')
                    <div class="dropdown">
                        <button class="btn-outline-aktivasi dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-bolt me-1"></i> Aktifkan Semua
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item bulk-activate" href="#" data-type="krs">Aktifkan Semua KRS</a></li>
                            <li><a class="dropdown-item bulk-activate" href="#" data-type="uts">Aktifkan Semua Jadwal UTS</a></li>
                            <li><a class="dropdown-item bulk-activate" href="#" data-type="uas">Aktifkan Semua Jadwal UAS</a></li>
                            <li><a class="dropdown-item bulk-activate" href="#" data-type="nilai_uts">Aktifkan Semua Nilai UTS</a></li>
                            <li><a class="dropdown-item bulk-activate" href="#" data-type="nilai_uas">Aktifkan Semua Nilai UAS</a></li>
                            <li><a class="dropdown-item bulk-activate" href="#" data-type="akhir">Aktifkan Semua KHS</a></li>
                        </ul>
                    </div>
                @endcan
                @can('aktivasi-reset')
                    <button id="reset-all-status" class="btn-danger-soft">
                        <i class="bx bx-refresh me-1"></i> Reset Semua Status
                    </button>
                @endcan
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="table-card">
        <div id="aktivasi-list">
            @include('admin.kemahasiswaan.aktivasi-mhs.partials_list', ['mahasiswa' => $mahasiswa])
        </div>
    </div>
</div>

@push('head')
<script>
    // Global config for AJAX routes
    window.aktivasiConfig = {
        indexRoute: "{{ route('admin.aktivasi.index') }}",
        updateStatusRoute: "{{ route('admin.aktivasi-mhs.updateStatus') }}",
        bulkUpdateRoute: "{{ route('admin.aktivasi-mhs.bulkUpdate') }}",
        resetRoute: "{{ route('admin.reset.all.status') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
@endpush
@endsection

@push('head')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const config = window.aktivasiConfig;

    // ===== FILTER AJAX =====
    document.getElementById('search-aktivasi').addEventListener('submit', function(e) {
        e.preventDefault();
        fetchAktivasiData();
    });

    // Auto-filter on dropdown change
    ['filter-prodi', 'filter-semester', 'filter-kelas'].forEach(function(id) {
        document.getElementById(id).addEventListener('change', function() {
            fetchAktivasiData();
        });
    });

    function fetchAktivasiData() {
        const params = new URLSearchParams({
            search: document.getElementById('search-input').value,
            jurusan_id: document.getElementById('filter-prodi').value,
            semester: document.getElementById('filter-semester').value,
            kelas: document.getElementById('filter-kelas').value,
        });

        const listEl = document.getElementById('aktivasi-list');
        listEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        fetch(config.indexRoute + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            listEl.innerHTML = data.html;
            // Update stats if returned
            if (data.stats) {
                updateStatCards(data.stats);
            }
        })
        .catch(() => {
            listEl.innerHTML = '<div class="text-center py-5 text-danger">Gagal memuat data.</div>';
        });
    }

    function updateStatCards(stats) {
        const totalEl = document.getElementById('stat-total');
        const krsEl = document.getElementById('stat-krs');
        const utsEl = document.getElementById('stat-uts');
        const uasEl = document.getElementById('stat-uas');
        if (totalEl) totalEl.textContent = Number(stats.total).toLocaleString('id-ID');
        if (krsEl) krsEl.textContent = stats.krs + '%';
        if (utsEl) utsEl.textContent = stats.uts + '%';
        if (uasEl) uasEl.textContent = stats.uas + '%';
    }

    // ===== PAGINATION AJAX =====
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-links a');
        if (!link) return;
        e.preventDefault();
        const url = link.getAttribute('href');
        const listEl = document.getElementById('aktivasi-list');
        listEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => { listEl.innerHTML = data.html; })
            .catch(() => { listEl.innerHTML = '<div class="text-center py-5 text-danger">Gagal memuat data.</div>'; });
    });

    // ===== TOGGLE STATUS (Toastr) =====
    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('toggle-status')) return;
        const toggle = e.target;
        const mahasiswaId = toggle.dataset.id;
        const type = toggle.dataset.type;
        const status = toggle.checked ? 1 : 0;

        // Update label
        const label = toggle.closest('.toggle-wrapper')?.querySelector('.toggle-label');
        if (label) {
            label.textContent = status ? 'AKTIF' : 'NONAKTIF';
            label.className = 'toggle-label ' + (status ? 'aktif' : 'nonaktif');
        }

        fetch(config.updateStatusRoute, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': config.csrfToken,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ mahasiswa_id: mahasiswaId, type: type, status: status }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (typeof toastr !== 'undefined') {
                    toastr.success(data.message, 'Berhasil!', { timeOut: 2500, progressBar: true });
                }
            } else {
                toggle.checked = !toggle.checked;
                if (label) {
                    label.textContent = toggle.checked ? 'AKTIF' : 'NONAKTIF';
                    label.className = 'toggle-label ' + (toggle.checked ? 'aktif' : 'nonaktif');
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(data.message, 'Gagal!');
                }
            }
        })
        .catch(() => {
            toggle.checked = !toggle.checked;
            if (typeof toastr !== 'undefined') {
                toastr.error('Terjadi kesalahan jaringan.', 'Error!');
            }
        });
    });

    // ===== RESET ALL STATUS (SweetAlert) =====
    document.getElementById('reset-all-status')?.addEventListener('click', function() {
        Swal.fire({
            title: 'Reset Semua Status?',
            text: 'Semua status aktivasi mahasiswa akan dikembalikan ke nonaktif. Tindakan ini tidak bisa dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff3e1d',
            cancelButtonColor: '#8592a3',
            confirmButtonText: '<i class="bx bx-refresh"></i> Ya, Reset Semua!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(config.resetRoute, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Content-Type': 'application/json',
                    },
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ title: 'Berhasil!', text: data.message, icon: 'success', timer: 2000, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                })
                .catch(() => Swal.fire('Error!', 'Terjadi kesalahan jaringan.', 'error'));
            }
        });
    });

    // ===== BULK ACTIVATE (SweetAlert) =====
    document.querySelectorAll('.bulk-activate').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const type = this.dataset.type;
            const typeLabel = this.textContent.trim();

            Swal.fire({
                title: 'Konfirmasi',
                text: 'Yakin ingin ' + typeLabel + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Aktifkan!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    const body = {
                        type: type,
                        status: 1,
                        jurusan_id: document.getElementById('filter-prodi').value,
                        semester: document.getElementById('filter-semester').value,
                        kelas: document.getElementById('filter-kelas').value,
                    };

                    fetch(config.bulkUpdateRoute, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(body),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ title: 'Berhasil!', text: data.message, icon: 'success', timer: 2000, showConfirmButton: false })
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error!', 'Terjadi kesalahan jaringan.', 'error'));
                }
            });
        });
    });
});
</script>
@endpush
