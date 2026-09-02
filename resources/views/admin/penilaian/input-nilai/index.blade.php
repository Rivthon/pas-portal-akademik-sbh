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

    /* Input Nilai & Input Absolute */
    .nilai-table .komponen-nilai,
    .nilai-table .input-nilai-akhir {
        border: 1px solid rgba(0,0,0,.08);
        background: #fafbfc;
        border-radius: 8px;
        text-align: center;
        font-weight: 600;
        font-size: .82rem;
        width: 80px;
        padding: .35rem .4rem;
        transition: all .15s ease;
    }
    .nilai-table .input-nilai-akhir {
        background: #f0f2ff;
        border-color: #b2b4ff;
        color: #566a7f;
        font-weight: 800;
    }
    .nilai-table .komponen-nilai:focus,
    .nilai-table .input-nilai-akhir:focus {
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
    .badge-mutu {
        font-size: .75rem;
        font-weight: 800;
        padding: 6px 12px;
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
                    Nilai akhir & huruf mutu akan dihitung otomatis berdasarkan bobot, namun BAAK dapat mengedit nilai absolut secara manual.
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
                        Nilai akhir & huruf mutu terhitung otomatis. BAAK dapat mengubah nilai absolut jika diperlukan sebelum menyimpan.
                    </div>
                    <button type="submit" class="btn btn-primary" id="save-nilai" style="border-radius: 10px; padding: .55rem 1.5rem;">
                        <i class="bx bx-save me-1"></i> Simpan Nilai
                    </button>
                </div>
            </form>
        </div>
    </section>

</div>

@push('script')
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

$(document).ready(function () {
    // Initialize Select2
    $("#tahun-ajaran, #program-studi, #mata-kuliah").select2({
        allowClear: true,
        placeholder: "Pilih opsi",
        width: '100%'
    });

    // DOM Elements
    const tableBody = $("#table-mahasiswa tbody");
    const saveButton = $("#save-nilai");
    const loadingSpinner = $("#loading-spinner");
    const errorMessage = $("#error-message");

    // Global bobot storage
    let currentBobot = { uts: 25, uas: 35, tugas: 20, absensi: 10, praktik: 10 };
    let currentKonfigurasi = {};

    // Utility Functions
    const showTableMessage = (message, sub) => {
        const subHtml = sub ? `<div class="empty-sub">${sub}</div>` : '';
        tableBody.html(`<tr><td colspan="9"><div class="empty-state"><div class="empty-icon"><i class="bx bx-search-alt"></i></div><div class="empty-text">${message}</div>${subHtml}</div></td></tr>`);
        $('#table-info').text(message);
        $('#save-bar').hide();
    };

    const showError = (message) => {
        if (typeof toastr !== 'undefined') {
            toastr.error(message, 'Error!');
        } else {
            errorMessage.text(message).fadeIn().delay(3000).fadeOut();
        }
    };

    const setLoading = (isLoading) => {
        if (isLoading) {
            loadingSpinner.show();
            $("select").prop("disabled", true);
        } else {
            loadingSpinner.hide();
            $("select").not("#program-studi, #mata-kuliah").prop("disabled", false);

            if ($("#tahun-ajaran").val()) {
                $("#program-studi").prop("disabled", false);
            }
            if ($("#program-studi").val()) {
                $("#mata-kuliah").prop("disabled", false);
            }
        }
    };

    // Calculate grade from score
    const calculateGrade = (score) => {
        if (score >= 85.5) return 'A';
        if (score >= 78.5) return 'AB';
        if (score >= 74.5) return 'BA';
        if (score >= 70.5) return 'B';
        if (score >= 66.5) return 'BC';
        if (score >= 59.5) return 'C';
        if (score >= 45.5) return 'D';
        return 'E';
    };

    // Update Badge Huruf Mutu
    const updateBadgeMutu = ($row, hurufMutu) => {
        const $badge = $row.find('.nilai-huruf');
        $badge.text(hurufMutu);
        
        // Reset Kelas Badge
        $badge.removeClass('bg-success bg-primary bg-warning bg-danger');

        // Tetapkan Warna Baru
        if (hurufMutu === 'A' || hurufMutu === 'AB') {
            $badge.addClass('bg-success');
        } else if (hurufMutu === 'BA' || hurufMutu === 'B') {
            $badge.addClass('bg-primary');
        } else if (hurufMutu === 'BC' || hurufMutu === 'C') {
            $badge.addClass('bg-warning');
        } else {
            $badge.addClass('bg-danger');
        }
    };

    // Auto-calculate row dari Komponen Nilai
    const autoCalcRow = ($row) => {
        const uts = parseFloat($row.find('input[name^="uts"]').val()) || 0;
        const uas = parseFloat($row.find('input[name^="uas"]').val()) || 0;
        const tugas = parseFloat($row.find('input[name^="tugas"]').val()) || 0;
        const absensi = parseFloat($row.find('input[name^="absensi"]').val()) || 0;
        const praktik = parseFloat($row.find('input[name^="praktik"]').val()) || 0;

        const nilaiAkhir = (
            (uts * currentBobot.uts / 100) +
            (uas * currentBobot.uas / 100) +
            (tugas * currentBobot.tugas / 100) +
            (absensi * currentBobot.absensi / 100) +
            (praktik * currentBobot.praktik / 100)
        ).toFixed(2);

        const hurufMutu = calculateGrade(parseFloat(nilaiAkhir));

        // Update nilai pada Input Absolute & Huruf Mutu
        $row.find('.input-nilai-akhir').val(nilaiAkhir);
        updateBadgeMutu($row, hurufMutu);
    };

    // Render bobot panel
    const renderBobotPanel = (bobot, konfigurasi) => {
        const source = konfigurasi.bobot_source === 'custom' ?
            '<span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Custom MK</span>' :
            '<span class="badge bg-label-warning"><i class="bx bx-info-circle me-1"></i>Default Prodi</span>';

        const totalPersen = parseFloat(bobot.uts) + parseFloat(bobot.uas) + parseFloat(bobot.tugas) + parseFloat(bobot.absensi) + parseFloat(bobot.praktik);

        const html = `
                <div id="bobot-panel" class="card mb-3 border" style="border-color: rgba(105,108,255,.15) !important;">
                    <div class="card-header d-flex align-items-center justify-content-between py-2" style="background: rgba(105,108,255,.04);">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bx bx-calculator text-primary"></i>
                            <span class="fw-bold" style="font-size: .85rem;">Bobot Penilaian</span>
                            ${source}
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted" style="font-size: .75rem;">Total: <strong class="${totalPersen === 100 ? 'text-success' : 'text-danger'}">${totalPersen}%</strong></span>
                            <button type="button" id="btn-edit-bobot" class="btn btn-sm btn-outline-primary" style="font-size: .72rem;">
                                <i class="bx bx-edit-alt me-1"></i>Ubah Bobot
                            </button>
                        </div>
                    </div>
                    <div id="bobot-display" class="card-body py-2">
                        <div class="d-flex flex-wrap gap-3">
                            <span class="badge bg-label-primary">UTS: ${bobot.uts}%</span>
                            <span class="badge bg-label-primary">UAS: ${bobot.uas}%</span>
                            <span class="badge bg-label-primary">Tugas: ${bobot.tugas}%</span>
                            <span class="badge bg-label-primary">Absen: ${bobot.absensi}%</span>
                            <span class="badge bg-label-primary">Praktik: ${bobot.praktik}%</span>
                        </div>
                    </div>
                    <div id="bobot-edit" class="card-body py-3" style="display: none;">
                        <div class="row g-2 align-items-end">
                            <div class="col">
                                <label class="form-label mb-1" style="font-size: .7rem; font-weight: 700;">UTS (%)</label>
                                <input type="number" class="form-control form-control-sm bobot-input" id="bobot-uts" value="${bobot.uts}" min="0" max="100">
                            </div>
                            <div class="col">
                                <label class="form-label mb-1" style="font-size: .7rem; font-weight: 700;">UAS (%)</label>
                                <input type="number" class="form-control form-control-sm bobot-input" id="bobot-uas" value="${bobot.uas}" min="0" max="100">
                            </div>
                            <div class="col">
                                <label class="form-label mb-1" style="font-size: .7rem; font-weight: 700;">Tugas (%)</label>
                                <input type="number" class="form-control form-control-sm bobot-input" id="bobot-tugas" value="${bobot.tugas}" min="0" max="100">
                            </div>
                            <div class="col">
                                <label class="form-label mb-1" style="font-size: .7rem; font-weight: 700;">Absen (%)</label>
                                <input type="number" class="form-control form-control-sm bobot-input" id="bobot-absen" value="${bobot.absensi}" min="0" max="100">
                            </div>
                            <div class="col">
                                <label class="form-label mb-1" style="font-size: .7rem; font-weight: 700;">Praktik (%)</label>
                                <input type="number" class="form-control form-control-sm bobot-input" id="bobot-praktik" value="${bobot.praktik}" min="0" max="100">
                            </div>
                            <div class="col-auto">
                                <button type="button" id="btn-save-bobot" class="btn btn-sm btn-primary">
                                    <i class="bx bx-save me-1"></i>Simpan
                                </button>
                                <button type="button" id="btn-cancel-bobot" class="btn btn-sm btn-outline-secondary">Batal</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

        $('#bobot-panel').remove();
        $('#bobot-panel-container').html(html);
    };

    // Update table header with bobot %
    const updateTableHeader = (bobot) => {
        $('.th-komponen').eq(0).html(`UTS <small class="text-muted badge bg-label-secondary ms-1">(${bobot.uts}%)</small>`);
        $('.th-komponen').eq(1).html(`UAS <small class="text-muted badge bg-label-secondary ms-1">(${bobot.uas}%)</small>`);
        $('.th-komponen').eq(2).html(`TUGAS <small class="text-muted badge bg-label-secondary ms-1">(${bobot.tugas}%)</small>`);
        $('.th-komponen').eq(3).html(`ABSEN <small class="text-muted badge bg-label-secondary ms-1">(${bobot.absensi}%)</small>`);
        $('.th-komponen').eq(4).html(`PRAKTIK <small class="text-muted badge bg-label-secondary ms-1">(${bobot.praktik}%)</small>`);
    };

    // Tahun Ajaran Change Handler
    $("#tahun-ajaran").on("change", function () {
        const tahunAjaranId = $(this).val();
        $("#program-studi").val(null).trigger("change");
        $("#mata-kuliah").val(null).trigger("change");
        $("#program-studi").prop("disabled", !tahunAjaranId);
        $('#bobot-panel-container').empty();
        showTableMessage(
            tahunAjaranId ? "Silakan pilih program studi" : "Silakan pilih tahun ajaran",
            "Ikuti langkah di atas untuk memulai input nilai"
        );
    });

    // Program Studi Change Handler
    $("#program-studi").on("change", async function () {
        const programStudiId = $(this).val();
        const tahunAjaranId = $("#tahun-ajaran").val();
        const mataKuliahSelect = $("#mata-kuliah");

        mataKuliahSelect.val(null).trigger("change");
        showTableMessage("Memuat data mata kuliah...", "Mohon tunggu sebentar");
        saveButton.hide();
        $('#save-bar').hide();
        $('#bobot-panel-container').empty();

        if (!programStudiId || !tahunAjaranId) return;

        setLoading(true);

        try {
            const response = await $.ajax({
                url: `/admin/mata-kuliah/${programStudiId}/${tahunAjaranId}`,
                method: 'GET',
                dataType: 'json'
            });

            mataKuliahSelect.empty().append('<option value=""></option>');

            if (response.length > 0) {
                $.each(response, function (index, mk) {
                    mataKuliahSelect.append(
                        `<option value="${mk.matakuliah_id}">
                            ${mk.nama} (${mk.matakuliah_id}) - Semester ${mk.smt}
                        </option>`
                    );
                });
                mataKuliahSelect.prop("disabled", false);
                showTableMessage("Silakan pilih mata kuliah", "Pilih mata kuliah dari dropdown di atas");
            } else {
                showTableMessage("Tidak ada mata kuliah tersedia");
                showError("Tidak ditemukan mata kuliah untuk program studi ini");
                mataKuliahSelect.prop("disabled", false);
            }
        } catch (error) {
            console.error("Error:", error);
            showError("Gagal memuat mata kuliah");
            showTableMessage("Gagal memuat data");
            mataKuliahSelect.prop("disabled", false);
        } finally {
            setLoading(false);
        }
    });

    // Mata Kuliah Change Handler — load mahasiswa + bobot
    $("#mata-kuliah").on("change", async function () {
        const mataKuliahId = $(this).val();
        const tahunAjaranId = $("#tahun-ajaran").val();

        tableBody.empty();
        saveButton.hide();
        $('#bobot-panel-container').empty();

        if (!mataKuliahId || !tahunAjaranId) {
            showTableMessage("Silakan pilih mata kuliah", "Pilih mata kuliah dari dropdown di atas");
            return;
        }

        setLoading(true);
        showTableMessage("Memuat data mahasiswa...", "Mohon tunggu sebentar");

        try {
            const response = await $.ajax({
                url: `/admin/mahasiswa/input-nilai/${mataKuliahId}/${tahunAjaranId}`,
                method: 'GET',
                dataType: 'json'
            });

            if (response.mahasiswa && response.mahasiswa.length > 0) {
                currentBobot = response.konfigurasi.bobot;
                currentKonfigurasi = response.konfigurasi;

                renderBobotPanel(currentBobot, currentKonfigurasi);
                updateTableHeader(currentBobot);

                renderMahasiswaTable(response.mahasiswa);
                saveButton.show();
                $('#save-bar').show();
                $('#table-info').text(response.mahasiswa.length + ' Mahasiswa ditemukan');

                $('#table-actions').show();
                $('#btn-export-excel').attr('href', `/admin/mahasiswa/input-nilai/export/${mataKuliahId}/${tahunAjaranId}/excel`);
                $('#btn-export-pdf').attr('href', `/admin/mahasiswa/input-nilai/export/${mataKuliahId}/${tahunAjaranId}/pdf`);

            } else {
                showTableMessage("Tidak ada mahasiswa", "Tidak ada mahasiswa aktif yang mengambil mata kuliah ini");
                $('#table-actions').hide();
            }
        } catch (error) {
            console.error("Error:", error);
            const errMsg = error.responseJSON?.message || "Gagal memuat data mahasiswa";
            showError(errMsg);
            showTableMessage("Gagal memuat data", errMsg);
        } finally {
            setLoading(false);
            $("#mata-kuliah").prop("disabled", false);
        }
    });

    // Render Mahasiswa Table
    const renderMahasiswaTable = (students) => {
        tableBody.empty();

        $.each(students, function (index, mhs) {
            const uts = parseFloat(mhs.uts) || 0;
            const uas = parseFloat(mhs.uas) || 0;
            const tugas = parseFloat(mhs.tugas) || 0;
            const absen = parseFloat(mhs.absen) || 0;
            const praktik = parseFloat(mhs.praktik) || 0;

            // Gunakan Absolute tersimpan; hitung otomatis hanya jika belum pernah disimpan.
            const nilaiAkhirTersimpan = mhs.nilai_akhir ?? mhs.akhir;
            let nilaiAkhir = nilaiAkhirTersimpan !== null && nilaiAkhirTersimpan !== ''
                ? parseFloat(nilaiAkhirTersimpan).toFixed(2)
                : (
                (uts * currentBobot.uts / 100) +
                (uas * currentBobot.uas / 100) +
                (tugas * currentBobot.tugas / 100) +
                (absen * currentBobot.absensi / 100) +
                (praktik * currentBobot.praktik / 100)
            ).toFixed(2);

            const hurufMutu = calculateGrade(parseFloat(nilaiAkhir));

            const row = `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="student-cell">
                            <div class="student-name">${mhs.nama}</div>
                            <div class="student-nim">${mhs.nim}</div>
                        </td>
                        <td>
                            <input type="hidden" name="krs_id[${mhs.mahasiswa_id}]" value="${mhs.krs_id || ''}">
                            <input type="number" step="0.01" name="uts[${mhs.mahasiswa_id}]"
                                class="form-control form-control-sm komponen-nilai" value="${mhs.uts || ''}"
                                min="0" max="100">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="uas[${mhs.mahasiswa_id}]"
                                class="form-control form-control-sm komponen-nilai" value="${mhs.uas || ''}"
                                min="0" max="100">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="tugas[${mhs.mahasiswa_id}]"
                                class="form-control form-control-sm komponen-nilai" value="${mhs.tugas || ''}"
                                min="0" max="100">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="absensi[${mhs.mahasiswa_id}]"
                                class="form-control form-control-sm komponen-nilai" value="${mhs.absen || ''}"
                                min="0" max="100">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="praktik[${mhs.mahasiswa_id}]"
                                class="form-control form-control-sm komponen-nilai" value="${mhs.praktik || ''}"
                                min="0" max="100">
                        </td>
                        <td class="text-center">
                            <!-- PERBAIKAN BAAK: Mengubah nilai absolut menjadi input number -->
                            <input type="number" step="0.01" name="nilai_akhir[${mhs.mahasiswa_id}]"
                                class="form-control form-control-sm input-nilai-akhir" value="${nilaiAkhir}"
                                min="0" max="100">
                        </td>
                        <td class="text-center">
                            <span class="badge ${hurufMutu === 'A' || hurufMutu === 'AB' ? 'bg-success' : hurufMutu === 'BA' || hurufMutu === 'B' ? 'bg-primary' : hurufMutu === 'BC' || hurufMutu === 'C' ? 'bg-warning' : 'bg-danger'} nilai-huruf badge-mutu">${hurufMutu}</span>
                        </td>
                    </tr>
                `;
            tableBody.append(row);
        });
    };

    // Auto-calculate ketika komponen nilai diubah
    $(document).on("input", ".komponen-nilai", function () {
        const $row = $(this).closest("tr");
        autoCalcRow($row);
    });

    // PERBAIKAN BAAK: Auto-update Huruf Mutu ketika Nilai Absolut diubah manual oleh BAAK
    $(document).on("input", ".input-nilai-akhir", function () {
        const $row = $(this).closest("tr");
        const score = parseFloat($(this).val()) || 0;
        const hurufMutu = calculateGrade(score);
        updateBadgeMutu($row, hurufMutu);
    });

    // Bobot panel: toggle edit mode
    $(document).on("click", "#btn-edit-bobot", function () {
        $('#bobot-display').hide();
        $('#bobot-edit').show();
    });
    $(document).on("click", "#btn-cancel-bobot", function () {
        $('#bobot-edit').hide();
        $('#bobot-display').show();
    });

    // Bobot panel: save bobot via AJAX
    $(document).on("click", "#btn-save-bobot", async function () {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Menyimpan...');

        const newBobot = {
            program_studi_id: currentKonfigurasi.program_studi_id,
            matakuliah_id: currentKonfigurasi.matakuliah_id,
            persen_uts: parseFloat($('#bobot-uts').val()) || 0,
            persen_uas: parseFloat($('#bobot-uas').val()) || 0,
            persen_tugas: parseFloat($('#bobot-tugas').val()) || 0,
            persen_absen: parseFloat($('#bobot-absen').val()) || 0,
            persen_praktik: parseFloat($('#bobot-praktik').val()) || 0,
        };

        try {
            const response = await $.ajax({
                url: '{{ route("admin.bobot-nilai.save") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ...newBobot,
                },
                dataType: 'json'
            });

            if (response.success) {
                currentBobot = {
                    uts: newBobot.persen_uts,
                    uas: newBobot.persen_uas,
                    tugas: newBobot.persen_tugas,
                    absensi: newBobot.persen_absen,
                    praktik: newBobot.persen_praktik,
                };
                currentKonfigurasi.bobot = currentBobot;
                currentKonfigurasi.bobot_source = 'custom';

                renderBobotPanel(currentBobot, currentKonfigurasi);
                updateTableHeader(currentBobot);

                $('#table-mahasiswa tbody tr').each(function () {
                    autoCalcRow($(this));
                });

                if (typeof toastr !== 'undefined') {
                    toastr.success('Bobot berhasil disimpan & nilai diperbarui.', 'Berhasil!');
                }
            } else {
                showError(response.message || 'Gagal menyimpan bobot');
            }
        } catch (error) {
            console.error("Error:", error);
            showError("Gagal menyimpan bobot nilai");
        } finally {
            btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i>Simpan');
        }
    });

    // Form Submission Handler
    $("#form-nilai").on("submit", async function (e) {
        e.preventDefault();

        const form = this;
        const formData = $(form).serialize();
        const originalText = saveButton.text();

        saveButton.prop("disabled", true).text("Menyimpan...");
        setLoading(true);

        try {
            const response = await $.ajax({
                url: $(form).attr("action"),
                method: 'POST',
                data: formData,
                dataType: 'json'
            });

            if (response.success) {
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message, 'Berhasil!', { timeOut: 3000, progressBar: true });
                } else {
                    Swal.fire({ title: 'Berhasil!', text: response.message, icon: 'success', timer: 2000, showConfirmButton: false });
                }
            } else {
                showError(response.message || "Gagal menyimpan data");
            }
        } catch (error) {
            console.error("Error:", error);
            showError("Terjadi kesalahan saat menyimpan data");
        } finally {
            saveButton.prop("disabled", false).text(originalText);
            setLoading(false);
        }
    });
});

// Clear Selection Button
$("#clear-selection").on("click", function () {
    $("#mata-kuliah").val(null).trigger("change").prop("disabled", false);
    $("#program-studi").val(null).trigger("change");
    showTableMessage("Silakan pilih mata kuliah", "Ikuti langkah di atas untuk memulai input nilai");
    $("#save-nilai").hide();
    $('#save-bar').hide();
    $('#bobot-panel-container').empty();
    const tahunAjaranSelected = $("#tahun-ajaran").val();
    $("#program-studi").prop("disabled", !tahunAjaranSelected);
    $("#mata-kuliah").prop("disabled", false);
});
</script>
@endpush
@endsection
