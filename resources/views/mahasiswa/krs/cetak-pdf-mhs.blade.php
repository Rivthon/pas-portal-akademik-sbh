<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Rencana Studi - Mahasiswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 100%;
            height: auto;
        }

        .info {
            margin-bottom: 20px;
        }

        .info p {
            margin: 5px 0;
        }

        .verification {
            width: 100%;
            margin-top: 30px;
            text-align: center;
        }

        .verification-stamp {
            display: inline-block;
            width: 330px;
            border: 3px double #198754;
            padding: 12px 18px;
            text-align: center;
            color: #146c43;
            background-color: #f2fbf6;
        }

        .verification-stamp .stamp-title {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .verification-stamp .stamp-name {
            font-size: 13px;
            font-weight: bold;
            margin: 5px 0;
        }

        .verification-stamp .stamp-meta {
            font-size: 10px;
            color: #3d6f56;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50%;
            height: 50%;
            background: url('data:image/png;base64,{{ $logo }}') no-repeat center;
            background-size: contain;
            opacity: 0.1;
            z-index: -1;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 5px 10px;
            font-size: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="watermark"></div>
    <div class="header">
        {{-- <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header Image"
            style="width: 100%; height: auto; margin-bottom: 20px;"> --}}
        @if (isset($headerKrs) && $headerKrs)
        <img src="data:image/png;base64,{{ $headerKrs }}" alt="header krs" style="width: 100%; height: auto;">
        @endif
        {{-- <h1 style="text-align: center; margin: 0;">Form Kartu Rencana Studi Kaprodi</h1>
        <h3 style="text-align: center; margin: 0;">Tahun Ajaran {{ $ta->nama }} ( {{ $ta->semester }}) </h3> --}}
    </div>

    <div class="info">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <tbody>
                <tr>
                    <td style="padding: 4px 8px; width: 25%;"><strong>Nama Mahasiswa</strong></td>
                    <td style="padding: 4px 8px;">: {{ $mahasiswa->nama }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 8px; width: 25%;"><strong>NIM</strong></td>
                    <td style="padding: 4px 8px;">: {{ $mahasiswa->nim }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 8px; width: 25%;"><strong>Semester</strong></td>
                    <td style="padding: 4px 8px;">
                        : {{ $mahasiswa->semester }} /
                        @php
                        $semester = $mahasiswa->semester;
                        if ($semester >= 1 && $semester <= 2) { $tingkat=1; } elseif ($semester>= 3 && $semester <= 4) {
                                $tingkat=2; } elseif ($semester>= 5 && $semester <= 6) { $tingkat=3; } elseif
                                    ($semester>= 7 &&
                                    $semester <= 8) { $tingkat=4; } else { $tingkat='Tidak Diketahui' ; } @endphp
                                        Tingkat {{ $tingkat }} </td>
                </tr>
                <tr>
                    <td style="padding: 4px 8px; width: 15%;"><strong>Program Studi</strong></td>
                    <td style="padding: 4px 8px;">: {{ $mahasiswa->programStudi->nama }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Mata Kuliah</th>
                <th>Nama Mata Kuliah</th>
                <th>SKS</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSKS = 0; @endphp
            @foreach ($krs as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $item->kurikulum->mataKuliah->matakuliah_id }}</td>
                <td>{{ $item->kurikulum->mataKuliah->nama }}</td>
                <td style="text-align: center;">{{ $item->kurikulum->mataKuliah->sks }}</td>
            </tr>
            @php $totalSKS += $item->kurikulum->mataKuliah->sks; @endphp
            @endforeach
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">Total SKS</td>
                <td style="text-align: center; font-weight: bold;">{{ $totalSKS }}</td>
            </tr>
        </tbody>
    </table>

    <div class="verification">
        <div class="verification-stamp">
            <div class="stamp-title">TERVERIFIKASI OLEH DOSPEM</div>
            <div>Dosen Pembimbing Akademik</div>
            <div class="stamp-name">{{ $persetujuan?->disetujuiOleh?->nama ?? $mahasiswa->dosen?->nama ?? 'Dosen Pembimbing' }}</div>
            <div class="stamp-meta">
                Disetujui pada {{ $persetujuan?->disetujui_pada?->translatedFormat('d F Y H:i') ?? '-' }}
            </div>
        </div>
    </div>
    <div class="footer">
        © {{ date('Y') }} {{ $settings->footer_name }} – Dicetak oleh {{ $mahasiswa->nama }} (NIM: {{ $mahasiswa->nim
        }})<br>
        Tahun Akademik: {{ $ta->nama }} (Semester {{ $ta->semester }})<br>
        Dicetak pada {{ date('d/m/Y H:i') }} </div>
</body>

</html>
