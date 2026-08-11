<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Absensi Praktik</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
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

        .qr-code {
            margin-bottom: 20px;
            margin-top: 20px;
            text-align: right;
        }

        .qr-code img {
            width: 100px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <!-- Header dengan Gambar -->
    <div class="header">
        <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header Image">
    </div>
    <div class="container">
        <h2>Laporan Rekap Absensi Praktik</h2>

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
            <tr>
                <th>Nama Dosen</th>
                @if($dosenMatakuliah->isNotEmpty())
                <table>
                    @foreach ($dosenMatakuliah as $dosen)
                    <tr>
                        <td>: {{ $dosen->nama }}</td>
                    </tr>
                    @endforeach
                </table>
                @else
                <p>Tidak ada data dosen.</p>
                @endif
            </tr>
        </table>

        <!-- Rekap Absensi -->
        @include('dosen.absensi.partials.rekap-table')
        @include('dosen.absensi.partials.signatures')
    </div>

    <!-- <div class="qr-code">
        @if(isset($qrFilePath))
        <img src="{{ $qrFilePath }}" alt="QR Code" style="width: 100px; height: 100px;">
        @endif
    </div> -->

    <!-- Footer -->
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ $settings ? $settings->footer_name : 'Nama Aplikasi' }}.
            {{ $settings ? $settings->copyright : 'All rights reserved.' }}
        </p>
    </div>
</body>

</html>
