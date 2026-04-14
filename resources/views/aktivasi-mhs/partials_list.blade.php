<div class="table-responsive">
    <table class="table table-modern mb-0">
        <thead>
            <tr>
                <th><i class="bx bx-hash me-1"></i>NIM</th>
                <th><i class="bx bx-user me-1"></i>Mahasiswa</th>
                <th><i class="bx bx-buildings me-1"></i>Prodi</th>
                <th><i class="bx bx-check-double me-1"></i>KRS</th>
                <th><i class="bx bx-calendar me-1"></i>Jdw UTS</th>
                <th><i class="bx bx-calendar-check me-1"></i>Jdw UAS</th>
                <th><i class="bx bx-bar-chart-alt-2 me-1"></i>Nilai UTS</th>
                <th><i class="bx bx-bar-chart me-1"></i>Nilai UAS</th>
                <th><i class="bx bx-medal me-1"></i>UAP</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mahasiswa as $m)
            <tr>
                {{-- NIM --}}
                <td>
                    <span class="fw-semibold" style="font-size: .82rem; color: #566a7f;">{{ $m->nim }}</span>
                </td>

                {{-- Nama Mahasiswa --}}
                <td>
                    <div class="d-flex align-items-center gap-3 student-info">
                        <div class="position-relative">
                            <img src="{{ $m->avatar_url ?? asset('dashboard_assets/assets/img/avatars/1.png') }}"
                                alt="Avatar" class="student-avatar"
                                onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'">
                        </div>
                        <div>
                            <div class="student-name">{{ $m->nama }}</div>
                            <div class="student-nim">Semester {{ $m->semester }} • {{ ucfirst($m->kelas ?? '-') }}</div>
                        </div>
                    </div>
                </td>

                {{-- Program Studi --}}
                <td>
                    @php
                        $prodiName = $m->programStudi->nama ?? '-';
                        $singkat = strtolower($m->programStudi->singkat ?? '');
                        $badgeClass = 'default';
                        if (str_contains($singkat, 'farm')) $badgeClass = 'farmasi';
                        elseif (str_contains($singkat, 'gizi') || str_contains($singkat, 'gz')) $badgeClass = 'gizi';
                        elseif (str_contains($singkat, 'bid') || str_contains($singkat, 'keb')) $badgeClass = 'kebidanan';
                    @endphp
                    <span class="badge-prodi {{ $badgeClass }}">{{ $m->programStudi->singkat ?? $prodiName }}</span>
                </td>

                {{-- Status KRS --}}
                <td>
                    <div class="toggle-wrapper">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input toggle-status" type="checkbox" data-type="krs"
                                data-id="{{ $m->mahasiswa_id }}" @checked($m->status_krs)>
                        </div>
                        <span class="toggle-label {{ $m->status_krs ? 'aktif' : 'nonaktif' }}">
                            {{ $m->status_krs ? 'AKTIF' : 'NONAKTIF' }}
                        </span>
                    </div>
                </td>

                {{-- Status UTS --}}
                <td>
                    <div class="toggle-wrapper">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input toggle-status" type="checkbox" data-type="uts"
                                data-id="{{ $m->mahasiswa_id }}" @checked($m->status_uts)>
                        </div>
                        <span class="toggle-label {{ $m->status_uts ? 'aktif' : 'nonaktif' }}">
                            {{ $m->status_uts ? 'AKTIF' : 'NONAKTIF' }}
                        </span>
                    </div>
                </td>

                {{-- Status UAS --}}
                <td>
                    <div class="toggle-wrapper">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input toggle-status" type="checkbox" data-type="uas"
                                data-id="{{ $m->mahasiswa_id }}" @checked($m->status_uas)>
                        </div>
                        <span class="toggle-label {{ $m->status_uas ? 'aktif' : 'nonaktif' }}">
                            {{ $m->status_uas ? 'AKTIF' : 'NONAKTIF' }}
                        </span>
                    </div>
                </td>

                {{-- Status Nilai UTS --}}
                <td>
                    <div class="toggle-wrapper">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input toggle-status" type="checkbox" data-type="nilai_uts"
                                data-id="{{ $m->mahasiswa_id }}" @checked($m->status_nilai_uts)>
                        </div>
                        <span class="toggle-label {{ $m->status_nilai_uts ? 'aktif' : 'nonaktif' }}">
                            {{ $m->status_nilai_uts ? 'AKTIF' : 'NONAKTIF' }}
                        </span>
                    </div>
                </td>

                {{-- Status Nilai UAS --}}
                <td>
                    <div class="toggle-wrapper">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input toggle-status" type="checkbox" data-type="nilai_uas"
                                data-id="{{ $m->mahasiswa_id }}" @checked($m->status_nilai_uas)>
                        </div>
                        <span class="toggle-label {{ $m->status_nilai_uas ? 'aktif' : 'nonaktif' }}">
                            {{ $m->status_nilai_uas ? 'AKTIF' : 'NONAKTIF' }}
                        </span>
                    </div>
                </td>

                {{-- Status UAP --}}
                <td>
                    <div class="toggle-wrapper">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input toggle-status" type="checkbox" data-type="uap"
                                data-id="{{ $m->mahasiswa_id }}" @checked($m->status_uap)>
                        </div>
                        <span class="toggle-label {{ $m->status_uap ? 'aktif' : 'nonaktif' }}">
                            {{ $m->status_uap ? 'AKTIF' : 'NONAKTIF' }}
                        </span>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="d-flex flex-column align-items-center gap-2">
                        <i class="bx bx-search-alt" style="font-size: 2.5rem; color: #c2c6de;"></i>
                        <span class="text-muted fw-semibold">Tidak ada data ditemukan.</span>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if ($mahasiswa->hasPages())
<div class="pagination-modern">
    <span style="font-size: .78rem; color: #8592a3; font-weight: 500;">
        Menampilkan {{ $mahasiswa->firstItem() }} - {{ $mahasiswa->lastItem() }} dari {{ number_format($mahasiswa->total()) }} data
    </span>
    <div class="pagination-links">
        {{ $mahasiswa->links('pagination::bootstrap-4') }}
    </div>
</div>
@endif