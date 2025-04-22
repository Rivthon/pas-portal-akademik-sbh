@extends('layouts.master')
@section('title', 'Tagihan Pembayaran')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Tagihan Mahasiswa
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Halaman ini digunakan untuk menampilkan dan mengelola daftar tagihan mahasiswa aktif.
                </p>
                <div class="mb-3">
                    <a href="{{ route('admin.tagihan-mahasiswa.create') }}" class="btn btn-primary">
                        Tambah Tagihan
                    </a>
                    <button id="generate-tagihan" class="btn btn-success">
                        Generate Tagihan
                    </button>
                </div>
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="../assets/img/illustrations/kartu-study.png" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height:200px;">
            </div>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Cari Tagihan Pembayaran Mahasiswa</h5>
    </div>
    <div class="card-body">
        <form id="search-form">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="program_studi" class="form-label">Program Studi</label>
                    <select name="program_studi" id="program_studi" class="form-select">
                        <option value="">Pilih Program Studi</option>
                        @foreach($programStudiList as $prodi)
                        <option value="{{ $prodi->jurusan_id }}">{{ $prodi->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="tahun_masuk" class="form-label">Tahun Masuk</label>
                    <select name="tahun_masuk" id="tahun_masuk" class="form-select">
                        <option value="">Pilih Tahun Masuk</option>
                        @foreach($tahunMasukList as $tahun)
                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="mahasiswa_id" class="form-label">Nama Mahasiswa</label>
                    <select name="mahasiswa_id" id="mahasiswa_id" class="form-select">
                        <option value="">Pilih Mahasiswa</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Hasil Pencarian -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Hasil Pencarian</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="tagihan-table">
            <thead class="table-primary text-center">
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">Semester</th>
                    <th class="text-center">Tenor</th>
                    <th class="text-end">Jumlah Tagihan</th>
                    <th class="text-end">Total Pembayaran</th>
                    <th class="text-end">Sisa Pembayaran</th>
                    <th class="text-center">Jatuh Tempo</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Pembayaran</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total:</th>
                    <th class="text-end" id="total-jumlah-tagihan">0</th>
                    <th class="text-end" id="total-pembayaran">0</th>
                    <th class="text-end" id="total-sisa-tagihan">0</th>
                    <th colspan="4"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@push('script')
<script>
    $(document).ready(function () {
    function loadMahasiswa() {
        var programStudi = $('#program_studi').val();
        var tahunMasuk = $('#tahun_masuk').val();

        if (programStudi && tahunMasuk) {
            $.ajax({
                url: @json(route('admin.get.mahasiswa')),
                type: "GET",
                data: {
                    program_studi: programStudi,
                    tahun_masuk: tahunMasuk
                },
                dataType: "json",
                beforeSend: function () {
                    $('#mahasiswa_id').html('<option value="">Loading...</option>'); // Indikator loading
                },
                success: function (data) {
                    var mahasiswaDropdown = $('#mahasiswa_id');
                    mahasiswaDropdown.empty().append('<option value="">Pilih Mahasiswa</option>');

                    if (data.length > 0) {
                        $.each(data, function (index, mahasiswa) {
                            mahasiswaDropdown.append(`<option value="${mahasiswa.mahasiswa_id}">${mahasiswa.nama} (${mahasiswa.nim})</option>`);
                        });
                    } else {
                        mahasiswaDropdown.append('<option value="">Mahasiswa tidak ditemukan</option>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error:", xhr.responseText);
                    alert("Terjadi kesalahan saat mengambil data mahasiswa.");
                }
            });
        } else {
            $('#mahasiswa_id').html('<option value="">Pilih Program Studi & Tahun Masuk</option>');
        }
    }

    $('#program_studi, #tahun_masuk').change(function () {
        loadMahasiswa();
    });

   $('#search-form').submit(function (e) {
    e.preventDefault();

    $.ajax({
        url: @json(route('admin.search.tagihan')),
        type: "GET",
        data: $(this).serialize(),
        dataType: "json",
        beforeSend: function () {
            $("#tagihan-table tbody").html('<tr><td colspan="10" class="text-center">Loading...</td></tr>');
        },
        success: function (data) {
            let tbody = $("#tagihan-table tbody");
            tbody.empty();

            let totalJumlahTagihan = 0;
            let totalPembayaran = 0;
            let totalSisaTagihan = 0;
            let rows = '';

            if (data.length > 0) {
                $.each(data, function (index, item) {
                    let jumlahTagihan = Number(item.jumlah_tagihan) || 0;
                    let totalPembayaranItem = Number(item.total_pembayaran ) || 0;
                    let sisa = jumlahTagihan - totalPembayaranItem;

                    totalJumlahTagihan += jumlahTagihan;
                    totalPembayaran += totalPembayaranItem;
                    totalSisaTagihan += sisa;

                    let statusBadge = item.status === "lunas"
                        ? '<span class="badge bg-success">Lunas</span>'
                        : '<span class="badge bg-danger">Belum Lunas</span>';

                    rows += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center">Semester ${item.semester}</td>
                            <td class="text-center">${item.tenor_pembayaran.tenor}</td>
                            <td class="text-end">${formatRupiah(jumlahTagihan)}</td>
                            <td class="text-end">${formatRupiah(totalPembayaranItem)}</td>
                            <td class="text-end">${formatRupiah(sisa)}</td>
                            <td class="text-center">${formatTanggal(item.jatuh_tempo)}</td>
                            <td class="text-center">${statusBadge}</td>
                            <td class="text-center">
                                <input type="number" class="form-control text-end pembayaran-input" data-id="${item.id}" value="${totalPembayaranItem}">
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-success save-button" data-id="${item.id}">
                                    <i class="bx bxs-save"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                tbody.append(rows);

                // Update footer total
                updateTotalFooter(totalJumlahTagihan, totalPembayaran, totalSisaTagihan);

                // Event listener untuk tombol simpan
                $(".save-button").click(function () {
                    let id = $(this).data("id");
                    let pembayaran = $(this).closest("tr").find(".pembayaran-input").val();
                    updatePembayaran(id, pembayaran);
                });

            } else {
                tbody.append('<tr><td colspan="10" class="text-center">Data tidak ditemukan</td></tr>');
                updateTotalFooter(0, 0, 0);
            }
        },
        error: function () {
            alert("Terjadi kesalahan saat mengambil data tagihan.");
        }
    });
});

        // ✅ Fungsi untuk memformat angka ke Rupiah
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        // ✅ Fungsi untuk memformat tanggal ke format Indonesia
        function formatTanggal(tanggal) {
            return new Date(tanggal).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'short', year: 'numeric' });
        }

        // ✅ Fungsi untuk memperbarui total footer
        function updateTotalFooter(jumlahTagihan, pembayaran, sisaTagihan) {
            $("#total-jumlah-tagihan").text(formatRupiah(jumlahTagihan));
            $("#total-pembayaran").text(formatRupiah(pembayaran));
            $("#total-sisa-tagihan").text(formatRupiah(sisaTagihan));
        }

    function updatePembayaran(id, pembayaran) {
        $.ajax({
            url: "{{ route('admin.simpanPembayaran', ['id' => ':id']) }}".replace(':id', id),
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                total_pembayaran: pembayaran
            },
            success: function (response) {
                alert("Pembayaran berhasil diperbarui!");
                $("#search-form").submit(); // Refresh tabel setelah update
            },
            error: function (xhr, status, error) {
                alert("Gagal memperbarui pembayaran: " + xhr.responseText);
            }
        });
    }


    function deleteTagihan(id) {
        $.ajax({
            url: `/admin/tagihan-mahasiswa/${id}`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                _method: "DELETE"
            },
            success: function () {
                alert("Data berhasil dihapus.");
                $('#search-form').submit(); // Refresh data setelah delete
            },
            error: function (xhr, status, error) {
                alert("Terjadi kesalahan saat menghapus data.");
            }
        });
    }
});


</script>

@endpush
@endsection