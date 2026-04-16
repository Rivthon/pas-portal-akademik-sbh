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
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTarif" onclick="resetFormTarif()">
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

<!-- Modal Form Add/Edit Tarif -->
<div class="modal fade" id="modalTarif" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="modalTarifTitle">Tambah Tarif Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTarif" action="{{ route('admin.tarif.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                        <select name="semester" id="input_semester" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            @for ($i=1; $i<=14; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Program Studi <span class="text-danger">*</span></label>
                        <select name="jurusan_id" id="input_jurusan_id" class="form-select select2-modal" required>
                            <option value="">-- Pilih --</option>
                            @foreach($programStudiList as $prodi)
                                <option value="{{ $prodi->jurusan_id }}">{{ $prodi->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Tahun Masuk <span class="text-danger">*</span></label>
                            <select name="tahun_masuk" id="input_tahun_masuk" class="form-select select2-modal" required>
                                <option value="">-- Pilih --</option>
                                @foreach($tahunMasukList as $thn)
                                    <option value="{{ $thn }}">{{ $thn }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Gelombang</label>
                            <select name="gelombang_id" id="input_gelombang_id" class="form-select select2-modal">
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
                            <input type="number" name="tarif" id="input_tarif" class="form-control" required min="0">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
<script>
    function resetFormTarif() {
        $('#formTarif').attr('action', '{{ route('admin.tarif.store') }}');
        $('#formMethod').val('POST');
        $('#modalTarifTitle').text('Tambah Tarif Baru');
        
        $('#input_semester').val('');
        $('#input_jurusan_id').val('').trigger('change');
        $('#input_tahun_masuk').val('').trigger('change');
        $('#input_gelombang_id').val('').trigger('change');
        $('#input_tarif').val('');
    }

    function editTarif(id, semester, jurusan, tahun, gelombang, nominal) {
        let url = '{{ route("admin.tarif.update", ":id") }}'.replace(':id', id);
        $('#formTarif').attr('action', url);
        $('#formMethod').val('PUT');
        $('#modalTarifTitle').text('Edit Tarif');

        $('#input_semester').val(semester);
        $('#input_jurusan_id').val(jurusan).trigger('change');
        $('#input_tahun_masuk').val(tahun).trigger('change');
        $('#input_gelombang_id').val(gelombang || '').trigger('change');
        $('#input_tarif').val(nominal);

        $('#modalTarif').modal('show');
    }

    $(document).ready(function() {
        $('.select2-modal').select2({
            dropdownParent: $('#modalTarif')
        });
    });
</script>
@endpush
@endsection