@extends('layouts.master')
@section('title', 'Tarif Per Semester')
@section('content')
<div class="card shadow-sm mb-4 border-0">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Tarif Per Semester
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Kelola daftar nominal tarif biaya kuliah yang disesuaikan per program studi, tahun angkatan, dan gelombang masuk.
                </p>
                <div class="mb-3">
                    <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#collapseBulkAdd">
                        <i class="bx bx-plus"></i> Tambah Tarif
                    </button>
                    <button class="btn btn-outline-success" data-bs-toggle="collapse" data-bs-target="#collapseImport">
                        <i class="bx bx-import"></i> Import Excel
                    </button>
                </div>

                <div class="collapse mt-3" id="collapseImport">
                    <div class="card card-body bg-light border-0 p-3">
                        <form action="{{ route('admin.tarif.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-2 align-items-end">
                                <div class="col-md-8">
                                    <label for="file" class="form-label fw-bold small">Upload File (.xlsx / .csv)</label>
                                    <input type="file" name="file" id="file" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-sm btn-success w-100">Upload</button>
                                    <a href="{{ route('admin.tarif.download-template') }}" class="btn btn-sm btn-info" title="Download Template"><i class="bx bx-download"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center d-none d-md-block">
            <div class="p-3">
                <img src="../assets/img/illustrations/kartu-study.png" class="img-fluid opacity-75" alt="Tarif Illustration" style="max-height: 180px;">
            </div>
        </div>
    </div>
</div>

<!-- Bulk Add Form Section -->
<div class="collapse mb-4" id="collapseBulkAdd">
    <div class="card shadow-sm border-primary" style="border-top: 3px solid #696cff;">
        <div class="card-header bg-white pb-2 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold"><i class="bx bx-list-plus"></i> Input Tarif Masal (Bulk)</h5>
            <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#collapseBulkAdd" aria-label="Close"></button>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.tarif.storeBulk') }}" method="POST" id="formBulkTarif">
                @csrf
                <div class="table-responsive mb-3" style="overflow-visible: true;">
                    <table class="table table-bordered table-hover" id="bulkTable">
                        <thead class="table-light">
                            <tr>
                                <th>Program Studi <span class="text-danger">*</span></th>
                                <th>Tahun Angkatan <span class="text-danger">*</span></th>
                                <th>Gelombang <span class="text-danger">*</span></th>
                                <th>Semester <span class="text-danger">*</span></th>
                                <th>Nominal Tarif (Rp) <span class="text-danger">*</span></th>
                                <th width="50px" class="text-center"><i class="bx bx-cog"></i></th>
                            </tr>
                        </thead>
                        <tbody id="bulkTbody">
                            <!-- Baris pertama (default) -->
                            <tr class="bulk-row">
                                <td>
                                    <select name="tarif[0][jurusan_id]" class="form-select form-select-sm" required>
                                        <option value="">-- Pilih Prodi --</option>
                                        @foreach($programStudiList as $prodi)
                                            <option value="{{ $prodi->jurusan_id }}">{{ $prodi->singkat ?? $prodi->nama }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="tarif[0][tahun_masuk]" class="form-select form-select-sm" required>
                                        <option value="">-- Pilih Tahun --</option>
                                        @foreach($tahunMasukList as $thn)
                                            <option value="{{ $thn }}">{{ $thn }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="tarif[0][gelombang_id]" class="form-select form-select-sm" required>
                                        <option value="">-- Gelombang --</option>
                                        @foreach($gelombangsList as $gel)
                                            <option value="{{ $gel->id }}">{{ $gel->nama }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="tarif[0][semester]" class="form-select form-select-sm" required>
                                        <option value="">-- Semester --</option>
                                        @for ($i=1; $i<=14; $i++)
                                            <option value="{{ $i }}">Smt {{ $i }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="tarif[0][tarif]" class="form-control" required min="0" placeholder="0">
                                    </div>
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
        <h5 class="mb-0 text-primary fw-bold"><i class="bx bx-filter-alt"></i> Filter Pencarian Data</h5>
    </div>
    <div class="card-body mt-3">
        <form id="search-form" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Program Studi</label>
                    <select name="program_studi" class="form-select select2">
                        <option value="">-- Semua Program Studi --</option>
                        @foreach($programStudiList as $prodi)
                            <option value="{{ $prodi->jurusan_id }}" {{ request('program_studi') == $prodi->jurusan_id ? 'selected' : '' }}>{{ $prodi->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Tahun / Angkatan</label>
                    <select name="tahun_masuk" class="form-select select2">
                        <option value="">-- Bebas Angkatan --</option>
                        @foreach($tahunMasukList as $thn)
                            <option value="{{ $thn }}" {{ request('tahun_masuk') == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Gelombang</label>
                    <select name="gelombang_id" class="form-select select2">
                        <option value="">-- Semua Gelombang --</option>
                        @foreach($gelombangsList as $gel)
                            <option value="{{ $gel->id }}" {{ request('gelombang_id') == $gel->id ? 'selected' : '' }}>{{ $gel->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search"></i></button>
                </div>
            </div>
        </form>

        <div id="tarif-list">
            @include('admin.keuangan.tarif.partial_list', ['tarif' => $tarif])
        </div>
    </div>
</div>

<!-- Modal Form Edit Tarif -->
<div class="modal fade" id="modalEditTarif" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white">Edit Tarif</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditTarif">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                        <select name="semester" id="edit_semester" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            @for ($i=1; $i<=14; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Program Studi <span class="text-danger">*</span></label>
                        <select name="jurusan_id" id="edit_jurusan_id" class="form-select select2-edit" required>
                            <option value="">-- Pilih --</option>
                            @foreach($programStudiList as $prodi)
                                <option value="{{ $prodi->jurusan_id }}">{{ $prodi->nama }}</option>
                            @endforeach
                        </select>
                    </div>

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
                            <label class="form-label fw-bold">Gelombang</label>
                            <select name="gelombang_id" id="edit_gelombang_id" class="form-select select2-edit">
                                <option value="">-- Semua --</option>
                                @foreach($gelombangsList as $gel)
                                    <option value="{{ $gel->id }}">{{ $gel->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nominal Tarif (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="tarif" id="edit_tarif" class="form-control" required min="0">
                        </div>
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
    // Show Import Errors with SweetAlert
    @if(session('import_errors'))
        let errorList = {!! json_encode(session('import_errors')) !!};
        let errorHtml = '<ul class="text-start" style="max-height: 200px; overflow-y: auto;">';
        errorList.forEach(function(err) {
            errorHtml += '<li><small>' + err + '</small></li>';
        });
        errorHtml += '</ul>';

        Swal.fire({
            icon: 'error',
            title: 'Gagal Import Excel',
            html: errorHtml,
            confirmButtonText: 'Mengerti',
            width: '500px'
        });
    @endif

    // Bulk Add Form Logic
    $(document).ready(function() {
        let rowCount = 1;

        // Tambah Baris
        $('#btnAddRow').click(function() {
            let tr = $('#bulkTbody tr:first').clone();

            tr.find('select').val('');
            tr.find('input').val('');
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

        // Hapus Baris
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
    });

    function reloadTarifList(url = null) {
        let fetchUrl = url || '{{ route("admin.tarif.index") }}';
        let formData = $('#search-form').serialize();

        // Tambahkan loader overlay jika diperlukan, atau ganti isi dengan loading
        $('#tarif-list').css('opacity', '0.5');

        $.ajax({
            url: fetchUrl,
            type: 'GET',
            data: url ? null : formData, // Jika click pagination (ada url), params sudah nempel di URL
            success: function(response) {
                if(response.html) {
                    $('#tarif-list').html(response.html);
                }
                $('#tarif-list').css('opacity', '1');
            },
            error: function() {
                $('#tarif-list').css('opacity', '1');
                Swal.fire('Error', 'Gagal memuat data', 'error');
            }
        });
    }

    $(document).ready(function() {
        // AJAX Search Form Submit
        $('#search-form').on('submit', function(e) {
            e.preventDefault();
            reloadTarifList();
        });

        // AJAX Pagination Click
        $(document).on('click', '.pagination-links a', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            reloadTarifList(url);
        });

        // Hapus Baris
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
            dropdownParent: $('#modalEditTarif')
        });

        // AJAX Edit Form Submission
        $('#formEditTarif').on('submit', function(e) {
            e.preventDefault();
            let url = $(this).attr('action');
            let formData = $(this).serialize();

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#modalEditTarif').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        reloadTarifList(); // reload list tanpa refresh
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

    // Buka Modal Edit via AJAX Function
    function editTarifAjax(id, semester, jurusan, tahun, gelombang, nominal) {
        let url = '{{ route("admin.tarif.update", ":id") }}'.replace(':id', id);
        $('#formEditTarif').attr('action', url);

        $('#edit_semester').val(semester);
        $('#edit_jurusan_id').val(jurusan).trigger('change');
        $('#edit_tahun_masuk').val(tahun).trigger('change');
        $('#edit_gelombang_id').val(gelombang || '').trigger('change');
        $('#edit_tarif').val(nominal);

        $('#modalEditTarif').modal('show');
    }

    // Fungsi Hapus via AJAX
    function deleteTarifAjax(id, textAlert) {
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
                let url = '{{ route("admin.tarif.destroy", ":id") }}'.replace(':id', id);
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
                            reloadTarifList(); // reload list tanpa refresh
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