@extends('layouts.master')
@section('title', 'Mata Kuliah')
@section('content')

    <!-- Header Info -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-0 align-items-center">
                <div class="col-md-7">
                    <h5 class="card-title text-primary mb-3 fw-bold">Manajemen Mata Kuliah (Kurikulum)</h5>
                    <p class="mb-4 text-muted" style="line-height: 1.6;">
                        Halaman ini digunakan untuk mengelola daftar matakuliah yang diajarkan pada masing-masing program
                        studi dan semester.
                        Sesuai dengan tahun ajaran aktif: <br>
                        <span class="badge bg-label-primary mt-2 fs-6">Tahun Ajaran {{ $tahunAjaran->nama }}
                            ({{ $tahunAjaran->semester }})</span>
                    </p>
                </div>
                <div class="col-md-5 text-center">
                    <img src="../assets/img/illustrations/kartu-study.png" class="img-fluid" alt="Illustration"
                        style="max-height: 150px;">
                </div>
            </div>
        </div>
    </div>

    @can('jadwal-uts-create')
        <!-- TAMBAH MULTIPLE MATA KULIAH SECTION (DIRECT IN PAGE) -->
        <div class="card shadow-sm mb-4 border-top border-5 border-success">
            <div class="card-header bg-white pb-0">
                <h5 class="card-title text-success mb-0">
                    <i class="bx bx-list-plus me-1"></i> Form Tambah Multiple Mata Kuliah
                </h5>
                <small class="text-muted">Masukkan beberapa mata kuliah sekaligus ke kurikulum aktif.</small>
            </div>
            <div class="card-body mt-3">
                <input type="hidden" id="modal-ta-id" value="{{ $tahunAjaran->ta_id }}">

                <!-- Filter Mata Kuliah Opsional -->
                <div class="row align-items-end mb-4 bg-light p-3 rounded">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <label class="form-label fw-semibold text-secondary">
                            <i class="bx bx-filter-alt"></i> Filter Pilihan Program Studi (Opsional)
                        </label>
                        <select id="modal-filter-prodi" class="form-select">
                            <option value="">Semua Program Studi</option>
                            @foreach ($programStudi as $ps)
                                <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary">
                            <i class="bx bx-calendar"></i> Filter Pilihan Semester (Opsional)
                        </label>
                        <select id="modal-filter-smt" class="form-select">
                            <option value="">Semua Semester</option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- Maste Select for Cloning (Hidden) -->
                <select style="display:none;" id="modal-master-select">
                    <option value="">-- Pilih Mata Kuliah --</option>
                    @foreach($mataKuliah as $mata)
                        <option value="{{ $mata->matakuliah_id }}" data-jurusan="{{ $mata->jurusan_id }}"
                            data-smt="{{ $mata->smt }}">
                            {{ $mata->matakuliah_id }} - {{ $mata->nama }} (Semester {{ $mata->smt }})
                        </option>
                    @endforeach
                </select>

                <!-- Container baris input -->
                <div id="matkul-rows-container">
                    <!-- Baris pertama (template) -->
                    <div class="matkul-row card mb-3 border shadow-none bg-label-secondary" data-index="0">
                        <div class="card-body py-3">
                            <div class="row align-items-end">
                                <div class="col-md-10">
                                    <label class="form-label fw-semibold">
                                        <span class="badge bg-primary rounded-pill me-1 row-number">1</span>
                                        Pilih Mata Kuliah
                                    </label>
                                    <select name="matakuliah_ids[]" class="form-select select2-multiple-input matkul-select"
                                        required>
                                        <option value="">-- Pilih Mata Kuliah --</option>
                                        @foreach($mataKuliah as $mata)
                                            <option value="{{ $mata->matakuliah_id }}" data-jurusan="{{ $mata->jurusan_id }}"
                                                data-smt="{{ $mata->smt }}">
                                                {{ $mata->matakuliah_id }} - {{ $mata->nama }} (Semester {{ $mata->smt }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 text-end mt-2 mt-md-0">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn w-100" disabled>
                                        <i class="bx bx-trash me-1"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Tambah Baris & Simpan -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" id="add-row-btn" class="btn btn-outline-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Baris Mata Kuliah
                    </button>
                    <div class="d-flex align-items-center">
                        <span id="summary-count" class="badge bg-info me-3 py-2 px-3 fs-6" style="display: none;">0 mata kuliah
                            dipilih</span>
                        <button type="button" id="save-multiple-btn" class="btn btn-success btn-lg">
                            <i class="bx bx-save me-1"></i> Simpan ke Kurikulum
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    <!-- LIST MATA KULIAH (FILTER & TABLE) -->
    <div class="card shadow-sm mt-4 border-top border-5 border-primary">
        <div class="card-header bg-white pb-0">
            <h5 class="card-title text-primary mb-0"><i class="bx bx-search me-1"></i> Cari & Tampilkan Daftar Mata Kuliah
            </h5>
        </div>
        <div class="card-body">
            <div class="row mt-3">
                <div class="col-md-6 mb-3">
                    <label for="program-studi" class="form-label fw-bold">Pilih Program Studi <span
                            class="text-danger">*</span></label>
                    <select id="program-studi" class="form-select">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach ($programStudi as $ps)
                            <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="semester" class="form-label fw-bold">Pilih Semester <span
                            class="text-danger">*</span></label>
                    <select id="semester" class="form-select">
                        <option value="">-- Pilih Semester --</option>
                        @for ($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}">Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="mt-2 text-end">
                <button id="search-btn" class="btn btn-primary px-4"><i class="bx bx-search-alt me-1"></i>
                    Tampilkan</button>
            </div>

            <div id="alert-container" class="mt-3"></div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title"><i class="bx bx-table me-1"></i> Hasil Pencarian Kurikulum</h5>
            <div id="loading" class="text-center mt-3" style="display: none;">
                <span class="spinner-border text-primary"></span> <br>
                <small>Memuat data...</small>
            </div>

            <div class="table-responsive">
                <table id="kurikulum-table" class="table table-bordered table-striped mt-3" style="display: none;">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Kode Mata Kuliah</th>
                            <th>Mata Kuliah</th>
                            <th>Semester</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data akan diisi dengan AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // ==================== FILTER & TABLE MATA KULIAH ====================
        document.getElementById('search-btn').addEventListener('click', function () {
            let programStudi = document.getElementById('program-studi').value;
            let semester = document.getElementById('semester').value;
            let alertContainer = document.getElementById('alert-container');
            let loading = document.getElementById('loading');
            let table = document.getElementById('kurikulum-table');
            let tbody = table.querySelector('tbody');

            alertContainer.innerHTML = "";
            table.style.display = "none";
            tbody.innerHTML = "";

            if (!programStudi || !semester) {
                alertContainer.innerHTML = `<div class="alert alert-warning">Silakan pilih program studi dan semester terlebih dahulu.</div>`;
                return;
            }

            loading.style.display = "block";

            fetch(`{{ route('admin.kurikulum.filter') }}?programStudi=${programStudi}&semester=${semester}`)
                .then(response => {
                    if (!response.ok) throw new Error('Terjadi kesalahan saat mengambil data.');
                    return response.json();
                })
                .then(data => {
                    loading.style.display = "none";

                    if (data.message) {
                        alertContainer.innerHTML = `<div class="alert alert-info">${data.message}</div>`;
                        return;
                    }

                    data.forEach((kurikulum, index) => {
                        let editUrl = `{{ route('admin.kurikulum.edit', ':kurikulum_id') }}`.replace(':kurikulum_id', kurikulum.kurikulum_id);
                        let destroyUrl = `{{ route('admin.kurikulum.destroy', ':kurikulum_id') }}`.replace(':kurikulum_id', kurikulum.kurikulum_id);
                        let row = `<tr>
                                <td>${index + 1}</td>
                                <td>${kurikulum.matakuliah_id}</td>
                                <td>${kurikulum.nama_matakuliah}</td>
                                <td>Semester ${kurikulum.semester}</td>
                                <td>
                                    @can('kurikulum-edit')
                                        <a href="${editUrl}" class="btn btn-sm btn-warning">Edit</a>
                                    @endcan
                                    @can('kurikulum-delete')
                                        <form action="${destroyUrl}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger delete-btn" data-id="${kurikulum.kurikulum_id}" onclick="event.preventDefault(); window.deleteRow(this);">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>`;
                        tbody.innerHTML += row;
                    });

                    table.style.display = "table";
                })
                .catch(error => {
                    loading.style.display = "none";
                    alertContainer.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
                    console.error('Error:', error);
                });
        });

        window.deleteRow = function (btn) {
            let id = btn.getAttribute('data-id');
            let confirmation = confirm("Apakah Anda yakin ingin menghapus data ini?");
            if (confirmation) {

                // --- BAGIAN YANG DI-IMPROVE ---
                // Kita pakai 'id_placeholder' agar Blade tidak error, lalu JS yang akan menggantinya dengan id asli
                let url = `{{ route('admin.kurikulum.destroy', 'id_placeholder') }}`.replace('id_placeholder', id);

                fetch(url, {
                    // ------------------------------

                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', 'Data berhasil dihapus.', 'success');
                            btn.closest('tr').remove();
                        } else {
                            Swal.fire('Gagal!', 'Gagal menghapus data.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('search-btn').click();
                    });
            }
        };
    </script>

    {{-- ==================== SCRIPT MULTIPLE INPUT DIRECT ==================== --}}
    @can('jadwal-uts-create')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let rowIndex = 1;
                const container = document.getElementById('matkul-rows-container');
                const addBtn = document.getElementById('add-row-btn');
                const saveBtn = document.getElementById('save-multiple-btn');

                // Init initial Select2
                $('.select2-multiple-input').select2({
                    placeholder: '-- Pilih Mata Kuliah --',
                    allowClear: true,
                    width: '100%'
                });

                function getFilteredOptionsHtml() {
                    let prodi = document.getElementById('modal-filter-prodi').value;
                    let smt = document.getElementById('modal-filter-smt').value;

                    let html = '<option value="">-- Pilih Mata Kuliah --</option>';
                    const masterOptions = document.getElementById('modal-master-select').options;

                    for (let i = 1; i < masterOptions.length; i++) {
                        let opt = masterOptions[i];
                        let optProdi = opt.getAttribute('data-jurusan');
                        let optSmt = opt.getAttribute('data-smt');

                        let matchProdi = prodi === "" || prodi == optProdi;
                        let matchSmt = smt === "" || smt == optSmt;

                        if (matchProdi && matchSmt) {
                            html += `<option value="${opt.value}" data-jurusan="${optProdi}" data-smt="${optSmt}">${opt.text}</option>`;
                        }
                    }
                    return html;
                }

                function applyFilters() {
                    let newOptionsHtml = getFilteredOptionsHtml();
                    let selects = container.querySelectorAll('.matkul-select');

                    selects.forEach(sel => {
                        let currentVal = $(sel).val();

                        if ($(sel).hasClass('select2-hidden-accessible')) {
                            $(sel).select2('destroy');
                        }

                        sel.innerHTML = newOptionsHtml;

                        if (currentVal && sel.querySelector(`option[value="${currentVal}"]`)) {
                            $(sel).val(currentVal);
                        } else {
                            $(sel).val("");
                        }

                        $(sel).select2({
                            placeholder: '-- Pilih Mata Kuliah --',
                            allowClear: true,
                            width: '100%'
                        });
                    });
                    updateSummary();
                }

                document.getElementById('modal-filter-prodi').addEventListener('change', applyFilters);
                document.getElementById('modal-filter-smt').addEventListener('change', applyFilters);

                function updateRowNumbers() {
                    const rows = container.querySelectorAll('.matkul-row');
                    rows.forEach((row, idx) => {
                        row.querySelector('.row-number').textContent = idx + 1;
                        const removeBtn = row.querySelector('.remove-row-btn');
                        removeBtn.disabled = rows.length <= 1;
                    });
                    updateSummary();
                }

                function updateSummary() {
                    const selects = container.querySelectorAll('.matkul-select');
                    let count = 0;
                    selects.forEach(sel => {
                        // Bug fix: gunakan jQuery .val() untuk mendapatkan value Select2 yang paling akurat
                        if ($(sel).val()) count++;
                    });

                    const summaryEl = document.getElementById('summary-count');
                    if (count > 0) {
                        summaryEl.style.display = 'inline-block';
                        summaryEl.textContent = `${count} mata kuliah dipilih`;
                    } else {
                        summaryEl.style.display = 'none';
                    }
                }

                addBtn.addEventListener('click', function () {
                    rowIndex++;
                    const newRow = document.createElement('div');
                    newRow.className = 'matkul-row card mb-3 border shadow-none bg-label-secondary';
                    newRow.setAttribute('data-index', rowIndex);

                    newRow.innerHTML = `
                                <div class="card-body py-3">
                                    <div class="row align-items-end">
                                        <div class="col-md-10">
                                            <label class="form-label fw-semibold">
                                                <span class="badge bg-primary rounded-pill me-1 row-number">${rowIndex}</span>
                                                Pilih Mata Kuliah
                                            </label>
                                            <select name="matakuliah_ids[]" class="form-select select2-multiple-input matkul-select" required>
                                                ${getFilteredOptionsHtml()}
                                            </select>
                                        </div>
                                        <div class="col-md-2 text-end mt-2 mt-md-0">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn w-100" title="Hapus baris">
                                                <i class="bx bx-trash me-1"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                    container.appendChild(newRow);

                    $(newRow).find('.select2-multiple-input').select2({
                        placeholder: '-- Pilih Mata Kuliah --',
                        allowClear: true,
                        width: '100%'
                    });

                    updateRowNumbers();
                    newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });

                container.addEventListener('click', function (e) {
                    const removeBtn = e.target.closest('.remove-row-btn');
                    if (!removeBtn || removeBtn.disabled) return;

                    const row = removeBtn.closest('.matkul-row');
                    if (container.querySelectorAll('.matkul-row').length > 1) {
                        $(row).find('.select2-multiple-input').select2('destroy');
                        row.remove();
                        updateRowNumbers();
                    }
                });

                // Deteksi perubahan dari jQuery select2
                $(container).on('change', '.matkul-select', function () {
                    updateSummary();
                });

                saveBtn.addEventListener('click', function () {
                    const selects = container.querySelectorAll('.matkul-select');
                    const taId = document.getElementById('modal-ta-id').value;
                    const selectedIds = [];
                    const duplicates = new Set();

                    selects.forEach(sel => {
                        // Gunakan .val() jQuery untuk bug fix array
                        const val = $(sel).val();
                        if (val && val !== '') {
                            if (selectedIds.includes(val)) {
                                duplicates.add(val);
                            } else {
                                selectedIds.push(val);
                            }
                        }
                    });

                    if (selectedIds.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Pilih minimal 1 mata kuliah terlebih dahulu.',
                        });
                        return;
                    }

                    if (duplicates.size > 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Terdapat Duplikat',
                            text: 'Anda memilih Beberapa mata kuliah yang sama. Data duplikat akan diabaikan.',
                        });
                    }

                    Swal.fire({
                        title: 'Simpan Kurikulum',
                        html: `Menambahkan <strong>${selectedIds.length}</strong> mata kuliah unik ke kurikulum.<br>Lanjutkan?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Simpan!',
                        cancelButtonText: 'Batal',
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        saveBtn.disabled = true;
                        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Proses...';

                        fetch('{{ route("admin.kurikulum.storeMultiple") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                matakuliah_ids: selectedIds,
                                ta_id: taId,
                            }),
                        })
                            .then(res => res.json())
                            .then(data => {
                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '<i class="bx bx-save me-1"></i> Simpan ke Kurikulum';

                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Tersimpan!',
                                        text: data.message,
                                        timer: 3000,
                                        showConfirmButton: true,
                                    });

                                    // Reset form setelah berhasil
                                    const rows = container.querySelectorAll('.matkul-row');
                                    rows.forEach((row, idx) => {
                                        if (idx > 0) {
                                            $(row).find('.select2-multiple-input').select2('destroy');
                                            row.remove();
                                        } else {
                                            $(row).find('.matkul-select').val('').trigger('change');
                                        }
                                    });
                                    rowIndex = 1;
                                    updateRowNumbers();

                                    // Auto refresh tabel jika filter aktif
                                    const searchBtn = document.getElementById('search-btn');
                                    if (document.getElementById('program-studi').value && document.getElementById('semester').value) {
                                        searchBtn.click();
                                    }
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: data.message || 'Terjadi kesalahan saat menyimpan.',
                                    });
                                }
                            })
                            .catch(error => {
                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '<i class="bx bx-save me-1"></i> Simpan ke Kurikulum';
                                console.error('Error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error Jaringan',
                                    text: 'Terjadi kesalahan jaringan. Silakan coba lagi.',
                                });
                            });
                    });
                });
            });
        </script>
    @endcan
@endsection