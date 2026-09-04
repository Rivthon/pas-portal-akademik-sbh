@extends('layouts.master')
@section('title', 'Manajemen KRS')
@section('content')

@push('head')
<style>
    /* ===== KRS Admin Dashboard Custom Styles ===== */
    .krs-page .stat-card {
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        padding: 1.5rem;
        background: #fff;
        transition: box-shadow .2s ease, transform .15s ease;
    }
    .krs-page .stat-card:hover {
        box-shadow: 0 4px 24px rgba(105,108,255,.12);
        transform: translateY(-2px);
    }
    .krs-page .stat-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.3rem;
    }
    .krs-page .stat-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #8592a3;
        margin-bottom: 4px;
    }
    .krs-page .stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: #384551;
        line-height: 1;
    }
    .krs-page .progress-thin {
        height: 5px;
        border-radius: 99px;
        background: #f0f0f5;
        overflow: hidden;
        margin-top: 1rem;
    }
    .krs-page .progress-thin .bar {
        height: 100%;
        border-radius: 99px;
        transition: width .6s ease;
    }

    /* Filter Card */
    .krs-page .filter-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        padding: 1.5rem;
    }
    .krs-page .filter-card label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #8592a3;
        margin-bottom: 6px;
        display: block;
    }
    .krs-page .filter-card .form-select,
    .krs-page .filter-card .form-control {
        border: none;
        background: #f3f3f7;
        font-size: .85rem;
        border-radius: 8px;
        padding: .55rem .85rem;
    }
    .krs-page .filter-card .form-select:focus,
    .krs-page .filter-card .form-control:focus {
        box-shadow: 0 0 0 3px rgba(105,108,255,.12);
    }

    /* Table Modern */
    .krs-page .table-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        overflow: hidden;
    }
    .krs-page .table-modern thead th {
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
    .krs-page .table-modern tbody td {
        padding: .85rem 1.25rem;
        vertical-align: middle;
        border-color: rgba(0,0,0,.04);
        font-size: .875rem;
    }
    .krs-page .table-modern tbody tr {
        transition: background .15s ease;
    }
    .krs-page .table-modern tbody tr:hover {
        background: rgba(105,108,255,.04);
    }

    /* Badge Prodi */
    .krs-page .badge-prodi {
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding: 3px 10px;
        border-radius: 99px;
    }

    /* Student info cell */
    .krs-page .student-info .student-name {
        font-weight: 700;
        font-size: .875rem;
        color: #384551;
        margin-bottom: 1px;
    }
    .krs-page .student-info .student-nim {
        font-size: .75rem;
        color: #a1acb8;
        font-weight: 500;
    }
    .krs-page .student-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }

    /* Bulk Action Bar */
    .krs-page .bulk-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 1.25rem;
        margin-top: 1.25rem;
        border-top: 1px solid rgba(0,0,0,.06);
    }

    /* Pagination Modern */
    .krs-page .pagination-modern {
        background: #f3f3f7;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .krs-page .pagination-modern .page-link {
        border: none;
        border-radius: 8px;
        padding: .35rem .7rem;
        font-size: .8rem;
        font-weight: 600;
        margin: 0 2px;
        color: #566a7f;
    }
    .krs-page .pagination-modern .page-item.active .page-link {
        background: #696cff;
        color: #fff;
    }
    .krs-page .pagination-modern .page-item .page-link:hover {
        background: #e7e7ff;
    }

    /* Breadcrumb */
    .krs-page .breadcrumb-item a {
        color: #8592a3;
        text-decoration: none;
        font-size: .85rem;
    }
    .krs-page .breadcrumb-item.active {
        color: #696cff;
        font-weight: 600;
        font-size: .85rem;
    }

    /* Button styles */
    .btn-outline-krs {
        border: 1px solid #e2e2e6;
        background: #f3f3f7;
        color: #566a7f;
        font-size: .8rem;
        font-weight: 600;
        border-radius: 8px;
        padding: .45rem .9rem;
        transition: all .15s ease;
    }
    .btn-outline-krs:hover {
        background: #e7e7ff;
        color: #696cff;
        border-color: #c0c1ff;
    }

    /* MK Badge List */
    .mk-badge {
        display: inline-block;
        background: rgba(105,108,255,.08);
        color: #696cff;
        font-size: .72rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        margin: 2px 3px 2px 0;
        white-space: nowrap;
    }
    .mk-badge-more {
        background: rgba(133,146,163,.1);
        color: #8592a3;
        cursor: pointer;
    }

    /* Select2 in modal fix */
   #modalTambahKrs .modal-content {
        border: 0;
        /* border-radius: 18px; */
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(67, 89, 113, .25);
    }

    #modalTambahKrs .modal-header {
        background: linear-gradient(135deg, #696cff 0%, #5f61e6 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
        border-bottom: 0;
    }

    #modalTambahKrs .modal-title {
        font-weight: 700;
        letter-spacing: -.02em;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    #modalTambahKrs .modal-title-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: rgba(255, 255, 255, .18);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }

    #modalTambahKrs .modal-subtitle {
        color: rgba(255, 255, 255, .82);
        font-size: .8rem;
        font-weight: 400;
        margin-top: 2px;
    }

    #modalTambahKrs .modal-body {
        padding: 1.5rem;
        background: #fff;
    }

    #modalTambahKrs .form-label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #566a7f;
        margin-bottom: .5rem;
    }

    #modalTambahKrs .form-select,
    #modalTambahKrs .select2-container--default .select2-selection--single,
    #modalTambahKrs .select2-container--default .select2-selection--multiple {
        border: 1px solid #d9dee3;
        border-radius: 10px;
        min-height: 42px;
    }

    #modalTambahKrs .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px;
        padding-left: 14px;
        color: #566a7f;
    }

    #modalTambahKrs .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }

    #modalTambahKrs .form-section {
        background: #f8f8fb;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    #modalTambahKrs .modal-footer {
        border-top: 1px solid #eef0f4;
        padding: 1rem 1.5rem;
        background: #fbfbfd;
    }

    #modalTambahKrs .btn {
        border-radius: 10px;
        font-weight: 600;
        padding: .6rem 1.1rem;
    }
</style>
@endpush

<div class="krs-page">
    {{-- Breadcrumb & Page Header --}}
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.home') }}"><i class="bx bx-home-alt"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Akademik</a></li>
                <li class="breadcrumb-item active">Manajemen KRS</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-1" style="letter-spacing: -.02em;">Manajemen KRS Mahasiswa</h4>
        <p class="text-muted mb-0" style="font-size: .875rem;">
            Kelola Kartu Rencana Studi mahasiswa. Tambahkan KRS per mahasiswa atau secara massal (bulk).
            <span class="badge bg-label-primary ms-2 fs-6">{{ $tahunAjaran->nama }} ({{ $tahunAjaran->semester }})</span>
        </p>
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
                <div class="stat-value" id="stat-total">{{ number_format($stats['total_mahasiswa']) }}</div>
            </div>
        </div>
        {{-- Total KRS Terisi --}}
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: rgba(113,221,55,.1); color: #71dd37;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <span class="text-muted" style="font-size: .75rem;">{{ $stats['mahasiswa_with_krs'] }} mhs</span>
                </div>
                <div class="stat-label">Sudah Isi KRS</div>
                <div class="stat-value" id="stat-krs">{{ $stats['persentase_krs'] }}%</div>
                <div class="progress-thin">
                    <div class="bar" style="width: {{ $stats['persentase_krs'] }}%; background: #71dd37;"></div>
                </div>
            </div>
        </div>
        {{-- Belum KRS --}}
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: rgba(255,62,29,.1); color: #ff3e1d;">
                        <i class="bx bx-user-x"></i>
                    </div>
                </div>
                <div class="stat-label">Belum Isi KRS</div>
                <div class="stat-value" id="stat-belum">{{ number_format($stats['mahasiswa_tanpa_krs']) }}</div>
            </div>
        </div>
        {{-- Total Kurikulum --}}
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background: rgba(255,171,0,.1); color: #ffab00;">
                        <i class="bx bx-book-open"></i>
                    </div>
                </div>
                <div class="stat-label">Total Kurikulum Aktif</div>
                <div class="stat-value" id="stat-kurikulum">{{ number_format($stats['total_kurikulum']) }}</div>
            </div>
        </div>
    </div>

    {{-- Filter & Toolbar --}}
    <div class="filter-card mb-4">
        <form id="search-krs" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="filter-prodi">Program Studi</label>
                <select class="form-select" id="filter-prodi" name="jurusan_id">
                    <option value="">Semua Program Studi</option>
                    @foreach($programStudiList as $ps)
                    <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter-semester">Semester</label>
                <select class="form-select" id="filter-semester" name="semester">
                    <option value="">Semua</option>
                    @for($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}">Semester {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label for="search-input">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: #f3f3f7; border: none;"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" id="search-input" class="form-control"
                        placeholder="Nama, NIM..." style="border: none; background: #f3f3f7;">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary" style="border-radius: 8px;">
                    <i class="bx bx-filter-alt me-1"></i> Tampilkan
                </button>
                @can('krs-create')
                <button type="button" id="btn-tambah-krs" class="btn btn-success" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalTambahKrs">
                    <i class="bx bx-plus me-1"></i> Tambah KRS
                </button>
                <button type="button" id="btn-bulk-krs" class="btn btn-warning" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalBulkKrs">
                    <i class="bx bx-bolt me-1"></i> Bulk Assign
                </button>
                @endcan
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="table-card">
        <div id="krs-list">
            <div class="text-center py-5 text-muted">
                <i class="bx bx-info-circle" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">Pilih filter di atas lalu klik <strong>Tampilkan</strong> untuk melihat data KRS.</p>
            </div>
        </div>
    </div>
</div>

{{-- ==================== MODAL TAMBAH KRS INDIVIDUAL ==================== --}}
@can('krs-create')
<div class="modal fade" id="modalTambahKrs" tabindex="-1" aria-labelledby="modalTambahKrsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="modal-title-icon me-3">
                        <i class="bx bx-plus-circle"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="modalTambahKrsLabel">
                            Tambah KRS Mahasiswa
                        </h5>
                        <div class="modal-subtitle">
                            Pilih mahasiswa dan tambahkan mata kuliah ke KRS aktif.
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-body">

                <div class="form-section">
                    <label class="form-label fw-semibold">
                        <i class="bx bx-user me-1"></i> Pilih Mahasiswa <span class="text-danger">*</span>
                    </label>
                    <select id="select-mahasiswa" style="width: 100%;">
                        <option value="">Cari nama atau NIM mahasiswa...</option>
                    </select>
                </div>

                <div class="form-section">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-filter-alt me-1"></i> Filter Prodi
                            </label>
                            <select id="modal-filter-prodi" class="form-select">
                                <option value="">Semua Program Studi</option>
                                @foreach($programStudiList as $ps)
                                    <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-calendar me-1"></i> Filter Semester
                            </label>
                            <select id="modal-filter-semester" class="form-select">
                                <option value="">Semua Semester</option>
                                @for($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}">Semester {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section mb-0">
                    <label class="form-label fw-semibold">
                        <i class="bx bx-book me-1"></i> Pilih Mata Kuliah <span class="text-danger">*</span>
                    </label>

                    <select id="select-kurikulum" multiple="multiple" style="width: 100%;"></select>

                    <small class="text-muted d-block mt-2">
                        Anda bisa memilih beberapa mata kuliah sekaligus.
                    </small>

                    <div id="modal-selected-info" class="alert alert-info py-2 mt-3 mb-0" style="display: none;">
                        <i class="bx bx-info-circle me-1"></i>
                        <span id="modal-selected-count">0</span> mata kuliah dipilih
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" id="btn-simpan-krs" class="btn btn-primary">
                    <i class="bx bx-save me-1"></i> Simpan KRS
                </button>
            </div>

        </div>
    </div>
</div>
{{-- ==================== MODAL BULK ASSIGN KRS ==================== --}}
<div class="modal fade" id="modalBulkKrs" tabindex="-1" aria-labelledby="modalBulkKrsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalBulkKrsLabel">
                    <i class="bx bx-bolt me-1"></i> Bulk Assign KRS
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 mb-3">
                    <i class="bx bx-info-circle me-1"></i>
                    Fitur ini akan menambahkan <strong>semua mata kuliah kurikulum</strong> ke <strong>semua mahasiswa aktif</strong> yang sesuai filter. KRS yang sudah ada tidak akan diduplikasi.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Program Studi <span class="text-danger">*</span>
                    </label>
                    <select id="bulk-prodi" class="form-select">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach($programStudiList as $ps)
                        <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Semester <span class="text-danger">*</span>
                    </label>
                    <select id="bulk-semester" class="form-select">
                        <option value="">-- Pilih Semester --</option>
                        @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}">Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Kelas (Opsional)
                    </label>
                    <select id="bulk-kelas" class="form-select">
                        <option value="">Semua Kelas</option>
                        <option value="pagi">Reguler A</option>
                        <option value="karyawan">Reguler B</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btn-proses-bulk" class="btn btn-warning">
                    <i class="bx bx-bolt me-1"></i> Proses Bulk Assign
                </button>
            </div>
        </div>
    </div>
</div>
@endcan

@push('head')
<script>
    window.krsConfig = {
        filterRoute: "{{ route('admin.krs-admin.filter') }}",
        getMahasiswaRoute: "{{ route('admin.krs-admin.getMahasiswa') }}",
        getKurikulumRoute: "{{ route('admin.krs-admin.getKurikulum') }}",
        storeRoute: "{{ route('admin.krs-admin.store') }}",
        bulkStoreRoute: "{{ route('admin.krs-admin.bulkStore') }}",
        destroyRoute: "{{ route('admin.krs-admin.destroy', 'ID_PLACEHOLDER') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
@endpush

@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const config = window.krsConfig;

    // ===== FILTER AJAX =====
    document.getElementById('search-krs').addEventListener('submit', function(e) {
        e.preventDefault();
        fetchKrsData();
    });

    function fetchKrsData() {
        const params = new URLSearchParams({
            search: document.getElementById('search-input').value,
            jurusan_id: document.getElementById('filter-prodi').value,
            semester: document.getElementById('filter-semester').value,
        });

        const listEl = document.getElementById('krs-list');
        listEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Memuat data KRS...</p></div>';

        fetch(config.filterRoute + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            listEl.innerHTML = data.html;
        })
        .catch(() => {
            listEl.innerHTML = '<div class="text-center py-5 text-danger"><i class="bx bx-error" style="font-size:2rem;"></i><p class="mt-2">Gagal memuat data.</p></div>';
        });
    }

    // ===== PAGINATION AJAX =====
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-links a');
        if (!link) return;
        e.preventDefault();
        const url = link.getAttribute('href');
        const listEl = document.getElementById('krs-list');
        listEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => { listEl.innerHTML = data.html; })
            .catch(() => { listEl.innerHTML = '<div class="text-center py-5 text-danger">Gagal memuat data.</div>'; });
    });

    function formatMahasiswa(repo) {
        if (repo.loading) return repo.text;
        if (!repo.nama) return repo.text; // Fallback jika format default

        var $container = $(
            '<div class="d-flex align-items-center py-1">' +
                '<div class="avatar avatar-sm me-3">' +
                    '<span class="avatar-initial rounded-circle bg-label-primary">' + repo.nama.charAt(0) + '</span>' +
                '</div>' +
                '<div class="d-flex flex-column">' +
                    '<span class="fw-semibold text-dark" style="font-size: .85rem; line-height: 1.2;">' + repo.nama + '</span>' +
                    '<small class="text-muted" style="font-size: .75rem;">' + repo.nim + ' &bull; Smt ' + repo.semester + ' &bull; ' + repo.prodi + '</small>' +
                '</div>' +
            '</div>'
        );
        return $container;
    }

    function formatMahasiswaSelection(repo) {
        return repo.nama ? repo.nim + ' - ' + repo.nama : repo.text;
    }

    // ===== SELECT2: Mahasiswa - IMPROVED =====
    function initSelectMahasiswa() {
        const $select = $('#select-mahasiswa');

        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        $select.select2({
            dropdownParent: $('#modalTambahKrs'),
            width: '100%',
            placeholder: 'Klik untuk melihat mahasiswa atau ketik pencarian...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: config.getMahasiswaRoute,
                type: 'GET',
                dataType: 'json',
                delay: 300,
                cache: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: function(params) {
                    return {
                        search: params.term || '',
                        jurusan_id: $('#modal-filter-prodi').val() || '',
                        semester: $('#modal-filter-semester').val() || ''
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.text,
                                nama: item.nama,
                                nim: item.nim,
                                semester: item.semester,
                                prodi: item.prodi
                            };
                        })
                    };
                },
                error: function(xhr) {

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal memuat mahasiswa',
                            text: 'Terjadi kesalahan sistem, cek console log.'
                        });
                    } else {
                        alert('Gagal memuat data mahasiswa. Error ' + xhr.status);
                    }
                }
            },
            templateResult: formatMahasiswa,
            templateSelection: formatMahasiswaSelection
        });
    }

    // ===== LOAD KURIKULUM =====
    function loadKurikulum() {
        const prodiVal = document.getElementById('modal-filter-prodi').value;
        const semesterVal = document.getElementById('modal-filter-semester').value;

        const params = new URLSearchParams();
        if (prodiVal) params.set('jurusan_id', prodiVal);
        if (semesterVal) params.set('semester', semesterVal);

        // Destroy & re-init select2
        if ($('#select-kurikulum').hasClass('select2-hidden-accessible')) {
            $('#select-kurikulum').select2('destroy');
        }
        $('#select-kurikulum').empty();

        fetch(config.getKurikulumRoute + '?' + params.toString())
            .then(r => r.json())
            .then(data => {
                data.forEach(item => {
                    const opt = new Option(item.text, item.id, false, false);
                    $('#select-kurikulum').append(opt);
                });

                $('#select-kurikulum').select2({
                    dropdownParent: $('#modalTambahKrs'),
                    placeholder: 'Pilih mata kuliah...',
                    allowClear: true,
                    width: '100%'
                });
            });
    }

    document.getElementById('modal-filter-prodi').addEventListener('change', loadKurikulum);
    document.getElementById('modal-filter-semester').addEventListener('change', loadKurikulum);

    // Load on modal open
    $('#modalTambahKrs').on('shown.bs.modal', function() {
        initSelectMahasiswa(); // Re-init agar width tidak collapse jadi 0px
        loadKurikulum();
    });

    // Track selection count
    $(document).on('change', '#select-kurikulum', function() {
        const count = $(this).val()?.length || 0;
        const infoEl = document.getElementById('modal-selected-info');
        document.getElementById('modal-selected-count').textContent = count;
        infoEl.style.display = count > 0 ? 'block' : 'none';
    });

    // ===== SIMPAN KRS INDIVIDUAL =====
    document.getElementById('btn-simpan-krs').addEventListener('click', function() {
        const mahasiswaId = $('#select-mahasiswa').val();
        const kurikulumIds = $('#select-kurikulum').val();
        const btn = this;

        if (!mahasiswaId) {
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih mahasiswa terlebih dahulu.' });
            return;
        }

        if (!kurikulumIds || kurikulumIds.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih minimal 1 mata kuliah.' });
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Proses...';

            fetch(config.storeRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    mahasiswa_id: mahasiswaId,
                    kurikulum_ids: kurikulumIds.map(Number),
                }),
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-save me-1"></i> Simpan KRS';

                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 3000, showConfirmButton: true });
                    // Reset form
                    $('#select-mahasiswa').val(null).trigger('change');
                    $('#select-kurikulum').val(null).trigger('change');
                    document.getElementById('modal-selected-info').style.display = 'none';
                    // Refresh table if filter was active
                    fetchKrsData();
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('modalTambahKrs')).hide();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-save me-1"></i> Simpan KRS';
                Swal.fire({ icon: 'error', title: 'Error Jaringan', text: 'Terjadi kesalahan jaringan.' });
            });
    });

    // ===== BULK ASSIGN KRS =====
    document.getElementById('btn-proses-bulk').addEventListener('click', function() {
        const prodi = document.getElementById('bulk-prodi').value;
        const semester = document.getElementById('bulk-semester').value;
        const kelas = document.getElementById('bulk-kelas').value;
        const btn = this;

        if (!prodi || !semester) {
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Program Studi dan Semester wajib diisi.' });
            return;
        }

        Swal.fire({
            title: 'Bulk Assign KRS',
            html: `Semua mata kuliah kurikulum akan ditambahkan ke KRS <strong>semua mahasiswa aktif</strong> pada filter yang dipilih.<br><br>KRS yang sudah ada <strong>tidak akan</strong> diduplikasi.<br><br>Lanjutkan?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bx bx-bolt"></i> Ya, Proses!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ffab00',
        }).then((result) => {
            if (!result.isConfirmed) return;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

            fetch(config.bulkStoreRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    jurusan_id: prodi,
                    semester: semester,
                    kelas: kelas,
                }),
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-bolt me-1"></i> Proses Bulk Assign';

                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 4000, showConfirmButton: true })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-bolt me-1"></i> Proses Bulk Assign';
                Swal.fire({ icon: 'error', title: 'Error Jaringan', text: 'Terjadi kesalahan jaringan.' });
            });
        });
    });

    // ===== DELETE KRS =====
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-delete-krs');
        if (!btn) return;
        e.preventDefault();

        const krsId = btn.dataset.id;
        const mkName = btn.dataset.mk || 'mata kuliah ini';

        Swal.fire({
            title: 'Hapus KRS?',
            html: `Apakah Anda yakin ingin menghapus <strong>${mkName}</strong> dari KRS mahasiswa ini?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff3e1d',
            cancelButtonColor: '#8592a3',
            confirmButtonText: '<i class="bx bx-trash"></i> Ya, Hapus!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (!result.isConfirmed) return;

            const url = config.destroyRoute.replace('ID_PLACEHOLDER', krsId);

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message, 'Berhasil!', { timeOut: 2500, progressBar: true });
                    }
                    // Remove row or refresh
                    const row = btn.closest('tr');
                    if (row) row.remove();
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error!', 'Terjadi kesalahan jaringan.', 'error');
            });
        });
    });

    // ===== TOGGLE DETAIL =====
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-toggle-detail');
        if (!btn) return;
        e.preventDefault();

        const targetId = btn.dataset.target;
        const detailRow = document.getElementById(targetId);
        if (detailRow) {
            detailRow.classList.toggle('d-none');
            const icon = btn.querySelector('i');
            if (icon) {
                icon.classList.toggle('bx-chevron-down');
                icon.classList.toggle('bx-chevron-up');
            }
        }
    });
});
</script>
@endpush
