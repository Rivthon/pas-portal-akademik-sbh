@extends('layouts.dosen')

@section('title', 'Daftar Mahasiswa Bimbingan Akademik')

@section('content')
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">
        {{-- Hero Card --}}
        <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col-md-7 text-white p-3">
                        <h4 class="card-title mb-3 fw-bold text-white"><i class="bx bx-group me-2"></i>Mahasiswa Bimbingan Akademik</h4>
                        <p class="mb-0 text-white-50" style="line-height: 1.6;">
                            Berikut adalah daftar mahasiswa yang berada di bawah bimbingan akademik (PA) Anda.<br/>
                            Anda dapat memantau profil, melihat detail nilai, dan transkrip akademik mereka.
                        </p>
                    </div>
                    <div class="col-md-5 text-center d-none d-md-block">
                        <img src="{{ asset('assets/img/illustrations/undraw_studying_re_deca.svg') }}" class="img-fluid" onerror="this.src='{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}'" alt="Illustration" style="max-height: 150px; opacity: 0.9;">
                    </div>
                </div>
            </div>
        </div>

        @foreach(['success' => 'success', 'info' => 'info', 'error' => 'danger'] as $sessionKey => $alertType)
            @if(session($sessionKey))
                <div class="alert alert-{{ $alertType }} alert-dismissible shadow-sm border-0" role="alert">
                    <i class="bx {{ $sessionKey === 'success' ? 'bx-check-circle' : ($sessionKey === 'error' ? 'bx-error-circle' : 'bx-info-circle') }} me-2"></i>
                    {{ session($sessionKey) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach

        @if($errors->any())
            <div class="alert alert-danger shadow-sm border-0"><i class="bx bx-error-circle me-2"></i>{{ $errors->first() }}</div>
        @endif

        <form id="bulkAccKrsForm" method="POST" action="{{ route('dosen.mahasiswa.krs.bulk-approve') }}">
            @csrf
        </form>

        {{-- Table Card --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white pt-4 pb-3 border-bottom">
                <div>
                    <h5 class="mb-1 fw-bold text-dark"><i class="bx bx-list-ul text-primary me-2"></i>Daftar Anak Didik</h5>
                    <small class="text-muted">ACC KRS Tahun Akademik {{ $activeTA->nama ?? '-' }}</small>
                </div>

                <form action="{{ route('dosen.nilai-dosen.lihat') }}" method="GET" class="mt-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label for="filter-search" class="form-label small fw-semibold mb-1">Cari Mahasiswa</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input id="filter-search" type="text" name="search" class="form-control"
                                    placeholder="Nama atau NIM..." value="{{ $search }}">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label for="filter-sort" class="form-label small fw-semibold mb-1">Urutkan</label>
                            <select id="filter-sort" name="sort" class="form-select">
                                <option value="nama_asc" @selected($sort === 'nama_asc')>Nama A - Z</option>
                                <option value="nama_desc" @selected($sort === 'nama_desc')>Nama Z - A</option>
                                <option value="semester_asc" @selected($sort === 'semester_asc')>Semester Terendah</option>
                                <option value="semester_desc" @selected($sort === 'semester_desc')>Semester Tertinggi</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label for="filter-semester" class="form-label small fw-semibold mb-1">Semester</label>
                            <select id="filter-semester" name="semester" class="form-select">
                                <option value="">Semua Semester</option>
                                @foreach(range(1, 14) as $semester)
                                    <option value="{{ $semester }}" @selected($semesterFilter === $semester)>
                                        Semester {{ $semester }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label for="filter-status-krs" class="form-label small fw-semibold mb-1">Status KRS</label>
                            <select id="filter-status-krs" name="status_krs" class="form-select">
                                <option value="semua" @selected($statusKrs === 'semua')>Semua Status</option>
                                <option value="belum" @selected($statusKrs === 'belum')>Belum Diambil</option>
                                <option value="menunggu" @selected($statusKrs === 'menunggu')>Menunggu ACC</option>
                                <option value="disetujui" @selected($statusKrs === 'disetujui')>Disetujui</option>
                            </select>
                        </div>
                        <div class="col-6 col-lg-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bx bx-filter-alt me-1"></i>Terapkan
                            </button>
                            @if($search || $sort !== 'nama_asc' || $semesterFilter || $statusKrs !== 'semua')
                                <a href="{{ route('dosen.nilai-dosen.lihat') }}" class="btn btn-outline-secondary" title="Reset filter">
                                    <i class="bx bx-reset"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body py-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div class="small text-muted">
                    <i class="bx bx-info-circle me-1"></i>Centang mahasiswa berstatus menunggu untuk melakukan persetujuan sekaligus.
                </div>
                <button type="submit" form="bulkAccKrsForm" id="bulkAccButton" class="btn btn-success rounded-pill px-4" disabled
                    onclick="return confirm('Setujui seluruh KRS mahasiswa yang dipilih?')">
                    <i class="bx bx-check-double me-1"></i>Bulk ACC (<span id="selectedKrsCount">0</span>)
                </button>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 45px;">
                                <input type="checkbox" class="form-check-input" id="checkAllKrs" title="Pilih semua KRS yang menunggu">
                            </th>
                            <th class="text-center" style="width: 60px;">#</th>
                            <th style="min-width: 250px;">BIMBINGAN / INFO</th>
                            <th class="text-center">PRODI & SMT</th>
                            <th class="text-center">KONTAK (HP)</th>
                            <th class="text-center">STATUS</th>
                            <th class="text-center">STATUS KRS</th>
                            <th class="text-center" style="width: 90px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($mahasiswaList as $index => $mahasiswa)
                            @php
                                $krsAktif = $mahasiswa->krs;
                                $totalSks = $krsAktif->sum(fn ($krs) => (int) ($krs->kurikulum?->mataKuliah?->sks ?? 0));
                                $sksKurikulum = $mahasiswa->sks_kurikulum;
                                $selisihSksKurikulum = $mahasiswa->selisih_sks_kurikulum;
                                $sudahDisetujui = $krsAktif->isNotEmpty()
                                    && $krsAktif->every(fn ($krs) => $krs->disetujui_pada !== null);
                                $menungguAcc = $krsAktif->isNotEmpty() && ! $sudahDisetujui;
                            @endphp
                            <tr>
                                <td class="text-center">
                                    @if($menungguAcc)
                                        <input type="checkbox" class="form-check-input krs-bulk-checkbox"
                                            name="mahasiswa_ids[]" value="{{ $mahasiswa->mahasiswa_id }}"
                                            form="bulkAccKrsForm" aria-label="Pilih {{ $mahasiswa->nama }}">
                                    @else
                                        <input type="checkbox" class="form-check-input" disabled>
                                    @endif
                                </td>
                                <td class="text-center">{{ $mahasiswaList->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md me-3">
                                            @if($mahasiswa->avatar)
                                                <img src="{{ asset('storage/' . $mahasiswa->avatar) }}" alt="Avatar" class="rounded-circle object-fit-cover" style="width: 40px; height: 40px;">
                                            @else
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($mahasiswa->nama) }}&background=696cff&color=fff" alt="Avatar" class="rounded-circle">
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $mahasiswa->nama }}</h6>
                                            <span class="text-muted d-block" style="font-size: 0.8rem;"><i class="bx bx-id-card me-1"></i>{{ $mahasiswa->nim }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="d-block fw-semibold text-primary" style="font-size: 0.85rem;">{{ $mahasiswa->programStudi->nama ?? '-' }}</span>
                                    <span class="badge bg-label-info mt-1">Semester {{ $mahasiswa->semester }}</span>
                                </td>
                                <td class="text-center text-muted">
                                    <span style="font-size: 0.85rem;"><i class="bx bx-phone me-1"></i>{{ $mahasiswa->no_telp ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if(strtolower($mahasiswa->status_mhs) == 'aktif')
                                        <span class="badge bg-success shadow-sm">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary shadow-sm">{{ ucfirst($mahasiswa->status_mhs) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($krsAktif->isEmpty())
                                        <span class="badge bg-label-secondary">
                                            <i class="bx bx-minus-circle me-1"></i>Belum Diambil
                                        </span>
                                    @elseif($sudahDisetujui)
                                        <span class="badge bg-label-success">
                                            <i class="bx bx-check-shield me-1"></i>Disetujui
                                        </span>
                                        <small class="d-block text-muted mt-1">{{ $krsAktif->count() }} MK • {{ $totalSks }} SKS</small>
                                    @else
                                        <span class="badge bg-label-warning">
                                            <i class="bx bx-hourglass me-1"></i>Menunggu ACC
                                        </span>
                                        <small class="d-block text-muted mt-1">{{ $krsAktif->count() }} MK • {{ $totalSks }} SKS</small>
                                    @endif
                                    @if ($krsAktif->isNotEmpty() && $selisihSksKurikulum > 0)
                                        <span class="badge bg-label-danger d-inline-flex align-items-center mt-1"
                                            title="Acuan kurikulum: {{ $sksKurikulum }} SKS">
                                            <i class="bx bx-error-circle me-1"></i>Kurang {{ $selisihSksKurikulum }} SKS
                                        </span>
                                    @elseif ($krsAktif->isNotEmpty() && $selisihSksKurikulum < 0)
                                        <span class="badge bg-label-warning d-inline-flex align-items-center mt-1"
                                            title="Acuan kurikulum: {{ $sksKurikulum }} SKS">
                                            <i class="bx bx-info-circle me-1"></i>Lebih {{ abs($selisihSksKurikulum) }} SKS
                                        </span>
                                    @elseif ($krsAktif->isNotEmpty() && $sksKurikulum === null)
                                        <small class="d-block text-muted mt-1">Acuan SKS kurikulum belum tersedia</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="dropdown position-static">
                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded me-1"></i>Aksi
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('dosen.mahasiswa.krs.show', $mahasiswa) }}">
                                                    <i class="bx bx-detail text-info me-2"></i>Lihat KRS
                                                </a>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#diskusiKrs{{ $mahasiswa->mahasiswa_id }}">
                                                    <i class="bx bx-conversation text-primary me-2"></i>Diskusi KRS
                                                    @if($mahasiswa->guidanceMessages->isNotEmpty())
                                                        <span class="badge bg-primary ms-1">{{ $mahasiswa->guidanceMessages->count() }}</span>
                                                    @endif
                                                </button>
                                            </li>
                                            @if($menungguAcc)
                                                <li>
                                                    <form method="POST" action="{{ route('dosen.mahasiswa.krs.approve', $mahasiswa) }}"
                                                        onsubmit="return confirm('Setujui {{ $krsAktif->count() }} mata kuliah KRS {{ addslashes($mahasiswa->nama) }}?')">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success">
                                                            <i class="bx bx-check-shield me-2"></i>ACC KRS
                                                        </button>
                                                    </form>
                                                </li>
                                            @elseif($sudahDisetujui)
                                                <li>
                                                    <form method="POST" action="{{ route('dosen.mahasiswa.krs.cancel-approval', $mahasiswa) }}"
                                                        onsubmit="return confirm('Batalkan ACC KRS {{ addslashes($mahasiswa->nama) }}? Mahasiswa akan dapat mengubah KRS kembali.')">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bx bx-undo me-2"></i>Batalkan ACC
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('dosen.mahasiswa.transkrip', ['mahasiswa' => $mahasiswa->mahasiswa_id]) }}">
                                                    <i class="bx bx-show text-primary me-2"></i>Transkrip
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    @if(isset($search) && $search != '')
                                        <div class="text-muted mb-2"><i class="bx bx-search fs-1"></i></div>
                                        <h6 class="fw-semibold">Tidak ditemukan</h6>
                                        <p class="text-muted mb-0">Mahasiswa bimbingan dengan kata kunci "{{ $search }}" tidak ditemukan.</p>
                                    @else
                                        <div class="text-muted mb-2"><i class="bx bx-group fs-1"></i></div>
                                        <h6 class="fw-semibold">Belum Ada Bimbingan</h6>
                                        <p class="text-muted mb-0">Anda saat ini belum ditugaskan sebagai dosen Pembimbing Akademik (PA) untuk mahasiswa manapun.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top border-0 pt-4 pb-2">
                <div class="d-flex justify-content-center">
                    {{ $mahasiswaList->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($mahasiswaList as $guidedStudent)
    <div class="modal fade" id="diskusiKrs{{ $guidedStudent->mahasiswa_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <div><h5 class="modal-title">Diskusi KRS</h5><small class="text-muted">{{ $guidedStudent->nama }} &bull; {{ $guidedStudent->nim }}</small></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <x-krs-guidance-thread :messages="$guidedStudent->guidanceMessages"
                        :action="route('dosen.mahasiswa.guidance.store', $guidedStudent)" viewer="dosen"
                        title="Riwayat Percakapan" submit-label="Kirim Komentar" />
                </div>
            </div>
        </div>
    </div>
@endforeach

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('checkAllKrs');
        const checkboxes = Array.from(document.querySelectorAll('.krs-bulk-checkbox'));
        const bulkButton = document.getElementById('bulkAccButton');
        const selectedCount = document.getElementById('selectedKrsCount');

        function updateBulkState() {
            const checked = checkboxes.filter(checkbox => checkbox.checked).length;
            selectedCount.textContent = checked;
            bulkButton.disabled = checked === 0;
            checkAll.checked = checkboxes.length > 0 && checked === checkboxes.length;
            checkAll.indeterminate = checked > 0 && checked < checkboxes.length;
        }

        checkAll?.addEventListener('change', function () {
            checkboxes.forEach(checkbox => checkbox.checked = checkAll.checked);
            updateBulkState();
        });
        checkboxes.forEach(checkbox => checkbox.addEventListener('change', updateBulkState));
        updateBulkState();
    });
</script>
@endpush
@endsection
