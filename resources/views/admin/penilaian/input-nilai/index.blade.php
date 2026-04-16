@extends('layouts.master')
@section('tittle', 'Input Nilai Mahasiswa')

@push('head')
<style>
    /* ===== Input Nilai Premium Styles ===== */

    /* Hero Card */
    .nilai-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #696cff 0%, #5f61f4 50%, #8b8eff 100%);
        border-radius: 14px;
        padding: 2rem 2rem;
        color: #fff;
        box-shadow: 0 10px 30px rgba(105,108,255,.15);
    }
    .nilai-hero::before {
        content: '';
        position: absolute;
        right: -5%;
        top: -40%;
        width: 250px;
        height: 250px;
        background: rgba(255,255,255,.07);
        border-radius: 50%;
        filter: blur(40px);
    }
    .nilai-hero .hero-content { position: relative; z-index: 2; }
    .nilai-hero .hero-title {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -.02em;
        margin-bottom: .35rem;
    }
    .nilai-hero .hero-sub {
        opacity: .8;
        font-size: .85rem;
    }
    .nilai-hero .hero-illustration {
        position: absolute;
        right: 25px;
        bottom: 0;
        height: 140px;
        opacity: .85;
        z-index: 1;
    }

    /* Filter Card */
    .nilai-filter {
        background: #fff;
        border: 1px solid rgba(0,0,0,.05);
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0,0,0,.02);
        padding: 1.5rem;
    }
    .nilai-filter .filter-label {
        font-size: .65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #8592a3;
        margin-bottom: 6px;
        display: block;
    }
    .nilai-filter .form-select,
    .nilai-filter .select2-container--default .select2-selection--single {
        border: none !important;
        background: #f3f3f7 !important;
        font-size: .85rem;
        border-radius: 10px !important;
        min-height: 42px !important;
    }
    .nilai-filter .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px !important;
        padding-left: 12px;
    }
    .nilai-filter .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
    }

    /* Step Indicators */
    .filter-steps {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 1.25rem;
    }
    .filter-step {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .75rem;
        font-weight: 600;
        color: #a1acb8;
    }
    .filter-step.active {
        color: #696cff;
    }
    .filter-step .step-num {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .7rem;
        font-weight: 800;
        background: #f0f0f5;
        color: #a1acb8;
    }
    .filter-step.active .step-num {
        background: #696cff;
        color: #fff;
    }
    .filter-step.done .step-num {
        background: #71dd37;
        color: #fff;
    }
    .filter-step.done { color: #71dd37; }
    .step-connector {
        width: 30px;
        height: 2px;
        background: #e8e8ed;
        border-radius: 99px;
    }
    .step-connector.active { background: #696cff; }
    .step-connector.done { background: #71dd37; }

    /* Table Card */
    .nilai-table-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.05);
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0,0,0,.02);
        overflow: hidden;
    }
    .nilai-table-card .card-header-custom {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,.04);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .nilai-table-card .card-title-custom {
        font-size: .95rem;
        font-weight: 800;
        color: #384551;
    }
    .nilai-table-card .card-sub-custom {
        font-size: .72rem;
        color: #a1acb8;
    }

    /* Table Modern */
    .nilai-table thead th {
        background: #f8f8fb;
        font-size: .62rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #8592a3;
        border: none;
        padding: .75rem .85rem;
        white-space: nowrap;
    }
    .nilai-table thead th.th-komponen {
        text-align: center;
        min-width: 85px;
    }
    .nilai-table tbody td {
        padding: .65rem .85rem;
        vertical-align: middle;
        border-color: rgba(0,0,0,.04);
        font-size: .85rem;
    }
    .nilai-table tbody tr {
        transition: background .15s ease;
    }
    .nilai-table tbody tr:hover {
        background: rgba(105,108,255,.03);
    }

    /* Input Nilai */
    .nilai-table .komponen-nilai {
        border: 1px solid rgba(0,0,0,.08);
        background: #fafbfc;
        border-radius: 8px;
        text-align: center;
        font-weight: 600;
        font-size: .82rem;
        width: 75px;
        padding: .35rem .4rem;
        transition: all .15s ease;
    }
    .nilai-table .komponen-nilai:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 3px rgba(105,108,255,.12);
        background: #fff;
    }

    /* Student Info */
    .student-cell .student-name {
        font-weight: 700;
        font-size: .85rem;
        color: #384551;
    }
    .student-cell .student-nim {
        font-size: .72rem;
        color: #a1acb8;
        font-weight: 500;
    }

    /* Nilai Result */
    .nilai-result {
        font-size: .95rem;
        font-weight: 800;
        color: #384551;
    }
    .badge-mutu {
        font-size: .7rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 6px;
        letter-spacing: .04em;
    }

    /* Floating Save Bar */
    .save-bar {
        position: sticky;
        bottom: 0;
        background: rgba(255,255,255,.95);
        backdrop-filter: blur(12px);
        border-top: 1px solid rgba(0,0,0,.06);
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 10;
    }
    .save-bar .info-text {
        font-size: .78rem;
        color: #8592a3;
        font-weight: 500;
    }

    /* Bobot Panel Override */
    #bobot-panel {
        border-radius: 12px !important;
    }
    #bobot-panel .card-header {
        border-radius: 12px 12px 0 0 !important;
    }

    /* Empty State */
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
    }
    .empty-state .empty-icon {
        font-size: 3rem;
        color: #c2c6de;
        margin-bottom: .75rem;
    }
    .empty-state .empty-text {
        font-weight: 600;
        color: #a1acb8;
        font-size: .9rem;
    }
    .empty-state .empty-sub {
        font-size: .78rem;
        color: #c2c6de;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .nilai-hero { padding: 1.5rem; }
        .nilai-hero .hero-title { font-size: 1.1rem; }
        .nilai-hero .hero-illustration { display: none; }
        .filter-steps { display: none; }
    }
</style>
@endpush

@section('content')
<div class="input-nilai-page">

    {{-- Hero Card --}}
    <section class="mb-4">
        <div class="nilai-hero">
            <div class="hero-content">
                <h4 class="hero-title"><i class="bx bx-edit-alt me-2"></i>Input Nilai Mahasiswa</h4>
                <p class="hero-sub mb-0">
                    Pilih tahun ajaran, program studi, dan mata kuliah untuk mulai menginput nilai.
                    Nilai akhir & huruf mutu akan dihitung otomatis berdasarkan bobot yang telah dikonfigurasi.
                </p>
            </div>
            <img src="{{ asset('dashboard_assets/assets/img/illustrations/man-with-laptop.png') }}"
                 alt="Illustration" class="hero-illustration d-none d-lg-block">
        </div>
    </section>

    {{-- Filter Section --}}
    <section class="mb-4">
        <div class="nilai-filter">
            {{-- Step Indicators --}}
            <div class="filter-steps" id="filter-steps">
                <div class="filter-step active" id="step-1">
                    <span class="step-num">1</span> Tahun Ajaran
                </div>
                <div class="step-connector" id="conn-1"></div>
                <div class="filter-step" id="step-2">
                    <span class="step-num">2</span> Program Studi
                </div>
                <div class="step-connector" id="conn-2"></div>
                <div class="filter-step" id="step-3">
                    <span class="step-num">3</span> Mata Kuliah
                </div>
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="filter-label" for="tahun-ajaran">
                        <i class="bx bx-calendar me-1"></i> Tahun Ajaran
                    </label>
                    <select id="tahun-ajaran" class="form-select">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach ($tahunAjaran as $ta)
                        <option value="{{ $ta->ta_id }}">{{ $ta->nama }} ({{ $ta->semester }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="filter-label" for="program-studi">
                        <i class="bx bx-buildings me-1"></i> Program Studi
                    </label>
                    <select id="program-studi" class="form-select" disabled>
                        <option value="">-- Pilih Prodi --</option>
                        @foreach ($programStudi as $ps)
                        <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="filter-label" for="mata-kuliah">
                        <i class="bx bx-book-open me-1"></i> Mata Kuliah
                    </label>
                    <select id="mata-kuliah" class="form-select select2">
                        <option value="">-- Pilih Mata Kuliah --</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="button" id="clear-selection" class="btn btn-outline-danger w-100" style="border-radius: 10px; min-height: 42px;">
                        <i class="bx bx-x me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Table Section --}}
    <section>
        <div class="nilai-table-card">
            <div class="card-header-custom">
                <div>
                    <div class="card-title-custom">
                        <i class="bx bx-list-ul me-1 text-primary"></i> Daftar Mahasiswa
                    </div>
                    <div class="card-sub-custom" id="table-info">Pilih mata kuliah untuk menampilkan data</div>
                </div>
                <div id="table-actions" style="display: none;" class="d-flex align-items-center gap-2">
                    <span class="badge bg-label-primary me-2" id="count-badge" style="font-size: .72rem; display: none;"></span>
                    <a href="#" id="btn-export-excel" class="btn btn-sm btn-success shadow-sm" target="_blank">
                        <i class="bx bx-file me-1"></i> Excel
                    </a>
                    <a href="#" id="btn-export-pdf" class="btn btn-sm btn-danger shadow-sm" target="_blank">
                        <i class="bx bxs-file-pdf me-1"></i> PDF
                    </a>
                </div>
            </div>

            {{-- Bobot Panel Container --}}
            <div style="padding: 0 1.5rem;">
                <div id="bobot-panel-container"></div>
            </div>

            {{-- Table --}}
            <form id="form-nilai" method="POST" action="{{ route('admin.nilai.save') }}">
                @csrf
                <div class="table-responsive">
                    <table id="table-mahasiswa" class="table nilai-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px; padding-left: 1.5rem;">#</th>
                                <th style="min-width: 180px;">Mahasiswa</th>
                                <th class="th-komponen">UTS</th>
                                <th class="th-komponen">UAS</th>
                                <th class="th-komponen">TUGAS</th>
                                <th class="th-komponen">ABSEN</th>
                                <th class="th-komponen">PRAKTIK</th>
                                <th class="th-komponen">Absolute</th>
                                <th class="th-komponen">Huruf Mutu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="bx bx-search-alt"></i></div>
                                        <div class="empty-text">Silakan pilih mata kuliah</div>
                                        <div class="empty-sub">Ikuti langkah di atas untuk memulai input nilai</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Sticky Save Bar --}}
                <div class="save-bar" style="display: none;" id="save-bar">
                    <div class="info-text">
                        <i class="bx bx-info-circle me-1"></i>
                        Nilai akhir & huruf mutu dihitung otomatis berdasarkan bobot. Klik simpan untuk menyimpan ke database.
                    </div>
                    <button type="submit" class="btn btn-primary" id="save-nilai" style="border-radius: 10px; padding: .55rem 1.5rem;">
                        <i class="bx bx-save me-1"></i> Simpan Nilai
                    </button>
                </div>
            </form>
        </div>
    </section>

</div>

@push('head')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Step indicator logic
    function updateSteps() {
        const ta = document.getElementById('tahun-ajaran').value;
        const ps = document.getElementById('program-studi').value;
        const mk = document.getElementById('mata-kuliah').value;

        const s1 = document.getElementById('step-1');
        const s2 = document.getElementById('step-2');
        const s3 = document.getElementById('step-3');
        const c1 = document.getElementById('conn-1');
        const c2 = document.getElementById('conn-2');

        // Reset
        [s1,s2,s3].forEach(s => { s.className = 'filter-step'; });
        [c1,c2].forEach(c => { c.className = 'step-connector'; });

        if (mk) {
            s1.className = 'filter-step done';
            s2.className = 'filter-step done';
            s3.className = 'filter-step done';
            c1.className = 'step-connector done';
            c2.className = 'step-connector done';
        } else if (ps) {
            s1.className = 'filter-step done';
            s2.className = 'filter-step done';
            s3.className = 'filter-step active';
            c1.className = 'step-connector done';
            c2.className = 'step-connector active';
        } else if (ta) {
            s1.className = 'filter-step done';
            s2.className = 'filter-step active';
            c1.className = 'step-connector active';
        } else {
            s1.className = 'filter-step active';
        }
    }

    // Listen for changes
    ['tahun-ajaran', 'program-studi', 'mata-kuliah'].forEach(function(id) {
        document.getElementById(id).addEventListener('change', updateSteps);
    });

    // Update student count badge
    const observer = new MutationObserver(function() {
        const rows = document.querySelectorAll('#table-mahasiswa tbody tr');
        const badge = document.getElementById('count-badge');
        const actions = document.getElementById('table-actions');
        const saveBar = document.getElementById('save-bar');
        const hasData = rows.length > 0 && !rows[0].querySelector('.empty-state');

        if (hasData) {
            badge.textContent = rows.length + ' Mahasiswa';
            actions.style.display = '';
            saveBar.style.display = '';
        } else {
            actions.style.display = 'none';
            saveBar.style.display = 'none';
        }
    });

    observer.observe(document.querySelector('#table-mahasiswa tbody'), {
        childList: true, subtree: true
    });

    // Handle Export Buttons
    document.getElementById('btn-export-excel').addEventListener('click', function(e) {
        e.preventDefault();
        const mkId = document.getElementById('mata-kuliah').value;
        const taId = document.getElementById('tahun-ajaran').value;
        
        if (!mkId || !taId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Perhatian!', text: 'Silakan pilih Tahun Ajaran dan Mata Kuliah terlebih dahulu.', icon: 'warning' });
            } else {
                alert('Silakan pilih Tahun Ajaran dan Mata Kuliah terlebih dahulu.');
            }
            return;
        }
        
        window.open(`/admin/mahasiswa/input-nilai/export/${mkId}/${taId}/excel`, '_blank');
    });

    document.getElementById('btn-export-pdf').addEventListener('click', function(e) {
        e.preventDefault();
        const mkId = document.getElementById('mata-kuliah').value;
        const taId = document.getElementById('tahun-ajaran').value;
        
        if (!mkId || !taId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Perhatian!', text: 'Silakan pilih Tahun Ajaran dan Mata Kuliah terlebih dahulu.', icon: 'warning' });
            } else {
                alert('Silakan pilih Tahun Ajaran dan Mata Kuliah terlebih dahulu.');
            }
            return;
        }
        
        window.open(`/admin/mahasiswa/input-nilai/export/${mkId}/${taId}/pdf`, '_blank');
    });
});
</script>
@endpush
@endsection
