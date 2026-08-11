@extends('layouts.master')
@section('title', 'Monitoring Absensi Perkuliahan')

@push('head')
<style>
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
    }
    #table-container {
        transition: opacity 0.3s ease;
    }
</style>
@endpush

@section('content')
<div class="row">

    {{-- HERO --}}
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg,#1e3c72,#2a5298)">
            <div class="card-body text-white">
                <h4 class="fw-bold text-white">
                    <i class="bx bx-check-shield me-2"></i> Monitoring Absensi Akademik
                </h4>
                <p class="mb-0 text-white-50">
                    Pantau dan kelola absensi dosen serta cetak laporan kehadiran dengan mudah.
                </p>
            </div>
        </div>
    </div>

    {{-- STAT --}}
    <div class="col-12 mb-4">
        <div class="row g-4">
            @foreach([
                ['id'=>'total','label'=>'Total Jadwal','color'=>'primary','icon'=>'bx-calendar'],
                ['id'=>'belum','label'=>'Belum Diisi','color'=>'warning','icon'=>'bx-error'],
                ['id'=>'selesai','label'=>'Selesai','color'=>'info','icon'=>'bx-check']
            ] as $s)
            <div class="col-md-4">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between">
                        <div>
                            <small class="text-muted">{{ $s['label'] }}</small>
                            <h3 id="stat-{{ $s['id'] }}">0</h3>
                        </div>
                        <i class="bx {{ $s['icon'] }} fs-3 text-{{ $s['color'] }}"></i>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- FILTER + TABLE --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">

            {{-- FILTER --}}
            <div class="card-header bg-white">
                <form id="filter-form" class="row g-3">

                    <div class="col-md-3">
                        <label>Tahun Akademik</label>
                        {{-- PERBAIKAN 1: name="ta_id" diubah menjadi name="tahun_ajaran_id" agar sesuai dengan controller --}}
                        <select class="form-select select2" id="filter-ta" name="ta_id">
                            <option value="">-- Pilih --</option>
                            @foreach($tahunAkademik as $ta)
                                <option value="{{ $ta->ta_id }}">{{ $ta->nama }} - ({{ $ta->semester}})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Prodi</label>
                        <select class="form-select select2" name="prodi_id">
                            <option value="">-- Semua --</option>
                            @foreach($programStudi as $p)
                                <option value="{{ $p->jurusan_id }}">{{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Semester</label>
                        <select class="form-select select2" id="filter-semester" name="semester">
                            <option value="">-- Semua --</option>
                            @for($i=1;$i<=8;$i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Dosen</label>
                        <select class="form-select select2" name="dosen_id">
                            <option value="">-- Semua --</option>
                            @foreach($dosen as $d)
                                <option value="{{ $d->dosen_id }}">{{ $d->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary" id="btn-submit">
                            <i class="bx bx-search"></i> Tampilkan
                        </button>
                        <button type="button" id="reset-filter" class="btn btn-outline-secondary">
                            Reset
                        </button>
                    </div>

                </form>
            </div>

            {{-- TABLE --}}
            <div class="card-body position-relative">

                <div id="loading-spinner" class="text-center py-5" style="display:none;">
                    <div class="spinner-border text-primary"></div>
                </div>

                <div id="table-container">
                    <div class="empty-state">
                        <i class="bx bx-filter fs-1 text-primary"></i>
                        <h6 class="mt-3">Silakan Pilih Filter</h6>
                        <p class="text-muted">Klik tampilkan untuk melihat data</p>
                    </div>
                </div>

            </div>

        </div>
    </div>

</div>
@endsection
@push('script')
<script>
$(function(){

    $('.select2').select2({ width:'100%' });

    const table = $('#table-container');
    const loading = $('#loading-spinner');
    const form = $('#filter-form');
    const btn = $('#btn-submit');

    function animate(id,val){
        $('#'+id).text(val);
    }

    function loadData(url="{{ route('admin.absensi.filter') }}"){

        loading.show();
        table.css('opacity',0.5);
        btn.prop('disabled',true).text('Loading...');

        $.get(url, form.serialize(), function(res){

            table.html(res.html);

            if(res.statistik) {
                animate('stat-total', res.statistik.total || 0);
                animate('stat-belum', res.statistik.belum || 0);
                animate('stat-selesai', res.statistik.selesai || 0);
            }

            var tooltipTriggerList = [].slice.call(table[0].querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

        }).fail(function(){
            table.html(`<div class="text-center py-5 text-danger">Gagal load data dari server</div>`);
        }).always(function(){
            loading.hide();
            table.css('opacity',1);
            btn.prop('disabled',false).html('<i class="bx bx-search"></i> Tampilkan');
        });
    }

    // Validasi sudah dihapus, langsung eksekusi loadData()
    form.on('submit', function(e){
        e.preventDefault();
        loadData();
    });

    $(document).on('click','.pagination a', function(e){
        e.preventDefault();
        loadData($(this).attr('href'));
    });

    $('#reset-filter').click(function(){
        form[0].reset();
        $('.select2').val(null).trigger('change');

        table.html(`
            <div class="empty-state">
                <i class="bx bx-filter fs-1 text-primary"></i>
                <h6 class="mt-3">Silakan Pilih Filter</h6>
            </div>
        `);

        $('#stat-total,#stat-belum,#stat-selesai').text('0');
    });

});
</script>
@endpush