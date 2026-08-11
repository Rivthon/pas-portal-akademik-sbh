@foreach ($jadwalList as $hari => $jadwalHari)
<div class="mb-4 jadwal-hari-container">
    <h5 class="fw-bold text-dark">
        <i class="bi bi-calendar-event"></i> {{ ucfirst($hari) }}
    </h5>
    <div class="row">
        @foreach ($jadwalHari as $jadwal)
        @php
        $dosenTersaring = collect($jadwal['dosen'])->filter(function ($dosen) use ($jadwal) {
        return $dosen['jenis_kelas'] == $jadwal['jenis_kelas'];
        });
        $jadwalId = $jadwal['jadwal_praktik_id'];
        $namaMatakuliah = $jadwal['nama_matakuliah'];
        $kodeMatakuliah = $jadwal['kode_matakuliah'] ?? '-';
        @endphp
        @if (!empty($dosenTersaring))
        <div class="col-md-4 jadwal-card-item" data-nama="{{ $namaMatakuliah }}" data-dosen="{{ implode(', ', array_column($jadwal['dosen'], 'nama')) }}" data-jenis="{{ $jadwal['jenis_kelas'] }}">
            <div class="card shadow-sm mb-3 bg-light text-dark" style="cursor: pointer;"
                onclick="openAbsensiPraktikModal('{{ $jadwalId }}', '{{ $namaMatakuliah }}', '{{ $kodeMatakuliah }}')">
                <div class="card-body">
                    <h6 class="card-title fw-bold text-dark border-start border-3 ps-2">
                        {{ $namaMatakuliah }}
                        <span class="badge bg-warning ms-2">{{ $kodeMatakuliah }}</span>
                    </h6>
                    <p class="text-dark-50 mb-2">
                        <i class="bi bi-clock"></i> {{ $jadwal['jam_mulai'] }} - {{ $jadwal['jam_selesai'] }}
                    </p>
                    <p class="mb-1"><i class="bi bi-geo-alt"></i> Ruangan:
                        <span class="fw-bold">{{ $jadwal['ruangan'] }}</span>
                    </p>
                    <p class="mb-1"><i class="bi bi-person-badge"></i> Dosen:</p>
                    <ul class="list-unstyled">
                        @if (count($jadwal['dosen']) > 0)
                        @foreach ($jadwal['dosen'] as $dosen)
                        <li>
                            <i class="bi bi-dot"></i> {{ $dosen['nama'] }}
                            <span class="badge bg-warning text-white ms-2 text-capitalize">
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
        @endif {{-- End if $dosenTersaring tidak kosong --}}
        @endforeach
    </div>
</div>
@endforeach
<!-- 🔹 MODAL FORM UNTUK BUAT PERTEMUAN -->
<div class="modal fade" id="pertemuanPrModal" tabindex="-1" aria-labelledby="pertemuanPrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-light pb-3 border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="pertemuanPrModalLabel">
                    <i class="bx bx-calendar-plus text-primary fs-4 align-middle me-1"></i> Buat Pertemuan Baru Praktik
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form id="pertemuanprakForm">
                    @csrf
                    <input type="hidden" id="jadwal_praktik_id" name="jadwal_praktik_id">

                    <div class="mb-4">
                        <label for="nama_matakuliah" class="form-label fw-semibold text-muted mb-1">Mata Kuliah Praktik</label>
                        <input type="text" class="form-control bg-light border-0 fw-bold text-primary" id="nama_matakuliah" name="nama_matakuliah" readonly>
                    </div>

                    <div class="mb-4">
                        <label for="tanggal_pertemuan" class="form-label fw-semibold">Tanggal Pertemuan</label>
                        <input type="date" class="form-control" id="tanggal_pertemuan" name="tanggal_pertemuan" required>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6 mb-3">
                            <label for="jam_mulai" class="form-label fw-semibold">Jam Mulai</label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                            <p id="format-jam-mulai" class="text-muted small mt-1 mb-0"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jam_selesai" class="form-label fw-semibold">Jam Selesai</label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                            <p id="format-jam-selesai" class="text-muted small mt-1 mb-0"></p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="metode_pbm_praktik" class="form-label fw-semibold">Metode PBM</label>
                        <select class="form-select" id="metode_pbm_praktik" name="metode_pbm" required>
                            <option value="offline" selected>Offline / Tatap Muka</option>
                            <option value="online">Online / Daring</option>
                        </select>
                        <small class="text-muted">Pilih metode pelaksanaan pertemuan praktik ini.</small>
                    </div>

                    <div class="mb-4">
                        <label for="topik" class="form-label fw-semibold">Topik Pertemuan</label>
                        <textarea class="form-control" id="topik" name="topik" rows="2" placeholder="Tuliskan bahasan eksperimen/praktikum utama..." required></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="sub_topik" class="form-label fw-semibold">Sub Topik</label>
                        <textarea class="form-control" id="sub_topik" name="sub_topik" rows="2" placeholder="Rincian sub topik materi praktik..." required></textarea>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm" style="letter-spacing: 0.5px;">
                            <i class="bx bxs-save me-2"></i> Simpan Pertemuan
                        </button>
                    </div>
                </form>

                <!-- 🔹 List Pertemuan -->
                <div class="mt-5">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bx bx-list-ul text-primary fs-5 align-middle me-1"></i> Daftar Riwayat Pertemuan
                    </h6>
                    <ul id="absensiList" class="list-group list-group-flush border rounded-3 bg-light">
                        <li class="list-group-item bg-transparent text-center text-muted py-4">Memuat data histori pertemuan praktik...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
