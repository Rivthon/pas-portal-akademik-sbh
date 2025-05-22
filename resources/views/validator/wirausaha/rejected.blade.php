@extends('layouts.master')
@section('title', 'Wirausaha Disetujui')

@section('content')
<div class="container mt-4">


    <div class="card shadow-sm">
        <div class="card-body">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <p class="mb-0">Daftar P2MW yang Ditolak</p>
                <a href="{{ route('admin.skpi.wirausaha') }}" class="btn btn-secondary">Kembali</a>
            </div>
            <form id="searchForm" method="GET" action="{{ route('admin.skpi.wirausaha.ditolak') }}"
                class="row g-3 mb-3">
                <div class="col-md-10">
                    <input type="text" name="search" id="searchInput" class="form-control"
                        placeholder="Cari berdasarkan nama usaha..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>

            <div id="tableContainer">
                @include('validator.wirausaha.partials.table-reject', ['query' => $query])
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#searchForm').on('submit', function(e) {
            e.preventDefault();

            let search = $('#searchInput').val();
            $.ajax({
                url: "{{ route('admin.skpi.wirausaha.ditolak') }}",
                type: 'GET',
                data: {
                    search: search
                },
                success: function(response) {
                    let html = $(response).find('#tableContainer').html();
                    $('#tableContainer').html(html);
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan saat memuat data.');
                }
            });
        });
    });
</script>
@endsection