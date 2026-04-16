@foreach ($jadwalList as $hari => $jadwalHari)
<div class="mb-4">
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
        <div class="col-md-4">
            <div class="card shadow-sm mb-3 bg-light text-dark" style="cursor: pointer;"
                onclick="openPertemuanModal('{{ $jadwal['jadwal_id'] }}', '{{ $jadwal['nama_matakuliah'] }}', '{{ $jadwal['kode_matakuliah'] ?? '-' }}')">
                <div class="card-body">
                    <h6 class="card-title fw-bold text-dark border-start border-3 ps-2">
                        {{ $jadwal['nama_matakuliah'] }}
                        <span class="badge bg-warning ms-2">{{ $jadwal['kode_matakuliah'] ?? '-' }} - SMT ({{
                            $jadwal['semester_matkul'] ?? '-' }})</span>
                    </h6>
                    <p class="text-dark-50 mb-2">
                        <i class="bi bi-clock"></i> {{ $jadwal['jam_mulai'] }} - {{ $jadwal['jam_selesai'] }}
                    </p>
                    <p class="mb-1"><i class="bi bi-geo-alt"></i> Ruangan:
                        <span class="fw-bold">{{ $jadwal['ruangan'] }}</span>
                    </p>
                    <p class="mb-1"><i class="bi bi-person-badge"></i> Dosen:</p>
                    <ul class="list-unstyled">
                        @foreach ($dosenTersaring as $dosen)
                        <li>
                            <i class="bi bi-dot"></i> {{ $dosen['nama'] }}
                            <span class="badge bg-warning text-white ms-2 text-capitalize">
                                {{ $dosen['jenis_dosen'] }}
                            </span>
                        </li>
                        @endforeach
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
<div class="modal fade" id="pertemuanModal" tabindex="-1" aria-labelledby="pertemuanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <!-- Perbesar modal menjadi 8 kolom -->
        <div class="modal-content shadow-sm border-0">
            <div class="modal-header">
                <h5 class="modal-title" id="pertemuanPrModalLabel">
                    <i class="fas fa-calendar-plus"></i> Buat Pertemuan Baru Teori
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="pertemuanForm">
                    @csrf
                    <input type="hidden" id="jadwal_id" name="jadwal_id">

                    <div class="mb-3">
                        <label for="nama_matakuliah" class="form-label">Mata Kuliah</label>
                        <input type="text" class="form-control bg-light" id="nama_matakuliah" name="nama_matakuliah"
                            readonly>
                    </div>
                    <div class=" mb-3">
                        <label for="tanggal_pertemuan" class="form-label">Tanggal Pertemuan</label>
                        <input type="date" class="form-control" id="tanggal_pertemuan" name="tanggal_pertemuan"
                            required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jam_mulai" class="form-label">Jam Mulai</label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                            <p id="format-jam-mulai" class="text-muted small mt-1"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jam_selesai" class="form-label">Jam Selesai</label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                            <p id="format-jam-selesai" class="text-muted small mt-1"></p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="topik" class="form-label">Topik Pertemuan</label>
                        <textarea class="form-control" id="topik" name="topik" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="sub_topik" class="form-label">Sub Topik</label>
                        <textarea class="form-control" id="sub_topik" name="sub_topik" rows="2" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bxs-save"></i> Simpan
                    </button>
                </form>

                <!-- 🔹 List Pertemuan -->
                <hr>
                <div class="card mt-3 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-list"></i> Daftar Pertemuan</h6>
                        <ul id="pertemuanList" class="list-group">
                            <li class="list-group-item text-muted">Memuat data...</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>