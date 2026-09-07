@extends('layouts.mahasiswa')
@section('title', 'Edit KRS')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        @if(session('error'))
            <div class="alert alert-danger shadow-sm border-0">
                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger shadow-sm border-0">
                <i class="bx bx-error-circle me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <div class="card shadow-sm border-top border-5 border-warning mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge bg-label-warning mb-2">PERUBAHAN KRS</span>
                        <h4 class="fw-bold mb-1">Edit Kartu Rencana Studi</h4>
                        <p class="text-muted mb-0">
                            Tahun Akademik {{ $activeTA->nama }} &bull; Semester {{ $mahasiswa->semester }}
                        </p>
                    </div>
                    <a href="{{ route('mahasiswa.status.krs.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Kembali
                    </a>
                </div>
                <div class="alert alert-warning border-0 mt-3 mb-0">
                    <i class="bx bx-info-circle me-1"></i>
                    Perubahan hanya dapat dilakukan sebelum KRS disetujui Dosen Pembimbing.
                    Mata kuliah yang sudah diajukan telah dicentang otomatis.
                </div>
            </div>
        </div>

        <form action="{{ route('mahasiswa.krs.update') }}" method="POST" id="editKrsForm">
            @csrf
            @method('PUT')

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width: 50px;">Pilih</th>
                                    <th style="width: 60px;">No</th>
                                    <th>Nama Mata Kuliah</th>
                                    <th>Kode</th>
                                    <th>SKS</th>
                                    <th>Kategori</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $kelompokKrs = [
                                        [
                                            'judul' => 'Mata Kuliah Wajib',
                                            'warna' => 'primary',
                                            'items' => $kurikulum->filter(fn ($item) => (int) ($item->mataKuliah?->kategori_mk ?? -1) === 0),
                                        ],
                                        [
                                            'judul' => 'Mata Kuliah Pilihan',
                                            'warna' => 'success',
                                            'items' => $kurikulum->filter(fn ($item) => (int) ($item->mataKuliah?->kategori_mk ?? -1) === 1),
                                        ],
                                        [
                                            'judul' => 'Belum Dikategorikan',
                                            'warna' => 'secondary',
                                            'items' => $kurikulum->filter(fn ($item) => ! in_array((int) ($item->mataKuliah?->kategori_mk ?? -1), [0, 1], true)),
                                        ],
                                    ];
                                    $nomor = 1;
                                @endphp

                                @if($kurikulum->isEmpty())
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Tidak ada mata kuliah tersedia.</td>
                                    </tr>
                                @else
                                @foreach($kelompokKrs as $kelompok)
                                    @continue($kelompok['items']->isEmpty())
                                    <tr class="table-{{ $kelompok['warna'] }}">
                                        <td colspan="6" class="fw-bold py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>{{ $kelompok['judul'] }}</span>
                                                <span class="badge bg-{{ $kelompok['warna'] }}">
                                                    {{ $kelompok['items']->count() }} Mata Kuliah
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @foreach($kelompok['items'] as $item)
                                        @php
                                            $terpilih = $mataKuliahTerpilih->contains((string) $item->matakuliah_id);
                                        @endphp
                                        <tr>
                                            <td>
                                                <input class="form-check-input krs-checkbox" type="checkbox"
                                                    name="krs[]" value="{{ $item->kurikulum_id }}"
                                                    data-sks="{{ (int) ($item->mataKuliah?->sks ?? 0) }}"
                                                    @checked(in_array($item->kurikulum_id, old('krs', [])) || (old('krs') === null && $terpilih))>
                                            </td>
                                            <td>{{ $nomor++ }}</td>
                                            <td class="fw-semibold">{{ $item->mataKuliah?->nama ?? '-' }}</td>
                                            <td>{{ $item->mataKuliah?->matakuliah_id ?? '-' }}</td>
                                            <td>{{ $item->mataKuliah?->sks ?? 0 }}</td>
                                            <td>
                                                <span class="badge bg-{{ $kelompok['warna'] }}">
                                                    {{ (int) ($item->mataKuliah?->kategori_mk ?? -1) === 0 ? 'Wajib' : ((int) ($item->mataKuliah?->kategori_mk ?? -1) === 1 ? 'Pilihan' : 'Tidak Diketahui') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                        <div class="badge bg-label-primary fs-6 py-2 px-3">
                            Total dipilih: <span id="jumlahMk">0</span> Mata Kuliah &bull;
                            <span id="totalSks">0</span> SKS
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('mahasiswa.status.krs.index') }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bx bx-save me-1"></i>Simpan Perubahan KRS
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
    $(function () {
        function hitungPilihan() {
            let jumlah = 0;
            let totalSks = 0;
            $('.krs-checkbox:checked').each(function () {
                jumlah++;
                totalSks += Number($(this).data('sks')) || 0;
            });
            $('#jumlahMk').text(jumlah);
            $('#totalSks').text(totalSks);
        }

        $('.krs-checkbox').on('change', hitungPilihan);
        hitungPilihan();

        $('#editKrsForm').on('submit', function (event) {
            if ($('.krs-checkbox:checked').length === 0) {
                event.preventDefault();
                Swal.fire('Perhatian', 'Pilih minimal satu mata kuliah.', 'warning');
                return;
            }

            if (!confirm('Simpan perubahan KRS dan kirim kembali untuk menunggu ACC Dosen Pembimbing?')) {
                event.preventDefault();
            }
        });
    });
</script>
@endpush
