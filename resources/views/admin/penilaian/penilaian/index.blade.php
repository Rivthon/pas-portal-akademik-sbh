@extends('layouts.master')
@section('title', 'Penilaian Dosen')
@section('content')

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-dark fw-bold mb-1">Evaluasi Dosen Mengajar (EDOM)</h4>
            <p class="text-muted mb-0">Analisis presisi dan laporan performa dosen untuk semester akademik berjalan.</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.reset.edom') }}" method="POST" id="resetEdomForm">
                @csrf
                <button type="button" class="btn btn-outline-danger shadow-sm" onclick="confirmResetEdom()">
                    <i class="fas fa-undo-alt me-1"></i> Reset Status Mhs
                </button>
            </form>
            <form action="{{ route('admin.setup.edom') }}" method="POST" id="setupEdomForm">
                @csrf
                <button type="button" class="btn btn-success shadow-sm" onclick="confirmSetupEdom()">
                    <i class="fas fa-check-double me-1"></i> Pulihkan Status Mhs
                </button>
            </form>
        </div>
    </div>

    <!-- Search Parameters -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-4 d-flex align-items-center text-dark">
                <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                    style="width: 32px; height: 32px;">
                    <i class="bx bxs-filter-alt"></i>
                </span>
                Search Parameters
            </h6>

            <form method="GET" action="{{ route('admin.penilaian.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Academic Year -->
                    <div class="col-md-4">
                        <label for="ta_id" class="form-label small fw-bold text-muted text-uppercase mb-1">Academic Year
                            <span class="text-danger">*</span></label>
                        <select name="ta_id" id="ta_id" class="form-select select2" required>
                            <option value="">-- Pilih --</option>
                            @foreach ($tahunAjaran as $ta)
                                <option value="{{ $ta->ta_id }}" {{ request('ta_id') == $ta->ta_id ? 'selected' : '' }}>
                                    {{ $ta->nama }} ({{ ucfirst($ta->semester) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Study Program -->
                    <div class="col-md-4">
                        <label for="jurusan_id" class="form-label small fw-bold text-muted text-uppercase mb-1">Study
                            Program <span class="text-danger">*</span></label>
                        <select name="jurusan_id" id="jurusan_id" class="form-select select2" required>
                            <option value="">-- Pilih Jurusan --</option>
                            @foreach ($programStudi as $ps)
                                <option value="{{ $ps->jurusan_id }}" {{ request('jurusan_id') == $ps->jurusan_id ? 'selected' : '' }}>
                                    {{ $ps->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Course -->
                    <div class="col-md-4">
                        <label for="kurikulum_id"
                            class="form-label small fw-bold text-muted text-uppercase mb-1">Course</label>
                        <select name="kurikulum_id" id="kurikulum_id" class="form-select select2">
                            <option value="">All Courses</option>
                            @if(isset($kurikulumList))
                                @foreach ($kurikulumList as $k)
                                    <option value="{{ $k->kurikulum_id }}" {{ request('kurikulum_id') == $k->kurikulum_id ? 'selected' : '' }}>
                                        {{ $k->mataKuliah?->nama ?? '-' }} - SMT {{ $k->mataKuliah?->smt }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Method -->
                    <div class="col-md-4">
                        <label for="jenis_dosen"
                            class="form-label small fw-bold text-muted text-uppercase mb-1">Method</label>
                        <select name="jenis_dosen" id="jenis_dosen" class="form-select select2">
                            <option value="">Any Method</option>
                            <option value="teori" {{ request('jenis_dosen') == 'teori' ? 'selected' : '' }}>Teori</option>
                            <option value="praktik" {{ request('jenis_dosen') == 'praktik' ? 'selected' : '' }}>Praktik
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Validation Info & Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <small class="text-muted d-none" id="loadingIndicator">
                            <i class="fas fa-spinner fa-spin me-1"></i> Memuat daftar kursus...
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.penilaian.index') }}" class="btn btn-light border px-4 shadow-sm">
                            <i class="fas fa-redo-alt me-1 text-muted"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fas fa-search me-1"></i> Apply Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Evaluation Registry -->
    @if(request('ta_id') && request('jurusan_id'))
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div style="width: 5px; height: 25px; background: #4e73df; border-radius: 5px; margin-right: 12px;"></div>
                    <h5 class="mb-0 fw-bold d-inline-block me-3">Evaluation Registry</h5>
                    <span class="badge bg-light text-dark px-3 py-2 border rounded-pill">{{ $assignments->count() }} Results</span>
                </div>
                <div>
                    <form action="{{ route('admin.penilaian.cetak-registry') }}" method="GET" target="_blank" class="m-0">
                        <input type="hidden" name="ta_id" value="{{ request('ta_id') }}">
                        <input type="hidden" name="jurusan_id" value="{{ request('jurusan_id') }}">
                        <input type="hidden" name="kurikulum_id" value="{{ request('kurikulum_id') }}">
                        <input type="hidden" name="jenis_dosen" value="{{ request('jenis_dosen') }}">
                        <button type="submit" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-sm fw-bold">
                            <i class="bx bx-printer me-1"></i> Print Result
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body p-0 mt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="edomTable">
                        <thead class="text-uppercase small text-muted"
                            style="background-color: #f8f9fa; letter-spacing: 0.5px;">
                            <tr>
                                <th class="ps-4 py-3 fw-bold border-bottom-0" width="5%">No</th>
                                <th class="py-3 fw-bold border-bottom-0" width="25%">Lecturer Name</th>
                                <th class="py-3 fw-bold border-bottom-0" width="25%">Course</th>
                                <th class="py-3 fw-bold border-bottom-0" width="15%">Method</th>
                                <th class="py-3 fw-bold border-bottom-0" width="15%">Status</th>
                                <th class="py-3 fw-bold border-bottom-0 pe-4" width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $index => $row)
                                <tr>
                                    <td class="ps-4 text-muted">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($row->dosen->foto ?? false)
                                                <img src="{{ Storage::url($row->dosen->foto) }}" class="rounded-circle me-3" width="40"
                                                    height="40" style="object-fit:cover;">
                                            @else
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 text-secondary"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $row->dosen->nama ?? '-' }}</h6>
                                                <small class="text-muted">NIDN: {{ $row->dosen->nidn ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <h6 class="mb-0 text-dark">{{ $row->kurikulum->mataKuliah->nama ?? '-' }}</h6>
                                        <small class="text-muted">SMT {{ $row->kurikulum->semester ?? '-' }} •
                                            {{ $row->kurikulum->mataKuliah->sks ?? '-' }} SKS</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm">
                                            {{ ucfirst($row->jenis_dosen) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($row->status_edom)
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                                <i class="fas fa-circle me-1" style="font-size: 8px;"></i> Sudah Evaluasi
                                            </span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                                <i class="fas fa-circle me-1" style="font-size: 8px;"></i> Belum Ada
                                            </span>
                                        @endif
                                    </td>
                                    <td class="pe-4">
                                        @if($row->status_edom)
                                            <a href="{{ route('admin.penilaian.detail', ['dosen_id' => $row->dosen_id, 'kurikulum_id' => $row->kurikulum_id, 'jenis_dosen' => $row->jenis_dosen, 'ta_id' => request('ta_id'), 'jurusan_id' => request('jurusan_id')]) }}"
                                                class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-bold">
                                                View Report
                                            </a>
                                        @else
                                            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm fw-bold"
                                                disabled>
                                                No Data
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-folder-open mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                                            <h5>Tidak ada data ditemukan</h5>
                                            <p>Silakan sesuaikan parameter pencarian Anda.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-search mb-3" style="font-size: 4rem; opacity: 0.2;"></i>
            <h4>Pilih Parameter Pencarian</h4>
            <p>Silakan pilih Tahun Ajaran dan Program Studi lalu klik "Apply Filter" untuk melihat data dosen.</p>
        </div>
    @endif

@endsection

@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Init Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: "-- Pilih --",
                allowClear: true
            });

            // Setup AJAX Cascade logic
            const taSelect = $('#ta_id');
            const jurusanSelect = $('#jurusan_id');
            const kurikulumSelect = $('#kurikulum_id');
            const loadingStr = $('#loadingIndicator');

            function fetchKurikulum() {
                let ta = taSelect.val();
                let jur = jurusanSelect.val();

                // Only update if both are selected, otherwise just show main list
                if (ta && jur) {
                    loadingStr.removeClass('d-none');
                    $.ajax({
                        url: '{{ route("admin.penilaian.getKurikulum") }}',
                        data: { ta_id: ta, jurusan_id: jur },
                        success: function (res) {
                            kurikulumSelect.empty().append('<option value="">All Courses</option>');
                            res.forEach(item => {
                                kurikulumSelect.append(new Option(item.nama, item.id));
                            });
                            // Trigger select2 update cautiously
                            kurikulumSelect.trigger('change.select2');
                        },
                        error: function () {
                            console.error('Failed to fetch kurikulum.');
                        },
                        complete: function () {
                            loadingStr.addClass('d-none');
                        }
                    });
                } else {
                    kurikulumSelect.empty().append('<option value="">All Courses</option>');
                    kurikulumSelect.trigger('change.select2');
                }
            }

            // Catch the select2 change event
            taSelect.on('change', function () {
                fetchKurikulum();
            });

            jurusanSelect.on('change', function () {
                fetchKurikulum();
            });

            // Optional: DataTable for styling the registry if they have a lot of items
            if ($("#edomTable").length > 0 && $("#edomTable tbody tr.d-none").length === 0) {
                $("#edomTable").DataTable({
                    "pageLength": 10,
                    "lengthChange": false,
                    "searching": true,
                    "info": true,
                    "ordering": false,
                    "language": {
                        "search": "_INPUT_",
                        "searchPlaceholder": "Search in registry..."
                    },
                    "dom": "<'row mb-3'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6 text-md-end'p>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
                });
            }
        });

        function confirmResetEdom() {
            Swal.fire({
                title: 'Konfirmasi Reset EDOM?',
                text: "Status EDOM semua mahasiswa akan direset menjadi 'Belum mengisi'.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#secondary',
                confirmButtonText: 'Ya, Reset!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('resetEdomForm').submit();
                }
            })
        }

        function confirmSetupEdom() {
            Swal.fire({
                title: 'Konfirmasi Pulihkan Status?',
                text: "Status EDOM semua mahasiswa akan dikembalikan menjadi 'Sudah mengisi' (selesai).",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#secondary',
                confirmButtonText: 'Ya, Pulihkan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('setupEdomForm').submit();
                }
            })
        }
    </script>
@endpush