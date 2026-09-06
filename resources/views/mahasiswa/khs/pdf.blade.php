<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KHS Mahasiswa</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
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
        <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header Image"
            style="width: 100%; height: auto; margin-bottom: 20px;">
        <h1 style="text-align: center; margin: 0;">Kartu Hasil Studi</h1>
        <h3 style="text-align: center; margin: 5;">Tahun Ajaran : {{ $ta->nama }} - {{ $ta->semester }}</h3>
        <table class="info-table" style="margin-top: 20px;">
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
                        : {{ $semesterKhs ?? $mahasiswa->semester }} /
                        @php
                        $semester = (int) explode(',', (string) ($semesterKhs ?? $mahasiswa->semester))[0];
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
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="background-color: {{ $headerColor }}; color: {{ $textColor }}; text-align: center;">
                <th rowspan="2"
                    style="padding: 10px; border: 1px solid #000; vertical-align: middle; text-align: center;">No</th>
                <th rowspan="2"
                    style="padding: 10px; border: 1px solid #000; vertical-align: middle; text-align: center;">Kode</th>
                <th rowspan="2"
                    style="padding: 10px; border: 1px solid #000; vertical-align: middle; text-align: center;">Mata
                    Kuliah</th>
                <th rowspan="2"
                    style="padding: 10px; border: 1px solid #000; vertical-align: middle; text-align: center;">SKS</th>
                <th colspan="2"
                    style="padding: 10px; border: 1px solid #000; vertical-align: middle; text-align: center;">Nilai
                </th>
                <th rowspan="2"
                    style="padding: 10px; border: 1px solid #000; vertical-align: middle; text-align: center;">Total
                    Nilai (SKS x AM)</th>
            </tr>
            <tr style="background-color: {{ $headerColor }}; color: {{ $textColor }}; text-align: center;">
                <th style="padding: 10px; border: 1px solid #000;">HM</th>
                <th style="padding: 10px; border: 1px solid #000;">AM</th>
            </tr>
        </thead>
        <tbody>
            @php
            $calculateKhsWeight = fn ($grade) => match ($grade) {
            'A' => 4.00, 'AB' => 3.75, 'BA' => 3.50, 'B' => 3.00,
            'BC' => 2.75, 'C' => 2.00, 'D' => 1.00, 'E' => 0, default => 0,
            };

            $totalSks = $khs->sum(fn($item) => $item->kurikulum->mataKuliah->sks);
            $totalBobot = $khs->sum(fn($item) => $item->kurikulum->mataKuliah->sks * $calculateKhsWeight($item->khs));
            $ips = $totalSks ? $totalBobot / $totalSks : 0;
            @endphp

            @forelse ($khs as $index => $item)
            @php
            $bobot = $calculateKhsWeight($item->khs);
            $bobotTotal = $item->kurikulum->mataKuliah->sks * $bobot;
            @endphp
            <tr style="text-align: center;">
                <td style="padding: 8px; border: 1px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $item->kurikulum->mataKuliah->matakuliah_id }}</td>
                <td style="padding: 8px; border: 1px solid #000; text-align: left;">{{
                    $item->kurikulum->mataKuliah->nama }}
                </td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $item->kurikulum->mataKuliah->sks }}</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $item->khs }}</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ number_format($bobot, 2) }}</td>
                <td style="padding: 8px; border: 1px solid #000; text-align: center;">{{ number_format($bobotTotal, 2)
                    }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7"
                    style="padding: 10px; border: 1px solid #000; text-align: center; background-color: #f8f8f8;">
                    Tidak ada mata kuliah yang diambil.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f0f0;">
                <th colspan="6" style="text-align: left; padding: 10px;">Jumlah SKS</th>
                <th colspan="1" style="padding: 10px; text-align: center;">{{ $totalSks }}</th>
            </tr>
            <tr style="background-color: #f0f0f0;">
                <th colspan="6" style="text-align: left; padding: 10px;">Jumlah SKS x AM</th>
                <th colspan="1" style="padding: 10px; text-align: center;">{{ number_format($totalBobot, 2) }}</th>
            </tr>
            <tr style="background-color: #f0f0f0;">
                <th colspan="6" style="text-align: left; padding: 10px;">IPS (Indeks Prestasi Semester)</th>
                <th colspan="1" style="padding: 10px; text-align: center;">{{ number_format($ips, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Tabel IPS, IPK, dan Predikat -->
    <table style="width: 100%; margin-top: 20px; border-collapse: collapse; border: none;">
        {{-- <tr>
            <td colspan="3" style="text-align: left; padding: 10px; border: none;">IPS (Indeks Prestasi Semester)</td>
            <td colspan="4" style="text-align: center; padding: 10px; border: none;">{{ number_format($ips, 2) }}</td>
        </tr> --}}
        <tr>
            <td style="text-align: left; padding: 10px 8px 10px 0; border: none; width: 32%;">IPK (Indeks Prestasi
                Kumulatif)</td>
            <td style="text-align: left; padding: 10px 0 10px 4px; border: none; width: 5%;">:</td>
            <td style="text-align: left; padding: 10px 0 10px 4px; border: none; width: 25%;">{{ number_format($ipk, 2)
                }}</td>
            <td style="border: none;" colspan="1"></td>
        </tr>
        <tr></tr>
        <td style="text-align: left; padding: 10px 8px 10px 0; border: none;">Predikat</td>
        <td style="text-align: left; padding: 10px 0 10px 4px; border: none;">:</td>
        <td style="text-align: left; padding: 10px 0 10px 4px; border: none;">{{ getPredikat($ipk) }}</td>
        <td style="border: none;" colspan="2"></td>
        </tr>
    </table>

    @php
    function getPredikat($ipk) {
    return match (true) {
    $ipk >= 3.51 => 'Cumlaude',
    $ipk >= 3.01 => 'Sangat Memuaskan',
    $ipk >= 2.76 => 'Memuaskan',
    $ipk >= 2.00 => 'Cukup',
    default => 'Kurang',
    };
    }
    @endphp
    <!-- Footer Section -->
    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d-m-Y H:i') }}</p>
    </div>
</body>

</html>
