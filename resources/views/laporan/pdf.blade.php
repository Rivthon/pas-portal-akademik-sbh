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

    <table
        style="width: 100%; font-family: 'Times New Roman', Times, serif; font-size: 14px; border-collapse: collapse;"
        border="1">
        <thead>
            <tr>
                <th rowspan="2" style="text-align: center; vertical-align: middle;">No</th>
                <th rowspan="2" style="text-align: center; vertical-align: middle;">Nama Mahasiswa</th>
                <th colspan="{{ $totalPertemuan }}" style="text-align: center;">Pertemuan</th>
                <th rowspan="2" style="text-align: center; vertical-align: middle;">Total</th>
            </tr>
            <tr>
                @for ($i = 1; $i <= $totalPertemuan; $i++) <th style="text-align: center;">P{{ $i }}</th>
                    @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($mahasiswa as $mhs)
            <tr>
                <td style="text-align: left; padding: 5px;">{{ $mhs->nama }}</td>
                @foreach ($rekapAbsensi as $pertemuan)
                <td style="text-align: center;">{{ $pertemuan['absensi'][$mhs->mahasiswa_id] ?? '-' }}</td>
                @endforeach
                <td style="text-align: center;">
                    {{-- Hitung total kehadiran mahasiswa --}}
                    {{
                    collect($rekapAbsensi)
                    ->filter(fn($pertemuan) => ($pertemuan['absensi'][$mhs->mahasiswa_id] ?? null) === 'H')
                    ->count()
                    }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <!-- QR Code di bawah tabel -->
    <div class="qr-code">
        @if(!empty($qrCodeBase64))
        <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code">
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
