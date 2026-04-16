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
        <h5 class="mb-0">Daftar Tagihan & Riwayat Transaksi</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped table-hover" id="tagihan-table">
            <thead class="table-primary text-center">
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">Angkatan & Smt</th>
                    <th class="text-center">Tenor</th>
                    <th class="text-end">Jumlah Tagihan</th>
                    <th class="text-end">Telah Dibayar</th>
                    <th class="text-end">Sisa Pembayaran</th>
                    <th class="text-center">Jatuh Tempo</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi (Bayar)</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr class="table-secondary font-weight-bold">
                    <th colspan="3" class="text-end">TOTAL KESELURUHAN:</th>
                    <th class="text-end" id="total-jumlah-tagihan">0</th>
                    <th class="text-end" id="total-pembayaran">0</th>
                    <th class="text-end" id="total-sisa-tagihan">0</th>
                    <th colspan="3"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Modal Bayar Tagihan & Histori -->
<div class="modal fade" id="modalBayar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white">Kelola Pembayaran Cicilan (Tenor <span id="modal-tenor-label"></span>)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          
          <div class="row mb-4">
              <div class="col-6">
                <strong>Total Tagihan: </strong> <br>
                <h4 class="text-primary mb-0" id="info-total-tagihan">Rp 0</h4>
              </div>
              <div class="col-6 text-end">
                <strong>Sisa Kekurangan: </strong> <br>
                <h4 class="text-danger mb-0" id="info-sisa-tagihan">Rp 0</h4>
              </div>
          </div>

          <hr>

          <!-- Histori Pembayaran -->
          <h6 class="text-muted fw-bold mb-2">Riwayat Pembayaran Sebelumnya:</h6>
          <div class="table-responsive mb-4">
              <table class="table table-sm table-bordered">
                  <thead class="table-light">
                      <tr>
                          <th>Tanggal</th>
                          <th>Nominal</th>
                          <th>Keterangan</th>
                      </tr>
                  </thead>
                  <tbody id="histori-pembayaran-tbody">
                      <!-- Diisi via Ajax -->
                  </tbody>
              </table>
          </div>

          <hr>
          
          <!-- Form Input Pembayaran Baru -->
          <h6 class="text-primary fw-bold mb-3"><i class="bx bx-plus-circle"></i> Input Pembayaran Baru</h6>
          <form id="form-bayar-tagihan">
              <input type="hidden" id="input_tagihan_id" name="tagihan_id">
              <div class="row g-3">
                  <div class="col-md-6">
                      <label>Nominal Bayar (Rp)</label>
                      <input type="number" class="form-control" name="nominal_bayar" id="input_nominal_bayar" required min="1">
                  </div>
                  <div class="col-md-6">
                      <label>Tanggal Bayar</label>
                      <input type="date" class="form-control" name="tanggal_bayar" id="input_tanggal_bayar" required value="{{ date('Y-m-d') }}">
                  </div>
                  <div class="col-12">
                      <label>Keterangan / Ref Bank</label>
                      <input type="text" class="form-control" name="keterangan" id="input_keterangan" placeholder="Contoh: Transfer Mandiri An. Budi">
                  </div>
              </div>
          </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-success" id="btn-simpan-pembayaran">
            <i class="bx bx-check"></i> Simpan Transaksi
        </button>
      </div>
    </div>
  </div>
</div>

@push('script')
<script>
    $(document).ready(function () {
    // Variable global penyimpan data untuk modal
    let cachedDataTagihan = []; 

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
            cachedDataTagihan = data; // Simpan untuk modal
            let tbody = $("#tagihan-table tbody");
            tbody.empty();

            let totalJumlahTagihan = 0;
            let totalPembayaran = 0;
            let totalSisaTagihan = 0;
            let rows = '';

            if (data.length > 0) {
                $.each(data, function (index, item) {
                    let jumlahTagihan = Number(item.jumlah_tagihan) || 0;
                    
                    // Hitung total dari relasi transaksi
                    let totalPembayaranItem = 0;
                    if(item.transaksi && item.transaksi.length > 0) {
                        $.each(item.transaksi, function(i, trx) {
                            if(trx.status_verifikasi === 'diterima') {
                                totalPembayaranItem += Number(trx.nominal_bayar);
                            }
                        });
                    }

                    let sisa = jumlahTagihan - totalPembayaranItem;
                    if(sisa < 0) sisa = 0; // Prevent negative display

                    totalJumlahTagihan += jumlahTagihan;
                    totalPembayaran += totalPembayaranItem;
                    totalSisaTagihan += sisa;

                    let statusBadge = item.status === "lunas"
                        ? '<span class="badge bg-success">Lunas</span>'
                        : '<span class="badge bg-danger">Belum Lunas</span>';
                    
                    let angkatan = item.mahasiswa && item.mahasiswa.tahun_masuk ? item.mahasiswa.tahun_masuk : '-';

                    rows += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center">
                                <span class="badge bg-label-info">Smt ${item.semester}</span><br>
                                <small class="text-muted">Angk. ${angkatan}</small>
                            </td>
                            <td class="text-center fw-bold text-primary">${item.tenor_pembayaran ? item.tenor_pembayaran.tenor : '-'}</td>
                            <td class="text-end fw-bold">${formatRupiah(jumlahTagihan)}</td>
                            <td class="text-end text-success">${formatRupiah(totalPembayaranItem)}</td>
                            <td class="text-end text-danger">${formatRupiah(sisa)}</td>
                            <td class="text-center">${formatTanggal(item.jatuh_tempo)}</td>
                            <td class="text-center">${statusBadge}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary btn-bayar" data-id="${item.id}" data-index="${index}">
                                    <i class="bx bx-wallet"></i> Histori & Bayar
                                </button>
                            </td>
                        </tr>
                    `;
                });

                tbody.append(rows);

                // Update footer total
                updateTotalFooter(totalJumlahTagihan, totalPembayaran, totalSisaTagihan);

                // Ganti event listener ke Modal
                $(".btn-bayar").click(function () {
                    let index = $(this).data("index");
                    bukaModalBayar(index);
                });

            } else {
                tbody.append('<tr><td colspan="9" class="text-center py-4">Data tagihan belum dibuat atau tidak ditemukan.</td></tr>');
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

    // Buka Modal & Fetch Detail
    function bukaModalBayar(index) {
        let tagihan = cachedDataTagihan[index];
        $('#input_tagihan_id').val(tagihan.id);
        $('#modal-tenor-label').text(tagihan.tenor_pembayaran ? tagihan.tenor_pembayaran.tenor : '-');
        $('#info-total-tagihan').text(formatRupiah(tagihan.jumlah_tagihan));

        let totalTelahDibayar = 0;
        let riwayatHTML = '';

        if(tagihan.transaksi && tagihan.transaksi.length > 0) {
            $.each(tagihan.transaksi, function(i, trx) {
                totalTelahDibayar += Number(trx.nominal_bayar);
                let ket = trx.keterangan ? trx.keterangan : '-';
                riwayatHTML += `
                    <tr>
                        <td>${formatTanggal(trx.tanggal_bayar)}</td>
                        <td class="text-end fw-bold text-success">+ ${formatRupiah(trx.nominal_bayar)}</td>
                        <td>${ket}</td>
                    </tr>
                `;
            });
        }

        if(riwayatHTML === '') {
            riwayatHTML = '<tr><td colspan="3" class="text-center text-muted">Belum ada riwayat pembayaran</td></tr>';
        }
        $('#histori-pembayaran-tbody').html(riwayatHTML);

        let sisa = tagihan.jumlah_tagihan - totalTelahDibayar;
        if(sisa < 0) sisa = 0;
        $('#info-sisa-tagihan').text(formatRupiah(sisa));
        
        // Setup default input value (auto suggest bayar lunas sisanya)
        $('#input_nominal_bayar').val(sisa > 0 ? sisa : 0);
        $('#input_keterangan').val('');

        $('#modalBayar').modal('show');
    }

    // Submit Pembayaran
    $('#btn-simpan-pembayaran').click(function() {
        let id = $('#input_tagihan_id').val();
        let nominal = $('#input_nominal_bayar').val();
        let tgl = $('#input_tanggal_bayar').val();
        let ket = $('#input_keterangan').val();

        if(!nominal || nominal <= 0) {
            alert("Nominal pembayaran harus lebih dari 0");
            return;
        }

        $.ajax({
            url: "{{ route('admin.simpanPembayaran', ['id' => ':id']) }}".replace(':id', id),
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                nominal_bayar: nominal,
                tanggal_bayar: tgl,
                keterangan: ket
            },
            beforeSend: function() {
                $('#btn-simpan-pembayaran').text('Menyimpan...').prop('disabled', true);
            },
            success: function (response) {
                alert("Transaksi berhasil disimpan!");
                $('#modalBayar').modal('hide');
                $("#search-form").submit(); // Refresh layar belakang
            },
            error: function (xhr, status, error) {
                alert("Gagal memperbarui: Silakan periksa inputan Anda.");
            },
            complete: function() {
                $('#btn-simpan-pembayaran').html('<i class="bx bx-check"></i> Simpan Transaksi').prop('disabled', false);
            }
        });
    });

    // Fitur Tambahan Generate Tagihan (AJAX TRIGGER)
    $("#generate-tagihan").click(function () {
        if(!confirm("Apakah Anda yakin ingin MENGENERATE (membuat baru) tagihan angkatan dan bulan ini untuk seluruh Mahasiswa Aktif? Proses ini mungkin butuh waktu beberapa detik.")) {
            return;
        }
        
        let btn = $(this);
        let oriText = btn.html();
        
        $.ajax({
            url: @json(route('admin.generate')),
            type: "POST", // Kita merubah method menjadi POST sesuai web.php
            data: {
                _token: $('meta[name="csrf-token"]').attr("content")
            },
            success: function (res) {
                alert(res.message);
                if(res.success) {
                    $('#search-form').submit(); 
                }
            },
            error: function (xhr) {
                alert("Terjadi kegagalan saat generate tagihan.");
            },
            beforeSend: function () {
                btn.prop('disabled', true).text('Generating...');
            },
            complete: function () {
                btn.prop('disabled', false).html(oriText);
            }
        });
    });

});


</script>

@endpush
@endsection