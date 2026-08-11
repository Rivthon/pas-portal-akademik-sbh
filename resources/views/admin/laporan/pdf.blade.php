<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Absensi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header .logo {
            width: 80px;
        }

        .header .text {
            text-align: right;
            flex: 1;
        }

        .header .text h1 {
            margin: 0;
            font-size: 1.5em;
        }

        .header .text h2 {
            margin: 0;
            font-size: 1em;
            color: #555;
        }

        .content p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
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
    <div class="header">
        <!-- Logo -->
        @if($logoBase64)
        <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo" class="logo">
        @endif

        <!-- Teks Header -->
        <div class="text">
            <h1>Laporan Rekap Absensi</h1>
            <h2>{{ $settings ? $settings->name : 'Nama Aplikasi' }}</h2>
        </div>
    </div>

    <div class="content">
        <p><strong>Mata Kuliah:</strong> {{ $jadwal->kurikulum->mataKuliah->nama }}</p>
        <p><strong>Total Pertemuan:</strong> {{ $totalPertemuan }}</p>
        {{-- <p><strong>Dosen:</strong> {{ $jadwal->dosen->name ?? 'Tidak Diketahui' }}</p> --}}
    </div>

    @include('dosen.absensi.partials.rekap-table')
    <div class="qr-code">
        @if(isset($qrFilePath))
        <img src="{{ $qrFilePath }}" alt="QR Code" style="width: 100px; height: 100px;">
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ $settings ? $settings->footer_name : 'Nama Aplikasi' }}.
            {{ $settings ? $settings->copyright : 'All rights reserved.' }}
        </p>
    </div>
</body>

</html>
