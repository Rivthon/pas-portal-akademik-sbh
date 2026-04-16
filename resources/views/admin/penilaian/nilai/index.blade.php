@extends('layouts.master')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center item g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Trankrip Nilai Mahasiswa
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Pastikan Anda telah memilih program studi
                    dan semester yang bersangkutan. Kemudian pilih mahasiswa yang ingin Anda lihat nilai transkripnya.
                </p>

            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/nilai.png') }}" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="container mt-4 mb-4">
        <div>
            <h5>Pilih Program Studi</h5>
            <select id="program-studi" class="form-select">
                <option value="">-- Pilih Program Studi --</option>
                @foreach ($programStudi as $ps)
                <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                @endforeach
            </select>

            <h5 class="mt-4">Pilih Semester</h5>
            <select id="semester" class="form-select">
                <option value="">-- Pilih Semester --</option>
                @for ($i = 1; $i <= 8; $i++) <option value="{{ $i }}">Semester {{ $i }}</option>
                    @endfor
            </select>

            <h5 class="mt-4">Pilih Mahasiswa</h5>
            <select id="mahasiswa" class="form-select" disabled>
                <option value="">-- Pilih Mahasiswa --</option>
            </select>

            <h2 class="mt-4 text-center">Transkrip Nilai <i>(Academic Transcription)</i></h2>


            <table id="krs-table" class="table table-bordered mt-3" style="display: none;">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Kode MK (Code)</th>
                        <th>Mata Kuliah (Courses)</th>
                        <th>SKS (Credit)</th>
                        <th>Nilai (Grade)</th>
                        <th>Huruf (Symbol)</th>
                        <th>Angka (Score)</th>
                        <th>SKS x Angka (Point)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data akan diisi dengan AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const programStudiSelect = document.getElementById('program-studi');
    const semesterSelect = document.getElementById('semester');
    const mahasiswaSelect = document.getElementById('mahasiswa');
    const krsTable = document.getElementById('krs-table');
    const krsTableBody = krsTable.querySelector('tbody');

    // Fungsi reusable untuk mengambil data dari API
    const fetchData = async (url, onSuccess, onError) => {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error("Gagal memuat data dari server.");
            const data = await response.json();
            onSuccess(data);
        } catch (error) {
            console.error(error);
            if (onError) onError(error.message);
        }
    };

    // Fungsi untuk mengambil data mahasiswa
    const fetchMahasiswa = () => {
        const prodiId = programStudiSelect.value; // ID Program Studi
        const semester = semesterSelect.value; // Semester yang dipilih

        if (!prodiId || !semester) {
            mahasiswaSelect.innerHTML = '<option value="">-- Pilih Mahasiswa --</option>';
            mahasiswaSelect.disabled = true;
            return;
        }

        const url = `/admin/api/mahasiswa-by-prodi-semester?jurusan_id=${prodiId}&semester=${semester}`;
        fetchData(
            url,
            (data) => {
                mahasiswaSelect.innerHTML = '<option value="">-- Pilih Mahasiswa --</option>';
                if (data.length > 0) {
                    data.forEach((mahasiswa) => {
                        const option = `<option value="${mahasiswa.mahasiswa_id}">${mahasiswa.nama}</option>`;
                        mahasiswaSelect.insertAdjacentHTML('beforeend', option);
                    });
                    mahasiswaSelect.disabled = false;
                } else {
                    mahasiswaSelect.innerHTML = '<option value="">Tidak ada mahasiswa</option>';
                    mahasiswaSelect.disabled = true;
                }
            },
            () => {
                alert("Terjadi kesalahan saat memuat data mahasiswa. Silakan coba lagi.");
                mahasiswaSelect.innerHTML = '<option value="">-- Pilih Mahasiswa --</option>';
                mahasiswaSelect.disabled = true;
            }
        );
    };
// Fungsi untuk mengambil data KRS
const nilaiHurufToAngka = (huruf) => {
    const konversi = {
        'A': 4.00,
        'AB': 3.75,
        'BA': 3.50,
        'B': 3.00,
        'BC': 2.75,
        'C': 2.00,
        'D': 1.00,
        'E': 0
    };
    return konversi[huruf] || 0;
};

const fetchKRS = async () => {
    const mahasiswaId = mahasiswaSelect.value; // ID Mahasiswa yang dipilih

    if (!mahasiswaId) {
        krsTable.style.display = 'none';
        return;
    }

    try {
        const url = `/admin/api/krs-mahasiswa/${mahasiswaId}`;
        const response = await fetch(url);
        const data = await response.json();

        krsTableBody.innerHTML = ''; // Bersihkan tabel sebelumnya

        if (data.status === "success" && data.data) {
            krsTable.style.display = '';
            krsTableBody.innerHTML = '';

            let index = 1;
            let totalSksAll = 0;
            let totalSksAngkaAll = 0;

            // Looping setiap semester
            for (const semester in data.data) {
                let totalSksSemester = 0;
                let totalSksAngkaSemester = 0;

                // Tambahkan header semester
                const semesterRow = document.createElement('tr');
                semesterRow.innerHTML = `
                    <td colspan="8" class="table-secondary fw-bold text-center">
                        Semester ${semester}
                    </td>
                `;
                krsTableBody.appendChild(semesterRow);

                // Looping mata kuliah di semester tersebut
                data.data[semester].forEach((krs) => {
                    const sks = krs.sks || 0;
                    const nilaiAngka = nilaiHurufToAngka(krs.khs);
                    const sksAngka = sks * nilaiAngka;

                    totalSksSemester += sks;
                    totalSksAngkaSemester += sksAngka;

                    totalSksAll += sks;
                    totalSksAngkaAll += sksAngka;

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${index++}</td>
                        <td>${krs.kode_mk}</td>
                        <td>${krs.nama_mata_kuliah}</td>
                        <td>${sks}</td>
                        <td>${krs.akhir || '-'}</td>
                        <td>${krs.khs || '-'}</td>
                        <td>${nilaiAngka.toFixed(2)}</td>
                        <td>${sksAngka.toFixed(2)}</td>
                    `;
                    krsTableBody.appendChild(row);
                });

                // Tambahkan IPS per semester
                const ips = totalSksAngkaSemester / totalSksSemester;
                const ipsRow = document.createElement('tr');
                ipsRow.innerHTML = `
                <td colspan="6" class="text-Center">
                    <strong>IPS</strong>
                </td>
                <td colspan="2" class="text-center"><strong>${ips.toFixed(2)}</strong></td>
                `;
                krsTableBody.appendChild(ipsRow);
                }

                 // Tambahkan total semua semester
                const totalSksrow = document.createElement('tr');
                totalSksrow.innerHTML = `
                    <td colspan="6" class="text-center"><strong>Jumlah SKS </strong></td>
                    <td colspan="2" class="text-center"><strong>${totalSksAll}</strong></td>
                `;
                krsTableBody.appendChild(totalSksrow);

                const totalRow = document.createElement('tr');
                totalRow.innerHTML = `
                <td colspan="6" class="text-center"><strong>Total SKS x Angka </strong></td>
                <td colspan="2" class="text-center"><strong>${totalSksAngkaAll.toFixed(2)}</strong></td>
                `;
                krsTableBody.appendChild(totalRow);

                // Hitung IPK kumulatif
                const ipk = totalSksAngkaAll / totalSksAll;
                const ipkRow = document.createElement('tr');
                ipkRow.innerHTML = `
                    <td colspan="6" class="text-center"><strong>IPK</strong></td>
                    <td colspan="2" class="text-center"><strong> ${ipk.toFixed(2)}</strong></td>
                `;
                krsTableBody.appendChild(ipkRow);

                // Tentukan predikat
                let predikat = '';
                if (ipk >= 3.50) {
                    predikat = 'Dengan Pujian';
                } else if (ipk >= 3.00) {
                    predikat = 'Sangat Memuaskan';
                } else if (ipk >= 2.75) {
                    predikat = 'Memuaskan';
                } else {
                    predikat = 'Cukup';
                }

                // Tambahkan baris predikat
                const predikatRow = document.createElement('tr');
                predikatRow.innerHTML = `
                    <td colspan="6" class="text-center"><strong>Predikat</strong></td>
                    <td colspan="2" class="text-center"><strong>${predikat}</strong></td>
                `;
                krsTableBody.appendChild(predikatRow);

            } else {
                krsTable.style.display = 'none';
                alert(data.message || "Data KRS tidak ditemukan.");
            }

        } catch (error) {
            console.error(error);
            alert("Terjadi kesalahan saat memuat data KRS. Silakan coba lagi.");
        }
    };

// Event listeners
programStudiSelect.addEventListener('change', fetchMahasiswa);
semesterSelect.addEventListener('change', fetchMahasiswa);
mahasiswaSelect.addEventListener('change', fetchKRS);
});

    // Fungsi untuk menampilkan pesan pada tabel
</script>