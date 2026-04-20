@extends('layouts.dosen')
@section('title', 'Hasil Evaluasi Dosen (EDOM)')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h4 class="text-white mb-2 fw-bold">Rata-rata Nilai Evaluasi Keseluruhan</h4>
                                <p class="text-white-50 mb-0">Berdasarkan penilaian mahasiswa dari seluruh mata kuliah yang
                                    Anda ampu.</p>
                            </div>
                            <div class="text-end mt-3 mt-md-0">
                                <div class="display-4 text-white fw-bold mb-0">
                                    {{ $rataRataKeseluruhan }}<span class="fs-4 text-white-50">5.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white pb-0 pt-4">
                        <h5 class="card-title fw-bold text-primary mb-3"><i class="bx bx-list-ul me-2"></i>Rincian Nilai per
                            Mata Kuliah</h5>
                    </div>
                    <div class="card-body">
                        @if($hasilEdom->isEmpty())
                            <div class="alert alert-info text-center py-4">
                                <i class="bx bx-info-circle fs-2 mb-2"></i><br>
                                Belum ada data evaluasi dari mahasiswa untuk mata kuliah yang Anda ampu.
                            </div>
                        @else
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Kode MK</th>
                                            <th>Nama Mata Kuliah</th>
                                            <th>Semester</th>
                                            <th class="text-center">Jumlah Responden</th>
                                            <th class="text-center">Rata-rata Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        @foreach($hasilEdom as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><span class="badge bg-label-primary">{{ $item->matakuliah_id }}</span></td>
                                                <td class="fw-semibold">{{ $item->nama }}</td>
                                                <td>Semester {{ $item->smt }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-label-info">{{ $item->jumlah_pemberi_nilai }}
                                                        Mahasiswa</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <i class="bx bxs-star text-warning me-1"></i>
                                                        <span class="fw-bold">{{ round($item->rata_rata_nilai, 2) }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection