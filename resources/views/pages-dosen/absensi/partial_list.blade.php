@foreach ($absensiList as $semester => $jadwalSemester)
<div class="mb-4">
    <h5 class="fw-bold text-primary">Semester {{ $semester }}</h5>
    <div class="row">
        @foreach ($jadwalSemester as $jadwal)
        @php
        $jadwalId = $jadwal['jadwal_id'];
        $namaMatakuliah = $jadwal['nama_matakuliah'];
        $kodeMatakuliah = $jadwal['kode_matakuliah'] ?? '-';
        @endphp
        <div class="col-md-4">
            <div class="card shadow-sm mb-3 bg-primary text-white" style="cursor: pointer;"
                onclick="openPertemuanModal('{{ $jadwalId }}', '{{ $namaMatakuliah }}', '{{ $kodeMatakuliah }}')">
                <div class="card-body">
                    <h6 class="card-title fw-bold text-white border-start border-3 ps-2">
                        {{ $namaMatakuliah }}
                        <span class="badge bg-secondary ms-2">{{ $kodeMatakuliah }}</span>
                    </h6>
                    <p class="text-white-50 mb-2">
                        <i class="bi bi-calendar-event"></i> {{ $jadwal['hari'] }}
                    </p>
                    <p class="mb-1">
                        <i class="bi bi-clock"></i> {{ $jadwal['jam_mulai'] }} - {{ $jadwal['jam_selesai'] }}
                    </p>
                    <p class="mb-1"><i class="bi bi-person-badge"></i> Dosen:</p>
                    <ul class="list-unstyled">
                        @if (count($jadwal['dosen']) > 0)
                        @foreach ($jadwal['dosen'] as $dosen)
                        <li>
                            <i class="bi bi-dot"></i> {{ $dosen['nama'] }}
                            <span class="badge bg-info text-dark ms-2 text-capitalize">
                                {{ $dosen['jenis_dosen'] }}
                            </span>
                        </li>
                        @endforeach
                        @else
                        <li class="text-muted">Tidak ada data dosen</li>
                        @endif
                    </ul>
                    @if ($jadwal['jenis_kelas'] == 'karyawan')
                    <div class="alert alert-warning mt-2 p-2" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> Kelas Karyawan
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach
<!-- 🔹 MODAL FORM UNTUK BUAT PERTEMUAN -->
<div class="modal fade" id="pertemuanModal" tabindex="-1" aria-labelledby="pertemuanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pertemuanModalLabel">Buat Pertemuan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pertemuanForm">
                    @csrf
                    <input type="hidden" id="jadwal_id" name="jadwal_id">

                    <div class="mb-3">
                        <label for="nama_matakuliah" class="form-label">Mata Kuliah</label>
                        <input type="text" class="form-control" id="nama_matakuliah" name="nama_matakuliah" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_pertemuan" class="form-label">Tanggal Pertemuan</label>
                        <input type="date" class="form-control" id="tanggal_pertemuan" name="tanggal_pertemuan"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="jam_mulai" class="form-label">Jam Mulai</label>
                        <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                        <p id="format-jam"></p>
                    </div>

                    <div class="mb-3">
                        <label for="jam_selesai" class="form-label">Jam Selesai</label>
                        <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                        <p id="format-jam-selesai"></p>
                    </div>

                    <div class="mb-3">
                        <label for="topik" class="form-label">Topik Pertemuan</label>
                        <textarea class="form-control" id="topik" name="topik" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="sub_topik" class="form-label">Sub Topik</label>
                        <textarea class="form-control" id="sub_topik" name="sub_topik" rows="2" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Simpan</button>
                </form>

                <!-- 🔹 List Pertemuan -->
                <hr>
                <h6 class="mt-3">Daftar Pertemuan</h6>
                <ul id="pertemuanList" class="list-group">
                    <li class="list-group-item text-muted">Memuat data...</li>
                </ul>
            </div>
        </div>
    </div>
</div>