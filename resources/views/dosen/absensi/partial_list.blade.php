@foreach ($absensiList as $semester => $jadwalSemester)
<div class="mb-4 jadwal-hari-container">
    <h5 class="fw-bold text-primary"><i class="bx bx-calendar me-1"></i> Semester {{ $semester }}</h5>
    <div class="row">
        @foreach ($jadwalSemester as $jadwal)
        @php
        $jadwalId = $jadwal['jadwal_id'];
        $namaMatakuliah = $jadwal['nama_matakuliah'];
        $kodeMatakuliah = $jadwal['kode_matakuliah'] ?? '-';
        @endphp
        <div class="col-md-4 jadwal-card-item" data-nama="{{ $namaMatakuliah }}" data-dosen="{{ implode(', ', array_column($jadwal['dosen'], 'nama')) }}" data-jenis="{{ $jadwal['jenis_kelas'] }}">
            <div class="card shadow-sm mb-3 bg-light text-dark border-0" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'"
                onclick="openPertemuanModal('{{ $jadwalId }}', '{{ $namaMatakuliah }}', '{{ $kodeMatakuliah }}')">
                <div class="card-body">
                    <h6 class="card-title fw-bold text-dark border-start border-3 border-primary ps-2">
                        {{ $namaMatakuliah }}
                        <span class="badge bg-label-primary ms-2">{{ $kodeMatakuliah }}</span>
                    </h6>
                    <p class="text-muted mb-2">
                        <i class="bx bx-calendar-event"></i> {{ $jadwal['hari'] }}
                    </p>
                    <p class="mb-1">
                        <i class="bi bi-clock"></i> {{ $jadwal['jam_mulai'] }} - {{ $jadwal['jam_selesai'] }}
                    </p>
                    <p class="mb-1"><i class="bi bi-person-badge"></i> Dosen:</p>
                    <ul class="list-unstyled">
                        @if (count($jadwal['dosen']) > 0)
                        @foreach ($jadwal['dosen'] as $dosen)
                        <li class="text-muted">
                            <i class="bx bx-user me-1"></i> {{ $dosen['nama'] }}
                            <span class="badge bg-label-info text-dark ms-2 text-capitalize" style="font-size: 0.7rem;">
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark" id="pertemuanModalLabel"><i class="bx bx-calendar-plus text-primary me-2"></i>Buat Pertemuan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
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
                        <label for="sub_topik" class="form-label fw-semibold">Sub Topik</label>
                        <textarea class="form-control" id="sub_topik" name="sub_topik" rows="2" required placeholder="Detail bahasan..."></textarea>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill"><i class="bx bx-save me-1"></i>Simpan Pertemuan & Lanjut Absensi</button>
                    </div>
                </form>

                <!-- 🔹 List Pertemuan -->
                <div class="mt-5">
                    <h6 class="fw-bold text-muted border-bottom pb-2 mb-3"><i class="bx bx-list-ul me-1"></i>Riwayat Pertemuan Tersimpan</h6>
                    <ul id="pertemuanList" class="list-group list-group-flush border rounded">
                        <li class="list-group-item text-muted text-center py-4 bg-light">Memuat riwayat pertemuan...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>