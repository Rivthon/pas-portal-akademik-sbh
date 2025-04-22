<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KHS Mahasiswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        .header {
            margin-bottom: 20px;
        }

        .header img {
            width: 80px;
            height: auto;
        }

        .header h1,
        .header h3 {
            margin: 5px 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            /* Remove border from the table */
        }

        .header-table td {
            border: none;
            /* Remove border from table cells */
            text-align: center;
            vertical-align: middle;
        }

        .info-table {
            width: 100%;
            font-size: 12px;
            margin-bottom: 20px;
            border-collapse: collapse;
            background-color: transparent;
            /* Transparent background */
            border: none;
            /* Remove border from the table */
        }

        .info-table td {
            border: none;
            /* Remove border from table cells */
            padding: 6px 0;
            text-align: left;
        }

        /* General Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
            /* Warna border lebih soft */
        }

        th,
        td {
            padding: 10px;
            /* Padding lebih besar untuk kenyamanan */
            text-align: left;
        }



        /* Footer Styling */
        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 12px;
            /* Ukuran lebih konsisten */
            color: #555;
        }

        /* Warna Dinamis Berdasarkan Program Studi */
        .gizi {
            background-color: #fffbea;
            /* Kuning lembut */
            color: #a68c00;
            /* Teks kontras untuk kuning */
        }

        .farmasi {
            background-color: #f3e8ff;
            /* Ungu lembut */
            color: #6b3fa0;
            /* Teks kontras untuk ungu */
        }

        .bidan {
            background-color: #eaf6ff;
            /* Biru lembut */
            color: #005a9e;
            /* Teks kontras untuk biru */
        }

        /* Hover Effects */
        table tr:hover td {
            background-color: #f1f1f1;
            /* Highlight pada hover */
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 8px;
            }

            .footer {
                font-size: 10px;
            }
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <div class="header">
        <table class="header-table" style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td style="width: 20%; text-align: center;">
                    <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo" class="logo">
                </td>
                <td style="text-align: center;">
                    <h1>Kartu Hasil Studi (KHS)</h1>
                    <h3>Tahun Ajaran : {{ $ta->nama }} - {{ $ta->semester }}</h3>
                </td>
            </tr>
        </table>
        <table class="info-table">
            <tbody>
                <tr>
                    <td style="padding: 6px 10px; font-weight: bold; color: #333; width: 20%;">Nama Mahasiswa</td>
                    <td style="padding: 6px 10px; color: #555; width: 30%;">: {{ $mahasiswa->nama }}</td>
                    <td style="padding: 6px 10px; font-weight: bold; color: #333; width: 20%;">NIM</td>
                    <td style="padding: 6px 10px; color: #555; width: 30%;">: {{ $mahasiswa->nim }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 10px; font-weight: bold; color: #333;">Semester</td>
                    <td style="padding: 6px 10px; color: #555;">
                        : {{ $mahasiswa->semester }} /
                        @php
                        $semester = $mahasiswa->semester;
                        if ($semester >= 1 && $semester <= 2) { $tingkat=1; } elseif ($semester>= 3 && $semester <= 4) {
                                $tingkat=2; } elseif ($semester>= 5 && $semester <= 6) { $tingkat=3; } elseif
                                    ($semester>= 7 && $semester <= 8) { $tingkat=4; } else { $tingkat='Tidak Diketahui'
                                        ; } @endphp Tingkat {{ $tingkat }} </td>
                    <td style="padding: 6px 10px; font-weight: bold; color: #333;">Program Studi</td>
                    <td style="padding: 6px 10px; color: #555;">: {{ $mahasiswa->programStudi->nama }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Table Section -->
    <table>
        <thead>
            <tr style="background-color: {{ $headerColor }}; color: {{ $textColor }};">
                <th rowspan="2" style="padding: 8px;">No</th>
                <th rowspan="2" style="padding: 8px;">Kode MK</th>
                <th rowspan="2" style="padding: 8px; text-align: center;">Mata Kuliah</th>
                <th rowspan="2" style="padding: 8px;">SKS</th>
                <th colspan="2" style="padding: 8px;">Nilai</th>
                <th rowspan="2" style="padding: 8px;">Total Nilai<br>(SKS x AM)</th>
            </tr>
            <tr style="background-color: {{ $headerColor }}; color: {{ $textColor }};">
                <th style="padding: 8px;">AM</th>
                <th style="padding: 8px;">HM</th>
            </tr>
        </thead>
        <tbody>
            @php
            // Function to calculate grade weight
            function calculateWeight($grade) {
            return match ($grade) {
            'A' => 4.00,
            'AB' => 3.75,
            'BA' => 3.50,
            'B' => 3.00,
            'BC' => 2.75,
            'C' => 2.00,
            'D' => 1.00,
            'E' => 0,
            default => 0,
            };
            }
            @endphp
            @forelse ($khs as $index => $item)
            @php
            $bobot = calculateWeight($item->khs);
            $bobot2 = $item->kurikulum->mataKuliah->sks * $bobot;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->kurikulum->mataKuliah->matakuliah_id }}</td>
                <td>{{ $item->kurikulum->mataKuliah->nama }}</td>
                <td>{{ $item->kurikulum->mataKuliah->sks }}</td>
                <td>{{ $item->khs }}</td>
                <td>{{ $bobot }}</td>
                <td>{{ $bobot2 }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada mata kuliah yang diambil.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            @php
            $totalSks = $khs->sum(fn($item) => $item->kurikulum->mataKuliah->sks);
            $totalSksAm = $khs->sum(fn($item) => $item->kurikulum->mataKuliah->sks * calculateWeight($item->khs));
            $ips = $totalSks > 0 ? $totalSksAm / $totalSks : 0;
            @endphp
            <tr>
                <th colspan="6" style="text-align: center;">Jumlah SKS</th>
                <th>{{ $totalSks }}</th>
            </tr>
            <tr>
                <th colspan="6" style="text-align: center;">Jumlah SKS x AM</th>
                <th>{{ $totalSksAm }}</th>
            </tr>
            <tr>
                <th colspan="6" style="text-align: center;">IPS (Indeks Per Semester)</th>
                <th>{{ number_format($ips, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Footer Section -->
    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d-m-Y H:i') }}</p>
    </div>
</body>

</html>