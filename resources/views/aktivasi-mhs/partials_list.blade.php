<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Status KRS</th>
                                <th>Jadwal UTS</th>
                                <th>Jadwal UAS</th>
                                <th>Nilai UTS</th>
                                <th>Nilai UAS</th>
                                {{-- <th>KHS</th> --}}
                                <th>UAP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mahasiswa as $m)
                            <tr>
                                <!-- NIM -->
                                <td>{{ $m->nim }}</td>
                                <!-- Nama Mahasiswa -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            <img src="{{ $m->avatar_url ?? asset('dashboard_assets/assets/img/avatars/1.png') }}"
                                                alt="Avatar" class="rounded-circle"
                                                style="width: 40px; height: 40px; object-fit: cover;"
                                                onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'" />
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $m->nama }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <!-- Status KRS -->
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-status" type="checkbox" data-type="krs"
                                            data-id="{{ $m->mahasiswa_id }}" @checked($m->status_krs)>
                                    </div>
                                </td>
                                <!-- Status UTS -->
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-status" type="checkbox" data-type="uts"
                                            data-id="{{ $m->mahasiswa_id }}" @checked($m->status_uts)>
                                    </div>
                                </td>
                                <!-- Status UAS -->
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-status" type="checkbox" data-type="uas"
                                            data-id="{{ $m->mahasiswa_id }}" @checked($m->status_uas)>
                                    </div>
                                </td>
                                <!-- Status Nilai UTS -->
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-status" type="checkbox"
                                            data-type="nilai_uts" data-id="{{ $m->mahasiswa_id }}"
                                            @checked($m->status_nilai_uts)>
                                    </div>
                                </td>
                                <!-- Status Nilai UAS -->
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-status" type="checkbox"
                                            data-type="nilai_uas" data-id="{{ $m->mahasiswa_id }}"
                                            @checked($m->status_nilai_uas)>
                                    </div>
                                </td>
                                <!-- Status KHS -->
                                {{-- <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-status" type="checkbox" data-type="khs"
                                            data-id="{{ $m->mahasiswa_id }}" @checked($m->status_nilai_khs)>
                                    </div>
                                </td> --}}
                                <!-- Status UAP -->
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-status" type="checkbox" data-type="uap"
                                            data-id="{{ $m->mahasiswa_id }}" @checked($m->status_uap)>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($mahasiswa->hasPages())
            <div class="d-flex justify-content-center mt-3 pagination-links">
                {{ $mahasiswa->links('pagination::bootstrap-4') }}
            </div>
            @endif
        </div>
    </div>
</div>
</div>