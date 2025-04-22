<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Absensi Pertemuan Praktik Dosen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        /* Header Styling */
        .header {
            text-align: center;
            margin-bottom: 10px;
            width: 100%;
        }

        .header img {
            width: 100%;
            height: auto;
        }

        /* Container */
        .container {
            width: 100%;
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        /* Tabel Informasi Mata Kuliah (Tanpa Border) */
        .info-table {
            width: 100%;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .info-table th {
            text-align: left;
            width: 30%;
            padding: 5px 0;
        }

        .info-table td {
            padding: 5px 0;
        }

        /* Tabel Rekap Absensi */
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 10px;
        }

        .rekap-table th,
        .rekap-table td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .rekap-table th {
            background: #f2f2f2;
            text-align: center;
        }
    </style>
</head>

<body>

    <!-- Header dengan Gambar -->
    <div class="header">
        <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header Image">
    </div>

    <div class="container">
        <h2>Laporan Rekap Absensi Pertemuan Praktik Dosen</h2>

        <!-- Informasi Mata Kuliah -->
        <table class="info-table">
            <tr>
                <th>Mata Kuliah</th>
                <td>: {{ $jadwal->kurikulum->mataKuliah->nama }}</td>
            </tr>
            <tr>
                <th>Semester</th>
                <td>: {{ $jadwal->kurikulum->mataKuliah->smt }} - {{ $jadwal->kurikulum->mataKuliah->semester }}</td>
            </tr>
            <tr>
                <th>SKS</th>
                <td>: {{ $jadwal->kurikulum->mataKuliah->sks }}</td>
            </tr>

        </table>

        <!-- Rekap Absensi -->
        <table class="rekap-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Pokok Pembahasan</th>
                    <th>Sub Pokok Pembahasan</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Durasi (Menit)</th>
                    <th>Jumlah Mahasiswa</th>
                    <th>Hadir</th>
                    <th>Sakit</th>
                    <th>Ijin</th>
                    <th>Tidak Hadir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jadwal->pertemuan as $index => $pertemuan)
                @php
                $jamMulai = \Carbon\Carbon::parse($pertemuan->jam_mulai);
                $jamSelesai = \Carbon\Carbon::parse($pertemuan->jam_selesai);
                $durasi = $jamMulai->diffInMinutes($jamSelesai);

                // Hitung jumlah status absensi
                $hadir = $pertemuan->absensi->where('status', 'hadir')->count();
                $sakit = $pertemuan->absensi->where('status', 'sakit')->count();
                $ijin = $pertemuan->absensi->where('status', 'izin')->count();
                $tidakHadir = $pertemuan->absensi->where('status', 'tidak hadir')->count();

                // Jumlah total mahasiswa yang masuk dalam daftar absensi
                $jumlahMahasiswa = $pertemuan->absensi->count();
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($pertemuan->tanggal_pertemuan)->translatedFormat('l, d-m-Y') }}</td>
                    <td>{{ $pertemuan->topik }}</td>
                    <td>{{ $pertemuan->sub_topik }}</td>
                    <td>{{ $pertemuan->jam_mulai }}</td>
                    <td>{{ $pertemuan->jam_selesai }}</td>
                    <td style="text-align: center;">{{ $durasi }} menit</td>
                    <td style="text-align: center;">{{ $jumlahMahasiswa }}</td>
                    <td style="text-align: center;">{{ $hadir }}</td>
                    <td style="text-align: center;">{{ $sakit }}</td>
                    <td style="text-align: center;">{{ $ijin }}</td>
                    <td style="text-align: center;">{{ $tidakHadir }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>

</html>