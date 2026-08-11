@extends('layouts.dosen')

@section('content')
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">

        <!-- Tombol Kembali -->
        <div class="mb-3">
            <a href="{{ url()->previous() }}" class="btn btn-outline-primary rounded-pill btn-sm shadow-sm px-3">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <!-- Alert Notifikasi -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible shadow-sm border-0 mb-3" role="alert">
                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible shadow-sm border-0 mb-3" role="alert">
                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible shadow-sm border-0 mb-3" role="alert">
                <strong class="d-block mb-1"><i class="bx bx-error me-1"></i>Data gagal disimpan:</strong>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Banner Header Tugas -->
        <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body p-4 text-white">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-2 fw-semibold">
                            <i class="bx bx-task me-1"></i>Pengumpulan Tugas
                        </span>
                        <h3 class="text-white fw-bold mt-1 mb-2">{{ $tugas->judul }}</h3>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <span class="badge bg-white text-primary rounded-pill px-3 py-2">
                                <i class="bx bx-time-five me-1 text-danger"></i>Deadline: {{ $tugas->deadline->format('d M Y H:i') }}
                            </span>
                            <span class="badge bg-white text-primary rounded-pill px-3 py-2">
                                <i class="bx bx-star me-1 text-warning"></i>Nilai Maksimal: {{ $tugas->nilai_maksimal }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3 text-center d-none d-md-block">
                        <i class="bx bx-group text-white" style="font-size: 6.5rem; opacity: 0.25;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Statistik -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 border-start border-4 border-primary bg-white shadow-sm h-100">
                    <div class="card-body py-3">
                        <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem;">Total Peserta</small>
                        <h3 class="mb-0 fw-bold text-dark">{{ $totalPeserta }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 border-start border-4 border-success bg-white shadow-sm h-100">
                    <div class="card-body py-3">
                        <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem;">Sudah Mengumpulkan</small>
                        <h3 class="mb-0 fw-bold text-success">{{ $totalMengumpulkan }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 border-start border-4 border-danger bg-white shadow-sm h-100">
                    <div class="card-body py-3">
                        <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem;">Belum Mengumpulkan</small>
                        <h3 class="mb-0 fw-bold text-danger">{{ $totalBelumMengumpulkan }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 border-start border-4 border-info bg-white shadow-sm h-100">
                    <div class="card-body py-3">
                        <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem;">Sudah Dinilai</small>
                        <h3 class="mb-0 fw-bold text-info">{{ $totalDinilai }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Utama Tabel -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="bx bx-list-check text-primary me-2"></i>Daftar Pengumpulan Mahasiswa</h5>
                <span class="badge bg-label-primary rounded-pill px-3">{{ count($daftarPeserta) }} Mahasiswa</span>
            </div>

            <div class="card-body p-0">
                <!-- Tabel Daftar Peserta -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light border-bottom">
                            <tr class="text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                                <th class="text-center py-3" style="width: 50px">No</th>
                                <th class="py-3" style="width: 130px">NIM</th>
                                <th class="py-3">Nama Mahasiswa</th>
                                <th class="py-3" style="width: 170px">Status</th>
                                <th class="py-3" style="width: 170px">Waktu Upload</th>
                                <th class="text-center py-3" style="width: 120px">File</th>
                                <th class="text-center py-3" style="width: 120px">Nilai</th>
                                <th class="text-center py-3" style="width: 130px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse($daftarPeserta as $data)
                                @php
                                    $mahasiswa = $data->mahasiswa;
                                    $pengumpulan = $data->pengumpulan;
                                    $terlambat = $pengumpulan
                                        ? $pengumpulan->waktu_upload->greaterThan($tugas->deadline)
                                        : false;
                                    $namaMahasiswa = $mahasiswa->nama_mahasiswa ?? $mahasiswa->nama ?? '-';
                                    $nimMahasiswa = $mahasiswa->nim ?? $mahasiswa->npm ?? '-';
                                    $mhsId = $mahasiswa->mahasiswa_id ?? $mahasiswa->id ?? $loop->iteration;
                                @endphp

                                <tr class="border-bottom">
                                    <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                    <td><span class="badge bg-label-secondary font-monospace">{{ $nimMahasiswa }}</span></td>
                                    <td class="fw-semibold text-dark">{{ $namaMahasiswa }}</td>
                                    <td>
                                        @if(!$pengumpulan)
                                            <span class="badge bg-label-danger rounded-pill px-2 py-1">Belum Mengumpulkan</span>
                                        @elseif($terlambat)
                                            <span class="badge bg-label-warning rounded-pill px-2 py-1"><i class="bx bx-error me-1"></i> Terlambat</span>
                                        @else
                                            <span class="badge bg-label-success rounded-pill px-2 py-1"><i class="bx bx-check me-1"></i> Tepat Waktu</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        @if($pengumpulan)
                                            <i class="bx bx-time-five me-1"></i>{{ $pengumpulan->waktu_upload->format('d M Y H:i') }}
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($pengumpulan)
                                            <a href="{{ route('dosen.lms.pengumpulan.preview', $pengumpulan->pengumpulan_id) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bx bx-show me-1"></i> Lihat
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($pengumpulan && !is_null($pengumpulan->nilai))
                                            <span class="badge bg-primary rounded-pill px-3 py-1 fs-6 fw-bold">
                                                {{ $pengumpulan->nilai }} / {{ $tugas->nilai_maksimal }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($pengumpulan)
                                            <button type="button"
                                                    class="btn btn-primary btn-sm rounded-pill shadow-sm px-3"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalNilai{{ $mhsId }}">
                                                <i class="bx bx-edit me-1"></i>
                                                {{ is_null($pengumpulan->nilai) ? 'Nilai' : 'Edit' }}
                                            </button>
                                        @else
                                            <button class="btn btn-light text-muted border btn-sm rounded-pill px-3" disabled>
                                                Belum Upload
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bx bx-user-x fs-1 d-block mb-2"></i>
                                        Peserta mata kuliah belum ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Render Modal untuk Peserta yang Memiliki Pengumpulan -->
@foreach($daftarPeserta as $data)
    @php
        $pengumpulan = $data->pengumpulan;
        $mahasiswa = $data->mahasiswa;
        $namaMahasiswa = $mahasiswa->nama_mahasiswa ?? $mahasiswa->nama ?? '-';
        $nimMahasiswa = $mahasiswa->nim ?? $mahasiswa->npm ?? '-';
        $mhsId = $mahasiswa->mahasiswa_id ?? $mahasiswa->id ?? $loop->iteration;
    @endphp

    @if($pengumpulan)
        <div class="modal fade" id="modalNilai{{ $mhsId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <form action="{{ route('dosen.lms.pengumpulan.nilai', $pengumpulan->pengumpulan_id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="modal-header border-bottom" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                            <h5 class="modal-title fw-bold text-white">
                                <i class="bx bx-edit text-white me-2"></i>Penilaian Tugas Mahasiswa
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body bg-light">
                            <!-- Informasi Mahasiswa -->
                            <div class="p-3 bg-white border rounded-3 shadow-sm mb-3">
                                <h6 class="mb-1 fw-bold text-dark">{{ $namaMahasiswa }}</h6>
                                <span class="badge bg-label-secondary font-monospace">NIM: {{ $nimMahasiswa }}</span>
                            </div>

                            <!-- Unduh File -->
                            <div class="mb-3">
                                <a href="{{ route('dosen.lms.pengumpulan.download', $pengumpulan->pengumpulan_id) }}"
                                   class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                                    <i class="bx bx-download me-1"></i> Download File Jawaban
                                </a>
                            </div>

                            <!-- Preview PDF (Komentar Tetap Dipertahankan) -->
                            <!-- @if(\Illuminate\Support\Str::endsWith(strtolower($pengumpulan->file), '.pdf'))
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">Preview Document (PDF)</label>
                                    <iframe src="{{ route('dosen.lms.pengumpulan.preview', $pengumpulan->pengumpulan_id) }}"
                                            width="100%"
                                            height="400"
                                            class="rounded border bg-white shadow-sm"></iframe>
                                </div>
                            @endif -->

                            <!-- Catatan Mahasiswa -->
                            @if($pengumpulan->catatan)
                                <div class="alert alert-warning border-0 shadow-sm text-dark mb-3 rounded-3">
                                    <strong class="d-block mb-1 text-dark"><i class="bx bx-comment-detail me-1"></i> Catatan Mahasiswa:</strong>
                                    <div class="text-body">{!! nl2br(e($pengumpulan->catatan)) !!}</div>
                                </div>
                            @endif

                            <!-- Input Nilai -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Nilai <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number"
                                           name="nilai"
                                           value="{{ $pengumpulan->nilai }}"
                                           min="0"
                                           max="{{ $tugas->nilai_maksimal }}"
                                           step="0.01"
                                           class="form-control bg-white fw-bold text-dark fs-5 rounded-start-3"
                                           required>
                                    <span class="input-group-text bg-secondary text-white fw-bold rounded-end-3">/ {{ $tugas->nilai_maksimal }}</span>
                                </div>
                            </div>

                            <!-- Input Feedback -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Feedback / Catatan Dosen</label>
                                <textarea name="feedback"
                                          class="form-control bg-white text-dark rounded-3"
                                          rows="4"
                                          placeholder="Tuliskan feedback atau arahan perbaikan untuk mahasiswa">{{ $pengumpulan->feedback }}</textarea>
                            </div>

                            <!-- Info Waktu Penilaian -->
                            @if($pengumpulan->dinilai_pada)
                                <div class="text-muted small">
                                    <i class="bx bx-time-five me-1"></i> Terakhir dinilai: {{ \Carbon\Carbon::parse($pengumpulan->dinilai_pada)->format('d M Y H:i') }}
                                </div>
                            @endif
                        </div>

                        <div class="modal-footer bg-white border-top">
                            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="bx bx-save me-1"></i> Simpan Nilai
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection