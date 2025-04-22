@extends('layouts.mahasiswa')

@section('content')
<div class="container">
    <div class="col-md-12">
        <div class="row mt-4">
            <div class="col-md-12">
                <!-- Header Section -->
                <div class="card shadow-sm mb-4">
                    <div class="d-flex align-items-center g-0">
                        <!-- Content Section -->
                        <div class="col-md-7">
                            <div class="card-body">
                                <h2 class="card-title text-primary mb-3 fw-bold">
                                    Formulir Evaluasi Dosen Mengajar
                                </h2>
                                <p class="mb-4 text-muted" style="line-height: 1.6;">
                                    Semua pertanyaan wajib diisi dengan benar dan jujur.
                                </p>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bx bx-book-open me-2"></i>
                                        <p class="mb-0"><strong>Mata Kuliah:</strong> {{
                                            $krs->kurikulum->matakuliah->nama }}
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bx bx-building me-2"></i>
                                        <p class="mb-0"><strong>Program Studi:</strong> {{
                                            $krs->kurikulum->programStudi->nama }}</p>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-user me-2"></i>
                                        <p class="mb-0"><strong>Dosen:</strong> {{ $dosen->nama }}</p>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-label me-2"></i>
                                        <p class="mb-0"><strong>Jenis Dosen:</strong> {{ ucfirst($dosen->jenis_dosen) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Image Section -->
                        <div class="col-md-5 text-center">
                            <div class="p-3">
                                <img src="{{ asset('assets/img/illustrations/chat.png') }}" class="img-fluid"
                                    alt="Illustration of a schedule" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Section -->
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Form Section -->
                <form
                    action="{{ route('mahasiswa.edom.submit', ['krs_id' => $krs->krs_id, 'dosen_id' => $dosen->dosen_id]) }}"
                    method="POST">
                    @csrf
                    <input type="hidden" name="krs_id" value="{{ $krs->krs_id }}">
                    <!-- Evaluation Questions -->
                    @foreach ($evaluasis as $index => $evaluasi)
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="mb-3">{{ $index + 1 }}. {{ $evaluasi->nama }}</h6>
                            @foreach (range(1, 5) as $value)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="responses[{{ $evaluasi->eval_id }}]"
                                    id="evaluasi_{{ $evaluasi->eval_id }}_{{ $value }}" value="{{ $value }}" {{
                                    old("responses.{$evaluasi->eval_id}") == $value ? 'checked' : '' }}
                                required>
                                <label class="form-check-label" for="evaluasi_{{ $evaluasi->eval_id }}_{{ $value }}">
                                    @switch($value)
                                    @case(1) Sangat Tidak Setuju @break
                                    @case(2) Tidak Setuju @break
                                    @case(3) Netral @break
                                    @case(4) Setuju @break
                                    @case(5) Sangat Setuju @break
                                    @endswitch
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                    <!-- Suggestion Section -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-3">Saran untuk Dosen:</h6>
                            <div class="form-group">
                                <textarea name="suggestion" class="form-control" rows="5"
                                    placeholder="Tulis saran Anda di sini..."
                                    required>{{ old('suggestion') }}</textarea>
                            </div>
                        </div>
                        <div class="container mb-4">
                            <div class="d-flex justify-content-between mt-3">
                                <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">Submit EDOM</button>
                            </div>
                        </div>

                    </div>

                    <!-- Action Buttons -->

                </form>
            </div>
        </div>
    </div>
</div>
@endsection