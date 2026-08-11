@extends('layouts.master')
@section('title', 'Tagihan Pembayaran')
@section('content')

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                        style="width:48px;height:48px;background:rgba(105,108,255,.1)">
                        <i class="bx bx-group text-primary" style="font-size:1.5rem"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Mahasiswa Aktif</small>
                        <h4 class="mb-0 fw-bold">{{ number_format($totalMahasiswa) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                        style="width:48px;height:48px;background:rgba(113,221,55,.1)">
                        <i class="bx bx-receipt text-success" style="font-size:1.5rem"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Tagihan</small>
                        <h4 class="mb-0 fw-bold text-dark">Rp {{ number_format($totalTagihanAll, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                        style="width:48px;height:48px;background:rgba(3,195,236,.1)">
                        <i class="bx bx-check-circle text-info" style="font-size:1.5rem"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Telah Dibayar</small>
                        <h4 class="mb-0 fw-bold text-success">Rp {{ number_format($totalDibayarAll, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                        style="width:48px;height:48px;background:rgba(255,62,29,.1)">
                        <i class="bx bx-error-circle text-danger" style="font-size:1.5rem"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Sisa Tunggakan</small>
                        <h4 class="mb-0 fw-bold text-danger">Rp {{ number_format($totalSisaAll, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h5 class="mb-1 fw-bold"><i class="bx bx-wallet me-1 text-primary"></i> Tagihan Mahasiswa</h5>
                    <small class="text-muted">Kelola tagihan dan pembayaran seluruh mahasiswa aktif</small>
                </div>
                <div>
                    <button id="generate-tagihan" class="btn btn-primary">
                        <i class="bx bx-refresh me-1"></i> Generate Tagihan
                    </button>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="card-body border-bottom py-3" style="background:#f8f9fe">
            <form method="GET" action="{{ route('admin.tagihan-mahasiswa.index') }}" id="filter-form">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small mb-1">Program Studi</label>
                        <select name="program_studi" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Prodi</option>
                            @foreach($programStudiList as $prodi)
                                <option value="{{ $prodi->jurusan_id }}" {{ request('program_studi') == $prodi->jurusan_id ? 'selected' : '' }}>{{ $prodi->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small mb-1">Tahun Masuk</label>
                        <select name="tahun_masuk" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Angkatan</option>
                            @foreach($tahunMasukList as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun_masuk') == $tahun ? 'selected' : '' }}>{{ $tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">Cari Mahasiswa</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama atau NIM..."
                                value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary btn-sm px-3">Cari</button>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        @if(request()->hasAny(['program_studi', 'tahun_masuk', 'search']))
                            <a href="{{ route('admin.tagihan-mahasiswa.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bx bx-x me-1"></i>Reset Filter
                            </a>
                        @endif
                        <span class="badge bg-label-primary ms-2">{{ $mahasiswaList->total() }} mahasiswa</span>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tbl-tagihan-mhs">
                <thead>
                    <tr style="background:#f1f3ff">
                        <th class="text-center" style="width:50px">#</th>
                        <th style="min-width:200px">Mahasiswa</th>
                        <th class="text-center">Semester</th>
                        <th class="text-end">Total Tagihan</th>
                        <th class="text-end">Dibayar</th>
                        <th class="text-end">Sisa</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width:140px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswaList as $i => $mhs)
                        <tr>
                            <td class="text-center text-muted">{{ $mahasiswaList->firstItem() + $i }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm rounded-circle d-flex align-items-center justify-content-center"
                                        style="width:36px;height:36px;background:rgba(105,108,255,.08);font-size:.8rem;font-weight:700;color:#696cff">
                                        {{ strtoupper(substr($mhs->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-semibold d-block" style="font-size:.875rem">{{ $mhs->nama }}</span>
                                        <small class="text-muted">{{ $mhs->nim }} &middot;
                                            {{ $mhs->programStudi->nama ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center"><span class="badge bg-label-info">Smt {{ $mhs->semester }}</span></td>
                            <td class="text-end fw-semibold">
                                {{ $mhs->total_tagihan > 0 ? 'Rp ' . number_format($mhs->total_tagihan, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end text-success">
                                {{ $mhs->total_dibayar > 0 ? 'Rp ' . number_format($mhs->total_dibayar, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end fw-bold {{ $mhs->sisa_tagihan > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ $mhs->total_tagihan > 0 ? 'Rp ' . number_format($mhs->sisa_tagihan, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-center">
                                @if($mhs->total_tagihan == 0)
                                    <span class="badge bg-label-secondary"><i class="bx bx-minus-circle me-1"></i>Belum Ada</span>
                                @elseif($mhs->sisa_tagihan <= 0)
                                    <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Lunas</span>
                                @else
                                    <span class="badge bg-label-danger"><i class="bx bx-error-circle me-1"></i>Tunggakan</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button class="btn btn-sm btn-outline-primary btn-detail d-flex align-items-center gap-1"
                                        data-id="{{ $mhs->mahasiswa_id }}" title="Detail Tagihan">
                                        <i class="bx bx-detail"></i> Detail
                                    </button>
                                    <button class="btn btn-sm btn-success btn-bayar d-flex align-items-center gap-1 shadow-sm"
                                        data-id="{{ $mhs->mahasiswa_id }}" title="Bayar Tagihan" {{ $mhs->sisa_tagihan <= 0 ? 'disabled' : '' }}>
                                        <i class="bx bx-wallet"></i> Bayar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bx bx-search-alt" style="font-size:2.5rem"></i>
                                    <p class="mt-2 mb-0">Tidak ada data mahasiswa ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($mahasiswaList->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <small class="text-muted">Menampilkan {{ $mahasiswaList->firstItem() }}–{{ $mahasiswaList->lastItem() }} dari
                    {{ $mahasiswaList->total() }}</small>
                {{ $mahasiswaList->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Modal Detail --}}
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-detail me-2"></i>Detail Tagihan — <span
                            id="detail-nama">-</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detail-body">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2 text-muted">Memuat data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Bayar --}}
    <div class="modal fade" id="modalBayar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-wallet me-2"></i>Bayar Tagihan — <span id="bayar-nama">-</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="bayar-body">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success"></div>
                        <p class="mt-2 text-muted">Memuat data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('script')
    <script>
        $(document).ready(function () {
            const CSRF = $('meta[name="csrf-token"]').attr('content');
            const fmt = n => new Intl.NumberFormat('id-ID').format(n);
            const fmtDate = d => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

            // ===== DETAIL MODAL =====
            $(document).on('click', '.btn-detail', function () {
                let id = $(this).data('id');
                $('#detail-body').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Memuat data...</p></div>');
                let modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));
                modalDetail.show();
                $.getJSON(`/admin/tagihan-mahasiswa/${id}/detail`, function (res) {
                    $('#detail-nama').text(res.mahasiswa.nama + ' (' + res.mahasiswa.nim + ')');

                    let html = `
                                                            <div class="row g-3 mb-4">
                                                                <div class="col-md-4">
                                                                    <div class="p-3 rounded-3" style="background:rgba(105,108,255,.06)">
                                                                        <small class="text-muted d-block mb-1">Total Tagihan</small>
                                                                        <h5 class="mb-0 fw-bold">Rp ${fmt(res.total_tagihan)}</h5>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="p-3 rounded-3" style="background:rgba(113,221,55,.06)">
                                                                        <small class="text-muted d-block mb-1">Telah Dibayar</small>
                                                                        <h5 class="mb-0 fw-bold text-success">Rp ${fmt(res.total_dibayar)}</h5>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="p-3 rounded-3" style="background:${res.sisa > 0 ? 'rgba(255,62,29,.06)' : 'rgba(113,221,55,.06)'}">
                                                                        <small class="text-muted d-block mb-1">Sisa</small>
                                                                        <h5 class="mb-0 fw-bold ${res.sisa > 0 ? 'text-danger' : 'text-success'}">Rp ${fmt(res.sisa)}</h5>
                                                                    </div>
                                                                </div>
                                                            </div>`;

                    // Tagihan table
                    html += `<h6 class="fw-bold mb-2"><i class="bx bx-list-ul me-1 text-primary"></i>Rincian Tagihan per Tenor</h6>
                                                            <div class="table-responsive mb-4">
                                                                <table class="table table-sm table-bordered mb-0">
                                                                    <thead class="table-light"><tr>
                                                                        <th class="text-center">#</th><th>Tenor</th><th class="text-center">Smt</th>
                                                                        <th class="text-end">Tagihan</th><th class="text-end">Dibayar</th><th class="text-end">Sisa</th>
                                                                        <th class="text-center">Jatuh Tempo</th><th class="text-center">Status</th><th class="text-center">Aksi</th>
                                                                    </tr></thead><tbody>`;

                    if (res.tagihan.length) {
                        res.tagihan.forEach((t, i) => {
                            let badge = t.status === 'lunas'
                                ? '<span class="badge bg-success">Lunas</span>'
                                : '<span class="badge bg-danger">Belum Lunas</span>';
                            let actionBtn = t.sisa > 0
                                ? `<button class="btn btn-xs btn-success btn-bayar-langsung" data-mhs="${id}" data-tagihan="${t.id}">Bayar</button>`
                                : '-';
                            html += `<tr>
                                                                        <td class="text-center">${i + 1}</td>
                                                                        <td class="fw-semibold">${t.tenor}</td>
                                                                        <td class="text-center">${t.semester}</td>
                                                                        <td class="text-end">Rp ${fmt(t.jumlah_tagihan)}</td>
                                                                        <td class="text-end text-success">Rp ${fmt(t.dibayar)}</td>
                                                                        <td class="text-end fw-bold ${t.sisa > 0 ? 'text-danger' : ''}">Rp ${fmt(t.sisa)}</td>
                                                                        <td class="text-center">${fmtDate(t.jatuh_tempo)}</td>
                                                                        <td class="text-center">${badge}</td>
                                                                        <td class="text-center">${actionBtn}</td>
                                                                    </tr>`;
                        });
                    } else {
                        html += '<tr><td colspan="9" class="text-center text-muted py-3">Belum ada tagihan.</td></tr>';
                    }
                    html += '</tbody></table></div>';

                    // Riwayat pembayaran
                    let allTrx = [];
                    res.tagihan.forEach(t => {
                        t.transaksi.forEach(tr => {
                            allTrx.push({ ...tr, tenor: t.tenor });
                        });
                    });
                    allTrx.sort((a, b) => new Date(b.tanggal_bayar) - new Date(a.tanggal_bayar));

                    html += `<h6 class="fw-bold mb-2"><i class="bx bx-history me-1 text-primary"></i>Riwayat Pembayaran</h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered mb-0">
                                                                    <thead class="table-light"><tr>
                                                                        <th class="text-center">#</th><th>Tanggal</th><th>Tenor</th><th class="text-end">Nominal</th><th>Keterangan</th>
                                                                    </tr></thead><tbody>`;
                    if (allTrx.length) {
                        allTrx.forEach((tr, i) => {
                            html += `<tr>
                                                                        <td class="text-center">${i + 1}</td>
                                                                        <td>${fmtDate(tr.tanggal_bayar)}</td>
                                                                        <td>${tr.tenor}</td>
                                                                        <td class="text-end text-success fw-semibold">+ Rp ${fmt(tr.nominal_bayar)}</td>
                                                                        <td>${tr.keterangan || '-'}</td>
                                                                    </tr>`;
                        });
                    } else {
                        html += '<tr><td colspan="5" class="text-center text-muted py-3">Belum ada riwayat pembayaran.</td></tr>';
                    }
                    html += '</tbody></table></div>';
                    $('#detail-body').html(html);
                }).fail(function (xhr) {
                    $('#detail-body').html('<div class="alert alert-danger">Gagal memuat data detail.</div>');
                });
            });

            // ===== BAYAR MODAL =====
            $(document).on('click', '.btn-bayar-langsung', function () {
                let mhsId = $(this).data('mhs');
                let tagihanId = $(this).data('tagihan');
                $('#modalDetail').modal('hide');

                setTimeout(function () {
                    openModalBayar(mhsId, tagihanId);
                }, 400); // Tunggu animasi tutup modal detail
            });

            $(document).on('click', '.btn-bayar', function () {
                let id = $(this).data('id');
                openModalBayar(id, null);
            });

            function openModalBayar(id, preselectTagihanId = null) {
                $('#bayar-body').html('<div class="text-center py-5"><div class="spinner-border text-success"></div><p class="mt-2 text-muted">Memuat data...</p></div>');
                let modalBayar = new bootstrap.Modal(document.getElementById('modalBayar'));
                modalBayar.show();
                $.getJSON(`/admin/tagihan-mahasiswa/${id}/belum-lunas`, function (res) {
                    $('#bayar-nama').text(res.mahasiswa.nama + ' (' + res.mahasiswa.nim + ')');

                    if (!res.tagihan.length) {
                        $('#bayar-body').html('<div class="alert alert-success mb-0"><i class="bx bx-check-circle me-1"></i>Semua tagihan sudah lunas.</div>');
                        return;
                    }

                    let opts = '';
                    res.tagihan.forEach(t => {
                        let isSelected = (preselectTagihanId == t.id) ? 'selected' : '';
                        opts += `<option value="${t.id}" data-sisa="${t.sisa}" ${isSelected}>${t.tenor} (Smt ${t.semester}) — Sisa: Rp ${fmt(t.sisa)}</option>`;
                    });

                    let html = `
                                                            <div id="alert-bayar" class="d-none"></div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Pilih Tagihan</label>
                                                                <select id="select-tagihan-bayar" class="form-select">
                                                                    <option value="">-- Pilih Tagihan --</option>
                                                                    ${opts}
                                                                </select>
                                                            </div>
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">Nominal Bayar (Rp)</label>
                                                                    <input type="number" id="inp-nominal" class="form-control" min="1" placeholder="0">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">Tanggal Bayar</label>
                                                                    <input type="date" id="inp-tanggal" class="form-control" value="${new Date().toISOString().slice(0, 10)}">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label fw-semibold">Keterangan</label>
                                                                    <input type="text" id="inp-keterangan" class="form-control" placeholder="Contoh: Transfer Mandiri An. Ahmad">
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="d-flex justify-content-end gap-2">
                                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="button" class="btn btn-success" id="btn-submit-bayar" disabled>
                                                                    <i class="bx bx-check me-1"></i>Simpan Pembayaran
                                                                </button>
                                                            </div>`;
                    $('#bayar-body').html(html);

                    // Jika ada pre-selected, otomatis panggil trigger change
                    if (preselectTagihanId) {
                        $('#select-tagihan-bayar').trigger('change');
                    }

                    // Pilih tagihan → set nominal default
                    $('#select-tagihan-bayar').change(function () {
                        let sisa = $(this).find(':selected').data('sisa') || 0;
                        $('#inp-nominal').val(sisa > 0 ? sisa : '');
                        $('#btn-submit-bayar').prop('disabled', !$(this).val());
                    });

                    // Submit pembayaran
                    $('#btn-submit-bayar').click(function () {
                        let tagihanId = $('#select-tagihan-bayar').val();
                        let nom = $('#inp-nominal').val();
                        if (!tagihanId || !nom || nom <= 0) {
                            showBayarAlert('danger', 'Pilih tagihan dan isi nominal.');
                            return;
                        }
                        let btn = $(this);
                        btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Menyimpan...');
                        $.ajax({
                            url: `/admin/tagihan-mahasiswa/${tagihanId}/update-pembayaran`,
                            type: 'POST',
                            data: {
                                _token: CSRF,
                                nominal_bayar: nom,
                                tanggal_bayar: $('#inp-tanggal').val(),
                                keterangan: $('#inp-keterangan').val()
                            },
                            success: function (r) {
                                if (r.success) {
                                    showBayarAlert('success', r.message + ' Halaman akan diperbarui...');
                                    setTimeout(() => location.reload(), 1500);
                                } else {
                                    showBayarAlert('danger', r.message || 'Gagal.');
                                    btn.prop('disabled', false).html('<i class="bx bx-check me-1"></i>Simpan Pembayaran');
                                }
                            },
                            error: function (xhr) {
                                showBayarAlert('danger', xhr.responseJSON?.message || 'Gagal menyimpan.');
                                btn.prop('disabled', false).html('<i class="bx bx-check me-1"></i>Simpan Pembayaran');
                            }
                        });
                    });
                }).fail(function () {
                    $('#bayar-body').html('<div class="alert alert-danger">Gagal memuat data tagihan.</div>');
                });
            }

            function showBayarAlert(type, msg) {
                $('#alert-bayar').removeClass('d-none').attr('class', 'alert alert-' + type).html(msg);
            }

            // ===== GENERATE TAGIHAN =====
            $('#generate-tagihan').click(function () {
                Swal.fire({
                    title: 'Konfirmasi', text: 'Yakin ingin generate tagihan untuk seluruh Mahasiswa Aktif?',
                    icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Generate!', cancelButtonText: 'Batal'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    let btn = $(this), ori = btn.html();
                    $.ajax({
                        url: @json(route('admin.generate')), type: 'POST', data: { _token: CSRF },
                        beforeSend: () => btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Generating...'),
                        success: function (res) {
                            Swal.fire({ title: res.success ? 'Berhasil!' : 'Info', text: res.message, icon: res.success ? 'success' : 'info', timer: 3000, showConfirmButton: false })
                                .then(() => { if (res.success) location.reload(); });
                        },
                        error: function (xhr) {
                            Swal.fire({ title: 'Gagal!', text: xhr.responseJSON?.message || 'Terjadi kesalahan.', icon: 'error' });
                        },
                        complete: () => btn.prop('disabled', false).html(ori)
                    });
                });
            });
        });
    </script>
@endpush