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
        @endphp
        @if (!empty($dosenTersaring))
        {{-- Hanya tampilkan card jika ada dosen yang cocok --}}
        @php
            $dosenNames = collect($dosenTersaring)->pluck('nama')->implode(', ');
        @endphp
        <div class="col-md-4 jadwal-card-item mb-3" data-nama="{{ $jadwal['nama_matakuliah'] }}" data-dosen="{{ $dosenNames }}" data-jenis="{{ $jadwal['jenis_kelas'] }}">
            <div class="card shadow-sm h-100 bg-white border-0 hover-scale" style="cursor: pointer; transition: transform 0.2s;"
                onclick="openPertemuanModal('{{ $jadwal['jadwal_id'] }}', '{{ $jadwal['nama_matakuliah'] }}', '{{ $jadwal['kode_matakuliah'] ?? '-' }}')">
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title fw-bold text-primary border-start border-primary border-3 ps-2 mb-1">
                        {{ $jadwal['nama_matakuliah'] }}
                    </h6>
                    <div class="mb-2 mt-1">
                        <span class="badge bg-label-primary rounded-pill">{{ $jadwal['kode_matakuliah'] ?? '-' }}</span>
                        <span class="badge bg-label-info rounded-pill ms-1">SMT {{ $jadwal['semester_matkul'] ?? '-' }}</span>
                        <span class="badge bg-label-secondary rounded-pill ms-1"><i class="bx bx-buildings me-1"></i>{{ $jadwal['program_studi'] ?? '-' }}</span>
                    </div>
                    <hr class="mt-1 mb-2">
                    <p class="text-secondary mb-2">
                        <i class="bx bx-time text-primary"></i> <strong>{{ $jadwal['jam_mulai'] }} - {{ $jadwal['jam_selesai'] }}</strong>
                    </p>
                    <p class="mb-1"><i class="bx bx-buildings text-primary"></i> Ruangan:
                        <span class="fw-bold">{{ $jadwal['ruangan'] }}</span>
                    </p>
                    <p class="mb-1"><i class="bx bx-user text-primary"></i> Dosen:</p>
                    <ul class="list-unstyled ms-3 mb-auto">
                        @foreach ($dosenTersaring as $dosen)
                        <li>
                            <i class="bx bx-radio-circle text-primary"></i> {{ $dosen['nama'] }}
                        </li>
                        @endforeach
                    </ul>

                    @if (strtolower($jadwal['jenis_kelas']) == 'karyawan')
                    <div class="alert alert-danger mt-3 py-2 px-3 mb-0" role="alert" style="border-left: 4px solid #dc3545; background-color: #fcf1f1;">
                        <i class="bx bx-briefcase-alt-2 fs-5 me-1 align-middle text-danger"></i> <span class="align-middle fw-semibold text-danger">Kelas Reguler B</span>
                    </div>
                    @else
                    <div class="alert alert-info mt-3 py-2 px-3 mb-0" role="alert" style="border-left: 4px solid #0dcaf0; background-color: #f1fcfc;">
                        <i class="bx bx-sun fs-5 me-1 align-middle text-info"></i> <span class="align-middle fw-semibold text-info">Kelas Reguler A</span>
                    </div>
                    @endif
                </div>
            </div>
            <style>
                .hover-scale:hover {
                    transform: translateY(-5px) !important;
                    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
                }
            </style>
        </div>
        @endif {{-- End if $dosenTersaring tidak kosong --}}
        @endforeach
    </div>
</div>
@endforeach
<!-- 🔹 MODAL FORM UNTUK BUAT PERTEMUAN -->
<div class="modal fade" id="pertemuanModal" tabindex="-1" aria-labelledby="pertemuanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-light pb-3 border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="pertemuanPrModalLabel">
                    <i class="bx bx-calendar-plus text-primary fs-4 align-middle me-1"></i> Buat Pertemuan Baru Teori
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form id="pertemuanForm">
                    @csrf
                    <input type="hidden" id="jadwal_id" name="jadwal_id">

                    <div class="mb-4">
                        <label for="nama_matakuliah" class="form-label fw-semibold text-muted mb-1">Mata Kuliah</label>
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
                        <label for="metode_pbm" class="form-label fw-semibold">Metode PBM</label>
                        <select class="form-select" id="metode_pbm" name="metode_pbm" required>
                            <option value="offline" selected>Offline / Tatap Muka</option>
                            <option value="online">Online / Daring</option>
                        </select>
                        <small class="text-muted">Pilih metode pelaksanaan pertemuan ini.</small>
                    </div>

                    <div class="mb-4">
                        <label for="topik" class="form-label fw-semibold">Topik Pertemuan</label>
                        <textarea class="form-control" id="topik" name="topik" rows="2" placeholder="Tuliskan bahasan utama..." required></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="sub_topik" class="form-label fw-semibold">Sub Topik</label>
                        <textarea class="form-control" id="sub_topik" name="sub_topik" rows="2" placeholder="Rincian sub topik materi..." required></textarea>
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
                    <ul id="pertemuanList" class="list-group list-group-flush border rounded-3 bg-light">
                        <li class="list-group-item bg-transparent text-center text-muted py-4">Memuat data histori pertemuan...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
