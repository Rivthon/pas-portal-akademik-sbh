@extends('layouts.master')

@section('content')
<div class="card">
    <div class="card-header bg-white text-secondary">
        <h5 class="mb-0">Input Nilai UAP Kebidanan</h5>
    </div>
    <div class="card-body">
        <form id="filter-form">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Tahun Ajaran</label>
                    <select class="form-select" name="tahun_ajaran_id" required>
                        <option value="">Pilih Tahun</option>
                        @foreach($tahunAjaran as $ta)
                        <option value="{{ $ta->ta_id }}">{{ $ta->nama }} - {{ $ta->semester }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Semester</label>
                    <select class="form-select" name="semester" required>
                        @for($i = 1; $i <= 8; $i++) <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                    </select>
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-primary">Tampilkan Mahasiswa</button>
                </div>
            </div>
        </form>

        <hr class="my-4">

        <div id="mahasiswa-uap-list">
            <!-- AJAX akan menampilkan data di sini -->
        </div>
    </div>
</div>

@push('script')
<script>
$('#filter-form').on('submit', function (e) {
    e.preventDefault();
    let data = $(this).serialize();

    $.get("{{ route('admin.uap.getMahasiswa') }}", data)
        .done(function (response) {
            let rows = '';
            if (Array.isArray(response.data) && response.data.length > 0) {
                response.data.forEach(mhs => {
                    rows += `
            <tr data-id="${mhs.mahasiswa_id}">
                <td>${mhs.nim}</td>
                <td>${mhs.nama}</td>
                <td>
                <input type="number" class="form-control tulis" data-id="${mhs.mahasiswa_id}" name="uap_tulis[${mhs.mahasiswa_id}]" placeholder="Tulis" min="0" max="100" value="${mhs.uap_tulis ?? ''}">
                </td>
                <td>
                <input type="number" class="form-control praktik" data-id="${mhs.mahasiswa_id}" name="uap_praktik[${mhs.mahasiswa_id}]" placeholder="Praktik" min="0" max="100" value="${mhs.uap_praktik ?? ''}">
                </td>
                <td>
                <button type="button" class="btn btn-sm btn-success simpan-nilai" data-id="${mhs.mahasiswa_id}">Simpan</button>
                </td>
            </tr>
            `;
                });
            } else {
                rows = `<tr><td colspan="5" class="text-center">Tidak ada data mahasiswa ditemukan.</td></tr>`;
            }
            $('#mahasiswa-uap-list').html(`
        <table class="table table-bordered mt-3">
            <thead>
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>UAP Tulis</th>
                <th>UAP Praktik</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
        `);
        })
        .fail(function (xhr) {
            let message = xhr.responseJSON?.message ?? "Error tidak diketahui";
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                message += "\n" + Object.values(xhr.responseJSON.errors).flat().join("\n");
            }
            alert("Gagal ambil mahasiswa:\n" + message);
            $('#mahasiswa-uap-list').html(
                `<div class="alert alert-danger mt-3">${message}</div>`
            );
        });

});

// Simpan nilai dengan AJAX
$(document).on('click', '.simpan-nilai', function () {
    let id = $(this).data('id');
    // Ambil value terbaru dari input pada baris yang sama
    let row = $(this).closest('tr');
    let uap_tulis = row.find('.tulis').val();
    let uap_praktik = row.find('.praktik').val();
    let tahun_ajaran_id = $('[name="tahun_ajaran_id"]').val();

    $.post("{{ route('admin.uap.simpanNilai') }}", {
        _token: '{{ csrf_token() }}',
        mahasiswa_id: id,
        tahun_ajaran_id: tahun_ajaran_id,
        uap_tulis: uap_tulis,
        uap_praktik: uap_praktik
    }, function (res) {
        if (res.success) {
            alert('Nilai berhasil disimpan');
        } else {
            alert('Gagal menyimpan nilai');
        }
    });
});
</script>
@endpush
@endsection
