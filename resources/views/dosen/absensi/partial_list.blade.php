@include('dosen.absensi.semester-cards')
<!-- 🔹 MODAL FORM UNTUK BUAT PERTEMUAN -->
<div class="modal fade" id="pertemuanModal" tabindex="-1" aria-labelledby="pertemuanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark" id="pertemuanModalLabel"><i
                        class="bx bx-calendar-plus text-primary me-2"></i>Buat Pertemuan Baru</h5>
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
                        <label for="metode_pbm" class="form-label">Metode PBM</label>
                        <select class="form-select" id="metode_pbm" name="metode_pbm" required>
                            <option value="offline" selected>Offline / Tatap Muka</option>
                            <option value="online">Online / Daring</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="topik" class="form-label">Topik Pertemuan</label>
                        <textarea class="form-control" id="topik" name="topik" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="sub_topik" class="form-label fw-semibold">Sub Topik</label>
                        <textarea class="form-control" id="sub_topik" name="sub_topik" rows="2" required
                            placeholder="Detail bahasan..."></textarea>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill"><i class="bx bx-save me-1"></i>Simpan
                            Pertemuan & Lanjut Absensi</button>
                    </div>
                </form>

                <!-- 🔹 List Pertemuan -->
                <div class="mt-5">
                    <h6 class="fw-bold text-muted border-bottom pb-2 mb-3"><i class="bx bx-list-ul me-1"></i>Riwayat
                        Pertemuan Tersimpan</h6>
                    <ul id="pertemuanList" class="list-group list-group-flush border rounded">
                        <li class="list-group-item text-muted text-center py-4 bg-light">Memuat riwayat pertemuan...
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
