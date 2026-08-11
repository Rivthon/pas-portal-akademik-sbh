@extends('layouts.master')
@section('title', 'Pengajaran Dosen')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <!-- Content Section -->
                    <div class="col-md-7">
                        <h5 class="card-title text-primary mb-3 fw-bold">Pengajaran Dosen</h5>
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Halaman ini memfasilitasi Anda untuk menambahkan dosen pengajar ke mata kuliah yang tersedia.
                            Pilih Tahun Ajaran, Program Studi, dan Semester untuk menampilkan daftar Mata Kuliah.
                            <br>
                            @if($tahunAjaranAktif)
                            Tahun Ajaran Aktif: {{ $tahunAjaranAktif->nama }} ({{ $tahunAjaranAktif->semester }})
                            @endif
                        </p>
                    </div>

                    <!-- Image Section -->
                    <div class="col-md-5 text-center">
                        <img src="{{ asset('assets/img/illustrations/mahasiswa.png') }}" class="img-fluid"
                            alt="Illustration for morning schedule" style="max-height: 200px;">
                    </div>
                </div>

            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.assign.dosen') }}" method="POST" id="form-assign">
                    @csrf

                    <!-- Global Filter (Cascading Setup) -->
                    <h5 class="fw-bold d-flex align-items-center text-primary mb-3"><i class="bx bx-filter-alt me-2"></i> Filter Utama (Global Filters)</h5>
                    <div class="row mb-4 p-3 bg-light rounded border mx-0">
                        <div class="col-md-4 mb-2">
                            <label for="filter_ta_id" class="form-label fw-bold">Pilih Tahun Ajaran</label>
                            <select name="filter_ta_id" id="filter_ta_id" class="form-select select2 filter-global" required>
                                <option value="" disabled selected>-- Pilih Tahun Ajaran --</option>
                                @foreach($tahunAjaranList as $ta)
                                <option value="{{ $ta->ta_id }}" {{ $tahunAjaranAktif && $ta->ta_id == $tahunAjaranAktif->ta_id ? 'selected' : '' }}>
                                    {{ $ta->nama }} ({{ $ta->semester }}) {!! $ta->status_ta == 1 ? '&#9679; Aktif' : '' !!}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="filter_prodi_id" class="form-label fw-bold">Pilih Program Studi</label>
                            <select name="filter_prodi_id" id="filter_prodi_id" class="form-select select2 filter-global" required>
                                <option value="" disabled selected>-- Pilih Program Studi --</option>
                                @foreach ($programStudi as $ps)
                                <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="filter_smt" class="form-label fw-bold">Pilih Semester</label>
                            <select name="filter_smt" id="filter_smt" class="form-select select2 filter-global" required>
                                <option value="" disabled selected>-- Pilih Semester --</option>
                                @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 d-flex align-items-center"><i class="bx bx-list-plus me-2 text-primary"></i> Form Assign Dosen (Multiple Adds)</h5>
                    <hr class="mt-0 mb-4">

                    <div class="row">
                        <!-- Pilihan Mata Kuliah (Disabled default) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Pilih Mata Kuliah</label>
                            <select name="kurikulum_id" id="kurikulum_id" class="form-control select2" required disabled>
                                <option value="" disabled selected>-- Lengkapi Filter Dahulu --</option>
                            </select>
                        </div>

                        <!-- Pilihan Dosen (Multiple) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Pilih Dosen (Bisa pilih lebih dari 1)</label>
                            <select name="dosen_id[]" id="dosen_id" class="form-control select2" multiple="multiple" required>
                                @foreach($dosens as $dosen)
                                <option value="{{ $dosen->dosen_id }}">{{ $dosen->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Jenis Dosen -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Jenis Mata Kuliah</label>
                            <select name="jenis_dosen" id="jenis_dosen" class="form-control select2" required>
                                <option value="" disabled selected>Pilih Jenis</option>
                                <option value="teori">Dosen Teori</option>
                                <option value="praktik">Dosen Praktik</option>
                            </select>
                        </div>

                        <!-- Jenis Kelas -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Jenis Kelas</label>
                            <select name="jenis_kelas" id="jenis_kelas" class="form-control select2" required>
                                <option value="" disabled selected>Pilih Kelas</option>
                                <option value="reguler">Reguler</option>
                                <option value="karyawan">Karyawan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-end border-top pt-4">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" id="btn-submit">
                            <i class="bx bx-save me-1"></i> Simpan Penugasan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Table Section: List Assigned Dosen -->
<div class="card mt-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">List Pengajaran Dosen</h5>
            <button id="search-btn" class="btn btn-outline-primary btn-sm">
                <i class="bx bx-refresh"></i> Muat Ulang Tabel
            </button>
        </div>

        <div id="loading" class="text-center mt-3" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data...</p>
        </div>
        <div class="table-responsive">
            <table id="dosen-table" class="table table-bordered table-hover mt-3" style="display: none;">
                <thead class="table-primary">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Mata Kuliah</th>
                        <th>Kode</th>
                        <th>Semester</th>
                        <th>Dosen Reguler</th>
                        <th>Dosen Karywan</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div id="alert-container" class="mt-3"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Init Select2
        $('.select2').select2({ width: '100%' });

        // CASCADING DROPDOWN LOGIC
        $('.filter-global').on('change', function() {
            let taId = $('#filter_ta_id').val();
            let prodiId = $('#filter_prodi_id').val();
            let semester = $('#filter_smt').val();
            let kurikulumSelect = $('#kurikulum_id');

            // Reset Mata Kuliah select
            kurikulumSelect.empty().append('<option value="" disabled selected>Memuat list mata kuliah...</option>');
            kurikulumSelect.prop('disabled', true);

            // Cek jika ketiga filter sudah ada nilainya
            if (taId && prodiId && semester) {
                $.ajax({
                    url: "{{ route('admin.assign.kurikulum.filter') ?? '/admin/assign/kurikulum/filter/cascade' }}",
                    type: "GET",
                    data: {
                        tahunAjaran: taId,
                        programStudi: prodiId,
                        semester: semester
                    },
                    success: function(data) {
                        kurikulumSelect.empty().append('<option value="" disabled selected>Pilih Mata Kuliah</option>');
                        if (data.length > 0) {
                            data.forEach(function(k) {
                                kurikulumSelect.append(
                                    `<option value="${k.kurikulum_id}">${k.matakuliah_id} - ${k.nama} - ${k.smt} - ${k.semester}</option>`
                                );
                            });
                            kurikulumSelect.prop('disabled', false);
                        } else {
                            kurikulumSelect.empty().append('<option value="" disabled selected>Tidak ada Mata Kuliah di kurikulum ini</option>');
                        }
                        kurikulumSelect.trigger('change.select2');

                        // Automatis trigger memuat ulang tabel di bawah jika filter lengkap
                        loadTableData(taId, prodiId, semester);
                    },
                    error: function() {
                        kurikulumSelect.empty().append('<option value="" disabled selected>Gagal memuat mata kuliah</option>');
                        kurikulumSelect.trigger('change.select2');
                    }
                });
            } else {
                kurikulumSelect.empty().append('<option value="" disabled selected>-- Lengkapi Filter Dahulu --</option>');
                kurikulumSelect.trigger('change.select2');
            }
        });

        function showNotification(type, message) {
            let alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-warning');
            let iconClass = type === 'success' ? 'bx-check-circle' : (type === 'error' ? 'bx-error-circle' : 'bx-info-circle');
            let html = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="bx ${iconClass} me-1"></i> ${message.replace(/\n/g, '<br>')}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('#alert-container').html(html);
            $('html, body').animate({ scrollTop: $("#alert-container").offset().top - 100 }, 300);

            setTimeout(() => {
                $('#alert-container .alert').slideUp(300, function() { $(this).remove(); });
            }, 5000);
        }

        // FORM SUBMIT VIA AJAX
        $('#form-assign').submit(function(e) {
            e.preventDefault();

            let formElement = $(this);
            let submitBtn = $('#btn-submit');
            let originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Sedang Menyimpan...');

            // Enable disabled selects momentarily for serialization
            let disabledSelects = formElement.find('select:disabled');
            disabledSelects.prop('disabled', false);

            let formData = new FormData(this);

            disabledSelects.prop('disabled', true);

            $.ajax({
                url: formElement.attr('action'),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    showNotification('success', response.message);

                    // Reset fields except filters
                    $('#dosen_id').val([]).trigger('change');
                    $('#jenis_dosen').val('').trigger('change');
                    $('#jenis_kelas').val('').trigger('change');

                    submitBtn.prop('disabled', false).html(originalText);

                    // Reload table if filters are completed
                    $('#search-btn').trigger('click');
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = '';
                        $.each(errors, function(key, value) {
                            errorMessages += value[0] + '\n';
                        });
                        showNotification('error', "Validasi Gagal:\n" + errorMessages);
                    } else {
                        showNotification('error', "Gagal assign dosen: " + (xhr.responseJSON?.message || 'Server Error'));
                    }
                }
            });
        });

        // TABLE LOADING LOGIC
        $('#search-btn').on('click', function (e) {
            e.preventDefault();
            let ta = $('#filter_ta_id').val();
            let ps = $('#filter_prodi_id').val();
            let smt = $('#filter_smt').val();

            if (ta && ps && smt) {
                loadTableData(ta, ps, smt);
            } else {
                showNotification('warning', "Silakan lengkapi Filter Utama terlebih dahulu sebelum memuat tabel.");
            }
        });

        function loadTableData(tahunAjaran, programStudi, semester) {
            let alertContainer = $('#alert-container');
            let loading = $('#loading');
            let table = $('#dosen-table');
            let tbody = table.find('tbody');

            alertContainer.html("");
            table.hide();
            tbody.empty();
            loading.show();

            fetch(`{{ route('admin.assign.filter') }}?tahunAjaran=${encodeURIComponent(tahunAjaran)}&programStudi=${encodeURIComponent(programStudi)}&semester=${encodeURIComponent(semester)}`)
                .then(response => response.json())
                .then(data => {
                    loading.hide();

                    if (data.message) {
                        alertContainer.html(`<div class="alert alert-info">${data.message}</div>`);
                        return;
                    }

                    let groupedData = new Map();

                    data.forEach(dosenAssign => {
                        let kurikulumId = dosenAssign.kurikulum.kurikulum_id;
                        let mk = dosenAssign.kurikulum.mata_kuliah || dosenAssign.kurikulum.matakuliah;

                        if (!groupedData.has(kurikulumId)) {
                            groupedData.set(kurikulumId, {
                                matakuliah_nama: mk ? mk.nama : '-',
                                matakuliah_id: mk ? mk.matakuliah_id : '-',
                                semester: mk ? mk.smt : '-',
                                reguler: [],
                                karyawan: []
                            });
                        }

                        if (dosenAssign.jenis_kelas === 'reguler') {
                            groupedData.get(kurikulumId).reguler.push(dosenAssign);
                        } else {
                            groupedData.get(kurikulumId).karyawan.push(dosenAssign);
                        }
                    });

                    let index = 1;
                    groupedData.forEach((kurikulum, kurikulumId) => {
                        let row = document.createElement("tr");

                        row.innerHTML = `
                            <td class="text-center">${index++}</td>
                            <td class="fw-bold">${kurikulum.matakuliah_nama}</td>
                            <td><code>${kurikulum.matakuliah_id}</code></td>
                            <td><span class="badge bg-label-info">Smt ${kurikulum.semester}</span></td>
                        `;

                        let regulerTd = document.createElement("td");
                        regulerTd.appendChild(createDosenList(kurikulum.reguler));
                        row.appendChild(regulerTd);

                        let karyawanTd = document.createElement("td");
                        karyawanTd.appendChild(createDosenList(kurikulum.karyawan));
                        row.appendChild(karyawanTd);

                        tbody.append(row);
                    });

                    table.show();
                })
                .catch(error => {
                    loading.hide();
                    alertContainer.html(`<div class="alert alert-danger">Terjadi kesalahan: ${error.message}</div>`);
                });
        }

        // Fungsi Helper merakit ul li Dosen
        function createDosenList(dosenArray) {
            if(dosenArray.length === 0) {
                let em = document.createElement("span");
                em.className = "text-muted small";
                em.innerText = "Belum Ada Dosen";
                return em;
            }

            let divCont = document.createElement("div");
            divCont.className = "d-flex flex-wrap gap-2";

            dosenArray.forEach(d => {
                let dosenNama = (d.dosen && d.dosen.nama) ? d.dosen.nama : 'Dosen Tidak Ditemukan';
                let isTeori = d.jenis_dosen === 'teori';

                let badge = document.createElement("span");
                badge.className = `badge bg-label-${isTeori ? 'primary' : 'success'} d-flex align-items-center mb-1`;
                badge.style.fontSize = "0.75rem";
                badge.id = `dosen-${d.id}`;

                let iconClass = isTeori ? 'bx-book-open' : 'bx-test-tube';

                badge.innerHTML = `
                    <i class='bx ${iconClass} me-1'></i>
                    ${dosenNama}
                    <button type="button" class="btn-close ms-2 btn-remove-dosen" style="font-size: 0.55rem;" aria-label="Close" title="Hapus Dosen" data-id="${d.id}"></button>
                `;
                divCont.appendChild(badge);
            });

            return divCont;
        }

        // Logic Hapus Dosen AJAX
        $(document).on('click', '.btn-remove-dosen', function(e) {
            e.preventDefault();
            let dosenId = $(this).data('id');
            let badgeEl = $(this).closest('.badge');

            if (!confirm("Hapus dosen ini beserta mata kuliah yang diajarkannya?")) return;

            fetch(`{{ route('admin.remove.dosen.kurikulum', ':id') }}`.replace(':id', dosenId), {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    badgeEl.animate({ opacity: 0 }, 200, function() { $(this).remove(); });
                    showNotification('success', result.message);
                } else {
                    showNotification('error', "Gagal menghapus: " + result.message);
                }
            })
            .catch(error => {
                showNotification('error', "Terjadi kesalahan: " + error.message);
            });
        });
    });
</script>
@endsection
