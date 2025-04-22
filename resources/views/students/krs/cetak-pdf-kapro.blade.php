<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Rencana Studi - Kaprodi</title>
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

        .signature {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            table-layout: fixed;
        }

        .signature td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }

        .signature img {
            display: inline-block;
            height: 50px;
            margin-bottom: 5px;
            border: 1px solid #ccc;
        }

        .signature .placeholder {
            display: inline-block;
            width: 100px;
            height: 50px;
            margin-bottom: 5px;
            border: 1px dashed #ccc;
            background-color: #f9f9f9;
        }

        .signature .name {
            margin-top: 10px;
            font-weight: bold;
            text-decoration: underline;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50%;
            height: 50%;
            background: url("{{ public_path('assets/img/header/logo_sbh.png') }}") no-repeat center;
            background-size: contain;
            opacity: 0.1;
            z-index: -1;
        }

        .content {
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body>
    <div class="watermark"></div>
    <div class="header">
        <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header Image"
            style="width: 100%; height: auto; margin-bottom: 20px;">
        <h1 style="text-align: center; margin: 0;">Form Kartu Rencana Studi Kaprodi</h1>
        <h3 style="text-align: center; margin: 0;">Tahun Ajaran {{ $ta->nama }} ( {{ $ta->semester }}) </h3>
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

    <table class="signature">
        <tr>
            <td style="text-align: center;">
                <p>Mahasiswa</p>
                @if (isset($ttdMahasiswa) && $ttdMahasiswa)
                <img src="data:image/png;base64,{{ $ttdMahasiswa }}" alt="Tanda Tangan Mahasiswa" style="height: 50px;">
                @else
                <div class="placeholder"></div>
                @endif
                <div class="name">{{ $mahasiswa->nama }}</div>
            </td>
            <td style="text-align: center;">
                <p>Dosen Pembimbing</p>
                @if (isset($ttdDosPem) && $ttdDosPem)
                <img src="data:image/png;base64,{{ $ttdDosPem }}" alt="Tanda Tangan Dosen Pembimbing"
                    style="height: 50px;">
                @else
                <div class="placeholder"></div>
                @endif
                <div class="name">{{ $mahasiswa->dosen->nama ?? 'Dosen Pembimbing' }}</div>
            </td>
            <td style="text-align: center;">
                <p>Kaprodi</p>
                @if ($ttd)
                <div class="placeholder"></div>
                @else
                <div class="placeholder"></div>
                @endif
                <div class="name">{{ $mahasiswa->programStudi->kaprod ?? 'Ketua Program Studi' }}</div>
            </td>
        </tr>
    </table>
</body>

</html>