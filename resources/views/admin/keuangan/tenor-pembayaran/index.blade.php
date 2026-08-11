@extends('layouts.master')
@section('title', 'Tenor Pembayaran')
@section('content')
<div class="card shadow-sm mb-4 border-0">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Tenor / Skema Cicilan Pembayaran
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Atur batas waktu jatuh tempo dan persentase setiap cicilan tagihan. Tenor akan mempengaruhi kalkulasi kewajiban finansial per angkatan mahasiswa.
                </p>
                <div class="mb-3">
                    <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#collapseBulkAdd">
                        <i class="bx bx-plus"></i> Tambah Tenor
                    </button>
                </div>
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center d-none d-md-block">
            <div class="p-3">
                <img src="../assets/img/illustrations/kartu-study.png" class="img-fluid opacity-75" alt="Tenor Illustration" style="max-height: 180px;">
            </div>
        </div>
    </div>
</div>

<!-- Bulk Add Form Section -->
<div class="collapse mb-4" id="collapseBulkAdd">
    <div class="card shadow-sm border-primary" style="border-top: 3px solid #696cff;">
        <div class="card-header bg-white pb-2 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold"><i class="bx bx-list-plus"></i> Input Tenor Masal (Bulk)</h5>
            <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#collapseBulkAdd" aria-label="Close"></button>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.tenor-pembayaran.storeBulk') }}" method="POST" id="formBulkTenor">
                @csrf
                <div class="table-responsive mb-3" style="overflow-visible: true;">
                    <table class="table table-bordered table-hover" id="bulkTable">
                        <thead class="table-light">
                            <tr>
                                <th>Tahun Angkatan <span class="text-danger">*</span></th>
                                <th>Gelombang <span class="text-danger">*</span></th>
                                <th>Semester <span class="text-danger">*</span></th>
                                <th>Nama Cicilan <span class="text-danger">*</span></th>
                                <th>Persentase (%) <span class="text-danger">*</span></th>
                                <th>Batas Waktu <span class="text-danger">*</span></th>
                                <th width="50px" class="text-center"><i class="bx bx-cog"></i></th>
                            </tr>
                        </thead>
                        <tbody id="bulkTbody">
                            <!-- Baris pertama (default) -->
                            <tr class="bulk-row">
                                <td>
                                    <select name="tenor_data[0][tahun_masuk]" class="form-select form-select-sm" required>
                                        <option value="">-- Tahun --</option>
                                        @foreach($tahunMasukList as $thn)
                                            <option value="{{ $thn }}">{{ $thn }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="tenor_data[0][gelombang_id]" class="form-select form-select-sm" required>
                                        <option value="">-- Gelombang --</option>
                                        @foreach($gelombangsList as $gel)
                                            <option value="{{ $gel->id }}">{{ $gel->nama }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="tenor_data[0][semester]" class="form-select form-select-sm" required>
                                        <option value="">-- Smt --</option>
                                        @for ($i=1; $i<=14; $i++)
                                            <option value="{{ $i }}">Smt {{ $i }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td>
                                    <select name="tenor_data[0][tenor]" class="form-select form-select-sm" required>
                                        <option value="">-- Cicilan --</option>
                                        @for ($i=1; $i<=10; $i++)
                                            <option value="Tenor {{ $i }}">Tenor {{ $i }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="tenor_data[0][persentase]" class="form-control" required min="1" max="100" placeholder="0">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="date" name="tenor_data[0][batas_waktu]" class="form-control form-control-sm" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-icon btn-danger btn-remove-row" disabled>
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow">
                        <i class="bx bx-plus"></i> Tambah Baris
                    </button>
                    <div>
                        <button type="button" class="btn btn-secondary me-2" data-bs-toggle="collapse" data-bs-target="#collapseBulkAdd">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan Semua</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0 text-primary fw-bold"><i class="bx bx-filter-alt"></i> Filter Skema Tenor</h5>
    </div>
    <div class="card-body mt-3">
        <form id="search-form" class="mb-3">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Tahun / Angkatan</label>
                    <select name="tahun_masuk" class="form-select select2">
                        <option value="">-- Bebas Angkatan --</option>
                        @foreach($tahunMasukList as $thn)
                            <option value="{{ $thn }}" {{ request('tahun_masuk') == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Gelombang</label>
                    <select name="gelombang_id" class="form-select select2">
                        <option value="">-- Semua Gelombang --</option>
                        @foreach($gelombangsList as $gel)
                            <option value="{{ $gel->id }}" {{ request('gelombang_id') == $gel->id ? 'selected' : '' }}>{{ $gel->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search"></i> Cari Data</button>
                </div>
            </div>
        </form>

        <div id="tenor-list">
            @include('admin.keuangan.tenor-pembayaran.partial_list', ['tenor' => $tenor])
        </div>
    </div>
</div>

<!-- Modal Form Edit Tenor -->
<div class="modal fade" id="modalEditTenor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white">Edit Tenor Cicilan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditTenor">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Tahun Masuk <span class="text-danger">*</span></label>
                            <select name="tahun_masuk" id="edit_tahun_masuk" class="form-select select2-edit" required>
                                <option value="">-- Pilih --</option>
                                @foreach($tahunMasukList as $thn)
                                    <option value="{{ $thn }}">{{ $thn }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Gelombang <span class="text-danger">*</span></label>
                            <select name="gelombang_id" id="edit_gelombang_id" class="form-select select2-edit" required>
                                <option value="">-- Pilih --</option>
                                @foreach($gelombangsList as $gel)
                                    <option value="{{ $gel->id }}">{{ $gel->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                            <select name="semester" id="edit_semester" class="form-select" required>
                                <option value="">-- Semester --</option>
                                @for ($i=1; $i<=14; $i++)
                                    <option value="{{ $i }}">Semester {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Nama Cicilan <span class="text-danger">*</span></label>
                            <select name="tenor" id="edit_tenor" class="form-select" required>
                                <option value="">-- Pilih Tenor --</option>
                                @for ($i=1; $i<=10; $i++)
                                    <option value="Tenor {{ $i }}">Tenor {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Beban Persentase Tagihan (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="persentase" id="edit_persentase" class="form-control" required min="1" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Batas Jatuh Tempo <span class="text-danger">*</span></label>
                        <input type="date" name="batas_waktu" id="edit_batas_waktu" class="form-control" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bx bx-save"></i> Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
<script>
    function reloadTenorList(url = null) {
        let fetchUrl = url || '{{ route("admin.tenor-pembayaran.index") }}';
        let formData = $('#search-form').serialize();

        $('#tenor-list').css('opacity', '0.5');

        $.ajax({
            url: fetchUrl,
            type: 'GET',
            data: url ? null : formData,
            success: function(response) {
                if(response.html) {
                    $('#tenor-list').html(response.html);
                }
                $('#tenor-list').css('opacity', '1');
            },
            error: function() {
                $('#tenor-list').css('opacity', '1');
                Swal.fire('Error', 'Gagal memuat data', 'error');
            }
        });
    }

    $(document).ready(function() {
        // AJAX Search Form Submit
        $('#search-form').on('submit', function(e) {
            e.preventDefault();
            reloadTenorList();
        });

        // AJAX Pagination Click
        $(document).on('click', '.pagination-links a', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            reloadTenorList(url);
        });

        // Bulk Add Form Logic
        let rowCount = 1;

        $('#btnAddRow').click(function() {
            let tr = $('#bulkTbody tr:first').clone();

            tr.find('select').val('');
            tr.find('input[type="number"]').val('');
            tr.find('input[type="date"]').val('');
            tr.find('.btn-remove-row').prop('disabled', false);

            tr.find('select, input').each(function() {
                let name = $(this).attr('name');
                if (name) {
                    name = name.replace(/\[\d+\]/, '[' + rowCount + ']');
                    $(this).attr('name', name);
                }
            });

            $('#bulkTbody').append(tr);
            rowCount++;
            updateRemoveButtons();
        });

        $(document).on('click', '.btn-remove-row', function() {
            if ($('#bulkTbody tr').length > 1) {
                $(this).closest('tr').remove();
                updateRemoveButtons();
            }
        });

        function updateRemoveButtons() {
            if ($('#bulkTbody tr').length === 1) {
                $('.btn-remove-row').prop('disabled', true);
            } else {
                $('.btn-remove-row').prop('disabled', false);
            }
        }

        // Init Select2 for Edit Modal
        $('.select2-edit').select2({
            dropdownParent: $('#modalEditTenor')
        });

        // AJAX Edit Form Submission
        $('#formEditTenor').on('submit', function(e) {
            e.preventDefault();
            let url = $(this).attr('action');
            let formData = $(this).serialize();

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#modalEditTenor').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        reloadTenorList();
                    }
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan sistem.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal!', msg, 'error');
                }
            });
        });
    });

    function editTenorAjax(id, smt, tahun, gelombang, tenor, persen, batas) {
        let url = '{{ route("admin.tenor-pembayaran.update", ":id") }}'.replace(':id', id);
        $('#formEditTenor').attr('action', url);

        $('#edit_semester').val(smt);
        $('#edit_tahun_masuk').val(tahun).trigger('change');
        $('#edit_gelombang_id').val(gelombang).trigger('change');
        $('#edit_tenor').val(tenor);
        $('#edit_persentase').val(persen);
        $('#edit_batas_waktu').val(batas);

        $('#modalEditTenor').modal('show');
    }

    function deleteTenorAjax(id, textAlert) {
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data " + textAlert + " akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let url = '{{ route("admin.tenor-pembayaran.destroy", ":id") }}'.replace(':id', id);
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            reloadTenorList();
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan sistem saat menghapus.', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection