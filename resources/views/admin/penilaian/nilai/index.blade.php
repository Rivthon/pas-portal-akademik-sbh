@extends('layouts.master')

@section('content')
@push('head')
<style>
    .transkrip-page .stat-card {
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        padding: 1.5rem;
        background: #fff;
    }
    .transkrip-page .filter-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        padding: 1.5rem;
    }
    .transkrip-page .filter-card label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #8592a3;
        margin-bottom: 6px;
        display: block;
    }
    .transkrip-page .filter-card .form-select {
        border: none;
        background: #f3f3f7;
        font-size: .85rem;
        border-radius: 8px;
        padding: .55rem .85rem;
    }
    .transkrip-page .table-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        overflow: hidden;
    }
    .transkrip-page .table-modern thead th {
        background: #f3f3f7;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #8592a3;
        border: none;
        padding: .85rem 1.25rem;
    }
    .transkrip-page .table-modern tbody td {
        padding: .85rem 1.25rem;
        vertical-align: middle;
        border-color: rgba(0,0,0,.04);
        font-size: .875rem;
    }
</style>
@endpush

<div class="transkrip-page">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Transkrip Nilai Mahasiswa</h4>
        <p class="text-muted mb-0" style="font-size: .875rem;">
            Pilih program studi dan semester, lalu pilih mahasiswa untuk melihat transkrip nilainya.
        </p>
    </div>
    <div class="filter-card mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="program-studi">Program Studi</label>
                <select id="program-studi" class="form-select">
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach ($programStudi as $ps)
                    <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="semester">Semester</label>
                <select id="semester" class="form-select">
                    <option value="">-- Pilih Semester --</option>
                    @for ($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}">Semester {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label for="mahasiswa">Pilih Mahasiswa</label>
                <select id="mahasiswa" class="form-select" disabled>
                    <option value="">-- Pilih Mahasiswa --</option>
                </select>
            </div>
        </div>
    </div>

    <div class="table-card" id="krs-table-container" style="display: none;">
        <div class="p-4 border-bottom text-center">
            <h5 class="mb-0 fw-bold">Transkrip Nilai <i class="text-muted">(Academic Transcription)</i></h5>
        </div>
        <div class="table-responsive">
            <table id="krs-table" class="table table-modern table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 5%">No</th>
                        <th>Kode MK (Code)</th>
                        <th>Mata Kuliah (Courses)</th>
                        <th class="text-center">SKS (Credit)</th>
                        <th class="text-center">Nilai (Grade)</th>
                        <th class="text-center">Huruf (Symbol)</th>
                        <th class="text-center">Angka (Score)</th>
                        <th class="text-center">SKS x Angka (Point)</th>
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
                        const option = document.createElement('option');
                        option.value = mahasiswa.mahasiswa_id;
                        option.textContent = mahasiswa.nama;
                        mahasiswaSelect.appendChild(option);
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
        document.getElementById('krs-table-container').style.display = 'none';
        return;
    }

    try {
        const url = `/admin/api/krs-mahasiswa/${mahasiswaId}`;
        const response = await fetch(url);
        const data = await response.json();

        krsTableBody.innerHTML = ''; // Bersihkan tabel sebelumnya

        if (data.status === "success" && data.data) {
            document.getElementById('krs-table-container').style.display = 'block';
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
                    [index++, krs.kode_mk, krs.nama_mata_kuliah, sks, krs.akhir || '-',
                        krs.khs || '-', nilaiAngka.toFixed(2), sksAngka.toFixed(2)]
                        .forEach((value) => {
                            const cell = document.createElement('td');
                            cell.textContent = value;
                            row.appendChild(cell);
                        });
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
                document.getElementById('krs-table-container').style.display = 'none';
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
