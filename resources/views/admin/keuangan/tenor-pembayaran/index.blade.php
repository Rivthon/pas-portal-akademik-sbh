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
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTenor" onclick="resetFormTenor()">
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

<!-- Modal Form Add/Edit Tenor -->
<div class="modal fade" id="modalTenor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="modalTenorTitle">Tambah Tenor Cicilan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTenor" action="{{ route('admin.tenor-pembayaran.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethodTenor" value="POST">
                <div class="modal-body">
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Tahun Masuk <span class="text-danger">*</span></label>
                            <select name="tahun_masuk" id="input_tenor_tahun_masuk" class="form-select select2-modal" required>
                                <option value="">-- Pilih --</option>
                                @foreach($tahunMasukList as $thn)
                                    <option value="{{ $thn }}">{{ $thn }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Gelombang <span class="text-danger">*</span></label>
                            <select name="gelombang_id" id="input_tenor_gelombang" class="form-select select2-modal" required>
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
                            <select name="semester" id="input_tenor_semester" class="form-select" required>
                                <option value="">-- Semester --</option>
                                @for ($i=1; $i<=14; $i++)
                                    <option value="{{ $i }}">Semester {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Nama Cicilan <span class="text-danger">*</span></label>
                            <select name="tenor" id="input_tenor_nama" class="form-select" required>
                                <option value="">-- Pilih Tenor --</option>
                                @for ($i=1; $i<=10; $i++)
                                    <option value="{{ $i }}">Tenor {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Beban Persentase Tagihan (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="persentase" id="input_tenor_persen" class="form-control" required min="1" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Misal: 50% dari total tagihan keseluruhan prodi.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Batas Jatuh Tempo <span class="text-danger">*</span></label>
                        <input type="date" name="batas_waktu" id="input_tenor_waktu" class="form-control" required>
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
    function resetFormTenor() {
        $('#formTenor').attr('action', '{{ route('admin.tenor-pembayaran.store') }}');
        $('#formMethodTenor').val('POST');
        $('#modalTenorTitle').text('Tambah Tenor Baru');
        
        $('#input_tenor_semester').val('');
        $('#input_tenor_tahun_masuk').val('').trigger('change');
        $('#input_tenor_gelombang').val('').trigger('change');
        $('#input_tenor_nama').val('');
        $('#input_tenor_persen').val('');
        $('#input_tenor_waktu').val('');
    }

    function editTenor(id, smt, tahun, gelombang, tenor, persen, batas) {
        let url = '{{ route("admin.tenor-pembayaran.update", ":id") }}'.replace(':id', id);
        $('#formTenor').attr('action', url);
        $('#formMethodTenor').val('PUT');
        $('#modalTenorTitle').text('Edit Tenor Cicilan');

        $('#input_tenor_semester').val(smt);
        $('#input_tenor_tahun_masuk').val(tahun).trigger('change');
        $('#input_tenor_gelombang').val(gelombang).trigger('change');
        $('#input_tenor_nama').val(tenor);
        $('#input_tenor_persen').val(persen);
        $('#input_tenor_waktu').val(batas);

        $('#modalTenor').modal('show');
    }

    $(document).ready(function() {
        $('.select2-modal').select2({
            dropdownParent: $('#modalTenor')
        });
    });
</script>
@endpush
@endsection