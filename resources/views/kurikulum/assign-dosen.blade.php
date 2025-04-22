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
                            Halaman ini berisi daftar relasi antara dosen dan mata kuliah yang diajarkan. Silakan
                            pilih
                            dosen dan mata kuliah terlebih dahulu untuk menambahkan relasi baru.
                            <br>
                            Tahun Ajaran {{ $tahunAjaran->nama }} ({{ $tahunAjaran->semester }})
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
        <div class="card">
            {{-- <div class="card-header">
                <h5>List Pengajaran Dosen</h5>
            </div> --}}
            <div class="card-body">
                <form action="{{ route('admin.assign.dosen') }}" method="POST">
                    @csrf
                    <div class="row">
                        <!-- Pilihan Dosen -->
                        <div class="col-md-6 mb-3">
                            <label for="dosen_id" class="form-label">Pilih Dosen</label>
                            <select name="dosen_id" id="dosen_id" class="form-control select2">
                                <option value="" disabled selected>Pilih Dosen</option>
                                @foreach($dosens as $dosen)
                                <option value="{{ $dosen->dosen_id }}">{{ $dosen->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pilihan Mata Kuliah  -->
                        <div class="col-md-6 mb-3">
                            <label for="kurikulum_id" class="form-label">Pilih Mata Kuliah</label>
                            <select name="kurikulum_id" id="kurikulum_id" class="form-control select2">
                                <option value="" disabled selected>Pilih Mata Kuliah</option>
                                @foreach($kurikulums as $kurikulum)
                                <option value="{{ $kurikulum->kurikulum_id }}">
                                    {{ $kurikulum->matakuliah_id }} - {{ $kurikulum->matakuliah->nama}} -{{
                                    $kurikulum->matakuliah->smt}} - {{ $kurikulum->matakuliah->semester}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jenis_dosen" class="form-label">Jenis Mata Kuliah</label>
                            <select name="jenis_dosen" id="jenis_dosen" class="form-control select2">
                                <option value="" disabled selected>Pilih Jenis Mata Kuliah</option>
                                <option value="teori">Dosen Teori</option>
                                <option value="praktik">Dosen Praktik</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelas" class="form-label">Jenis Kelas</label>
                            <select name="jenis_kelas" id="jenis_kelas" class="form-control select2">
                                <option value="" disabled selected>Pilih Jenis Kelas</option>
                                <option value="reguler">Reguler</option>
                                <option value="karyawan">Karyawan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<!-- Filter Section -->
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Filter Pengajaran Dosen</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="program-studi" class="form-label fw-bold">Pilih Program Studi</label>
                <select id="program-studi" class="form-select">
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach ($programStudi as $ps)
                    <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="semester" class="form-label fw-bold">Pilih Semester</label>
                <select id="semester" class="form-select">
                    <option value="">-- Pilih Semester --</option>
                    @for ($i = 1; $i <= 8; $i++) <option value="{{ $i }}">Semester {{ $i }}</option>
                        @endfor
                </select>
            </div>
        </div>
        <div class="text-end mt-3">
            <button id="search-btn" class="btn btn-primary">
                <i class="bx bx-search"></i> Cari Pengajaran Dosen
            </button>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">List Pengajaran Dosen</h5>
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
                        <th>#</th>
                        <th>Mata Kuliah</th>
                        <th>Kode</th>
                        <th>Semester</th>
                        <th>Nama Dosen Reguler</th>
                        <th>Nama Dosen Karywan</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div id="alert-container" class="mt-3"></div>


<script>
    document.getElementById('search-btn').addEventListener('click', function () {
        let programStudi = document.getElementById('program-studi').value;
        let semester = document.getElementById('semester').value;
        let alertContainer = document.getElementById('alert-container');
        let loading = document.getElementById('loading');
        let table = document.getElementById('dosen-table');
        let tbody = table.querySelector('tbody');

        alertContainer.innerHTML = "";
        table.style.display = "none";
        tbody.innerHTML = "";

        if (!programStudi || !semester) {
            alertContainer.innerHTML = `<div class="alert alert-warning">Silakan pilih program studi dan semester terlebih dahulu.</div>`;
            return;
        }

        loading.style.display = "block";
        fetch(`{{ route('admin.admin.assign.filter') }}?programStudi=${encodeURIComponent(programStudi)}&semester=${encodeURIComponent(semester)}`)
            .then(response => response.json())
            .then(data => {
                loading.style.display = "none";
                alertContainer.innerHTML = "";
                tbody.innerHTML = "";

                if (data.message) {
                    alertContainer.innerHTML = `<div class="alert alert-info">${data.message}</div>`;
                    return;
                }

                // Grup data berdasarkan kurikulum_id
                let groupedData = new Map();

                data.forEach(dosen => {
                    let kurikulumId = dosen.kurikulum.kurikulum_id;
                    if (!groupedData.has(kurikulumId)) {
                        groupedData.set(kurikulumId, {
                            matakuliah_nama: dosen.kurikulum.matakuliah.nama,
                            matakuliah_id: dosen.kurikulum.matakuliah.matakuliah_id,
                            semester: dosen.kurikulum.matakuliah.smt,
                            reguler: [],
                            karyawan: []
                        });
                    }

                    // Memisahkan dosen Reguler dan Karyawan
                    if (dosen.jenis_kelas === 'reguler') {
                        groupedData.get(kurikulumId).reguler.push(dosen);
                    } else {
                        groupedData.get(kurikulumId).karyawan.push(dosen);
                    }
                });

                let index = 1;
                groupedData.forEach((kurikulum, kurikulumId) => {
                    let row = document.createElement("tr");

                    row.innerHTML = `
                        <td>${index++}</td>
                        <td>${kurikulum.matakuliah_nama}</td>
                        <td>${kurikulum.matakuliah_id}</td>
                        <td>Semester ${kurikulum.semester}</td>
                    `;

                    // Menampilkan daftar dosen reguler
                    let regulerTd = document.createElement("td");
                    let regulerList = createDosenList(kurikulum.reguler);
                    regulerTd.appendChild(regulerList);
                    row.appendChild(regulerTd);

                    // Menampilkan daftar dosen karyawan
                    let karyawanTd = document.createElement("td");
                    let karyawanList = createDosenList(kurikulum.karyawan);
                    karyawanTd.appendChild(karyawanList);
                    row.appendChild(karyawanTd);

                    tbody.appendChild(row);
                });

                table.style.display = "table";
            })
            .catch(error => {
                loading.style.display = "none";
                alertContainer.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan: ${error.message}</div>`;
            });
    });

    // Fungsi untuk membuat daftar dosen dengan badge dan tombol hapus
    function createDosenList(dosenArray) {
        let dosenList = document.createElement("ul");
        dosenList.style.listStyle = "none";
        dosenList.style.padding = "0";

        dosenArray.forEach(d => {
            let listItem = document.createElement("li");
            listItem.id = `dosen-${d.id}`;
            listItem.style.marginBottom = "5px";

            let badge = document.createElement("span");
            badge.className = `badge bg-${d.jenis_dosen === 'teori' ? 'primary' : 'success'}`;
            badge.innerText = `${d.dosen.nama} (${d.jenis_dosen})`;

            let removeButton = document.createElement("button");
            removeButton.className = "btn btn-sm btn-danger";
            removeButton.innerText = "Hapus";
            removeButton.addEventListener("click", function (event) {
                event.preventDefault();
                removeDosen(d.id, listItem);
            });

            listItem.appendChild(badge);
            listItem.appendChild(removeButton);
            dosenList.appendChild(listItem);
        });

        return dosenList;
    }

    // Fungsi untuk menghapus dosen
    function removeDosen(dosenId, listItem) {
        if (!confirm("Apakah Anda yakin ingin menghapus dosen ini?")) {
            return;
        }

        fetch(`{{ route('admin.remove.dosen.kurikulum', ':id') }}`.replace(':id', dosenId), {
            method: "DELETE",
            headers: {
               "Content-Type": "application/json", // Tambahkan ini
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                listItem.remove(); // Hapus elemen dari tampilan
                alert(result.message);
            } else {
                alert("Gagal menghapus: " + result.message);
            }
        })
        .catch(error => {
            alert("Terjadi kesalahan: " + error.message);
        });
    }

</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('form').submit(function(e) {
            e.preventDefault(); // Mencegah reload halaman

            let formData = {
                _token: $('input[name="_token"]').val(),
                dosen_id: $('#dosen_id').val(),
                kurikulum_id: $('#kurikulum_id').val(),
                jenis_dosen: $('#jenis_dosen').val(),
                jenis_kelas: $('#jenis_kelas').val(),
            };

            $.ajax({
                url: "{{ route('admin.assign.dosen') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    alert(response.message); // Tampilkan pesan sukses
                    $('form')[0].reset(); // Reset form
                    $('.select2').val(null).trigger('change'); // Reset Select2
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = '';
                        $.each(errors, function(key, value) {
                            errorMessages += value[0] + '\n';
                        });
                        alert("Validasi Gagal:\n" + errorMessages);
                    } else {
                        alert("Gagal assign dosen: " + xhr.responseJSON.message);
                    }
                }
            });
        });
    });
</script>
@endsection