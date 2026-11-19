@extends('layouts.dosen')
@section('title', 'Absensi Mahasiswa')
@section('content')
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#1e3c72,#2a5298)">
        <h4 class="text-white mb-1"><i class="bx bx-user-check me-2"></i>Rekap Absensi Mahasiswa</h4>
        <p class="mb-0 text-white-50">Pilih mahasiswa untuk melihat kehadiran teori dan praktik pada seluruh mata kuliah yang diambil.</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('dosen.sekprodi.absensi-mahasiswa.index') }}" class="row g-3 align-items-end">
            @if($managedPrograms->count() > 1)
                <div class="col-lg-2">
                    <label class="form-label">Program Studi</label>
                    <select name="program_studi_id" class="form-select" onchange="document.getElementById('pilihMahasiswa').value=''; this.form.submit()">
                        @foreach($managedPrograms as $program)
                            <option value="{{ $program->jurusan_id }}" @selected((string)$selectedProgramId === (string)$program->jurusan_id)>{{ $program->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="program_studi_id" value="{{ $selectedProgramId }}">
            @endif
            <div class="col-lg-2">
                <label class="form-label">Tahun Akademik</label>
                <select name="ta_id" class="form-select">
                    @foreach($tahunAkademik as $ta)
                        <option value="{{ $ta->ta_id }}" @selected((int)$selectedTaId === (int)$ta->ta_id)>{{ $ta->nama }} - {{ $ta->semester }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label" for="cariMahasiswa">Cari NIM / Nama</label>
                <div class="input-group">
                    <input type="search" id="cariMahasiswa" class="form-control" placeholder="Contoh: 0225002 atau Fani">
                    <button type="button" id="tombolCariMahasiswa" class="btn btn-outline-primary">
                        <i class="bx bx-search me-1"></i>Cari
                    </button>
                </div>
                <small id="hasilCariMahasiswa" class="text-muted">Ketik NIM atau nama mahasiswa.</small>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Mahasiswa</label>
                <select name="mahasiswa_id" id="pilihMahasiswa" class="form-select" required>
                    <option value="">-- Pilih mahasiswa --</option>
                    @foreach($mahasiswaList as $mahasiswa)
                        <option value="{{ $mahasiswa->mahasiswa_id }}" @selected((int)request('mahasiswa_id') === (int)$mahasiswa->mahasiswa_id)>
                            {{ $mahasiswa->nim }} - {{ $mahasiswa->nama }} ({{ $mahasiswa->label_kelas }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 d-grid">
                <button class="btn btn-primary"><i class="bx bx-bar-chart-alt-2 me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

@if($selectedMahasiswa)
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h5 class="mb-1">{{ $selectedMahasiswa->nama }}</h5>
            <span class="text-muted">{{ $selectedMahasiswa->nim }} · {{ $selectedMahasiswa->programStudi?->nama }} · {{ $selectedMahasiswa->label_kelas }}</span>
        </div>
        <a class="btn btn-primary" href="{{ route('dosen.sekprodi.absensi-mahasiswa.download', ['program_studi_id'=>$selectedProgramId,'ta_id'=>$selectedTaId,'mahasiswa_id'=>$selectedMahasiswa->mahasiswa_id]) }}">
            <i class="bx bx-download me-1"></i>Download Seluruh Absensi
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3 col-xl"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Mata Kuliah</small><h4 class="mb-0">{{ $ringkasan['mata_kuliah'] }}</h4></div></div></div>
        <div class="col-6 col-md-3 col-xl"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Hadir</small><h4 class="mb-0 text-success">{{ $ringkasan['hadir'] }}</h4></div></div></div>
        <div class="col-6 col-md-3 col-xl"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Izin / Sakit</small><h4 class="mb-0 text-info">{{ $ringkasan['izin'] + $ringkasan['sakit'] }}</h4></div></div></div>
        <div class="col-6 col-md-3 col-xl"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Alpha</small><h4 class="mb-0 text-danger">{{ $ringkasan['alpha'] }}</h4></div></div></div>
        <div class="col-6 col-md-3 col-xl"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Kehadiran</small><h4 class="mb-0 text-primary">{{ $ringkasan['persentase'] }}%</h4></div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0">Mata Kuliah yang Diikuti</h5></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Mata Kuliah</th><th class="text-center">Pertemuan</th><th class="text-center text-success">H</th><th class="text-center">I</th><th class="text-center">S</th><th class="text-center text-danger">A</th><th class="text-center">Belum</th><th class="text-center">Kehadiran</th><th></th></tr></thead>
                <tbody>
                    @forelse($rekap as $item)
                        <tr>
                            <td><div class="fw-semibold">{{ $item->nama_matakuliah }}</div><small class="text-muted">{{ $item->matakuliah_id }} · {{ $item->sks }} SKS · Semester {{ $item->semester }} · {{ $item->kelas }}</small></td>
                            <td class="text-center">{{ $item->total_pertemuan }}</td>
                            <td class="text-center"><span class="badge bg-label-success">{{ $item->hadir }}</span></td>
                            <td class="text-center"><span class="badge bg-label-warning">{{ $item->izin }}</span></td>
                            <td class="text-center"><span class="badge bg-label-info">{{ $item->sakit }}</span></td>
                            <td class="text-center"><span class="badge bg-label-danger">{{ $item->alpha }}</span></td>
                            <td class="text-center"><span class="badge bg-label-secondary">{{ $item->belum_diabsen }}</span></td>
                            <td class="text-center fw-semibold text-primary">{{ $item->persentase }}%</td>
                            <td class="text-end"><button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#detail-{{ $item->krs_id }}"><i class="bx bx-list-ul me-1"></i>Rincian</button></td>
                        </tr>
                        <tr class="collapse bg-light" id="detail-{{ $item->krs_id }}">
                            <td colspan="9" class="p-3">
                                <div class="table-responsive border rounded bg-white">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead><tr><th>#</th><th>Jenis</th><th>Tanggal</th><th>Topik</th><th>Status</th><th>Keterangan</th></tr></thead>
                                        <tbody>
                                            @forelse($item->pertemuan as $meeting)
                                                @php($badge = match($meeting->kode_status) {'H'=>'success','I'=>'warning','S'=>'info','A'=>'danger',default=>'secondary'})
                                                <tr><td>{{ $loop->iteration }}</td><td>{{ $meeting->jenis }}</td><td>{{ $meeting->tanggal ? \Carbon\Carbon::parse($meeting->tanggal)->format('d/m/Y') : '-' }}</td><td>{{ $meeting->topik }}</td><td><span class="badge bg-label-{{ $badge }}">{{ $meeting->status }}</span></td><td>{{ $meeting->keterangan ?: '-' }}</td></tr>
                                            @empty
                                                <tr><td colspan="6" class="text-center text-muted py-3">Belum ada pertemuan untuk mata kuliah ini.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-5">Mahasiswa belum memiliki KRS yang disetujui pada tahun akademik ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('cariMahasiswa');
    const button = document.getElementById('tombolCariMahasiswa');
    const select = document.getElementById('pilihMahasiswa');
    const feedback = document.getElementById('hasilCariMahasiswa');

    if (!input || !button || !select) return;

    const mahasiswa = Array.from(select.options)
        .filter(option => option.value)
        .map(option => ({ value: option.value, text: option.textContent.trim() }));

    function normalize(value) {
        return value.toLocaleLowerCase('id-ID').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function cariMahasiswa() {
        const keyword = normalize(input.value.trim());
        const selectedValue = select.value;
        const hasil = keyword
            ? mahasiswa.filter(item => normalize(item.text).includes(keyword))
            : mahasiswa;

        select.replaceChildren(new Option(
            hasil.length ? '-- Pilih mahasiswa --' : '-- Mahasiswa tidak ditemukan --',
            ''
        ));
        hasil.forEach(item => select.add(new Option(item.text, item.value)));

        if (hasil.some(item => item.value === selectedValue)) {
            select.value = selectedValue;
        } else if (hasil.length === 1) {
            select.value = hasil[0].value;
        }

        feedback.textContent = keyword
            ? `${hasil.length} mahasiswa ditemukan.`
            : `${hasil.length} mahasiswa tersedia.`;
        feedback.classList.toggle('text-danger', hasil.length === 0);
        feedback.classList.toggle('text-muted', hasil.length > 0);
        select.focus();
    }

    button.addEventListener('click', cariMahasiswa);
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            cariMahasiswa();
        }
    });
});
</script>
@endpush
@endsection
