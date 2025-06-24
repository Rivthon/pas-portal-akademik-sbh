<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip Akademik / Academic Transcript </title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10px;
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

        .h3 {
            font-size: 15px;
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
    </style>
</head>

<body>
    <div class="watermark"></div>

    <div class="header">
        <img src="{{ public_path('assets/img/header/header_kop_utama1.jpg') }}" alt="Header Image"
            style="width: 100%; height: auto; margin-bottom: 20px;">
        <h2 style="text-align: center; margin: 0;">Transkrip Akademik / Academic Transcript (Sementara)</h2>
        <h5 style="text-align: center; margin: 0;">Tahun Ajaran / Academic Year {{ $ta->nama }} ( {{ $ta->semester }})
        </h5>
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
            <tr style="background-color: {{ $headerColor }}; color: {{ $textColor }}; text-align: center;">
                <th rowspan="2"
                    style="padding: 10px; border: 1px solid #000; vertical-align: middle; text-align: center;">No</th>
                <th rowspan="2"
                    style="padding: 10px; border: 1px solid #000; vertical-align: middle; text-align: center;">Kode /
                    Code</th>
                <th rowspan="2"
                    style="padding: 10px; border: 1px solid #000; vertical-align: middle; text-align: center;">Mata
                    Kuliah / Course</th>
                <th rowspan="2"
                    style="padding: 10px; border: 1px solid #000; vertical-align: middle; text-align: center;">SKS /
                    Credit</th>
                <th colspan="2"
                    style="padding: 10px; border: 1px solid #000; vertical-align: middle; text-align: center;">Nilai /
                    Grade</th>
                <th rowspan="2"
                    style="padding: 10px; border: 1px solid #000; vertical-align: middle; text-align: center;">
                    SKS x AM / Point</th>
            </tr>
            <tr style="background-color: {{ $headerColor }}; color: {{ $textColor }}; text-align: center;">
                <th style="padding: 10px; border: 1px solid #000;">Huruf / Symbol</th>
                <th style="padding: 10px; border: 1px solid #000;">Angka / Score</th>
            </tr>
        </thead>
        </thead>
        @php
        // Function untuk menghitung bobot nilai
        function calculateWeight($grade) {
        $gradeWeights = [
        'A' => 4.00, 'AB' => 3.75, 'BA' => 3.50, 'B' => 3.00,
        'BC' => 2.75, 'C' => 2.00, 'D' => 1.00, 'E' => 0
        ];
        return $gradeWeights[$grade] ?? 0;
        }

        // Filter data hanya untuk nilai yang tidak kosong
        $filteredKhs = collect($khs)->filter(fn($item) => !empty($item->khs));

        // Menghitung Total SKS & Bobot
        $totalSks = $filteredKhs->sum(fn($item) => optional($item->kurikulum->mataKuliah)->sks ?? 0);
        $totalBobot = $filteredKhs->sum(fn($item) => (optional($item->kurikulum->mataKuliah)->sks ?? 0) *
        calculateWeight($item->khs));

        // Menghitung IPS
        $ipk = $totalSks ? $totalBobot / $totalSks : 0;
        @endphp

        <tbody>
            @forelse ($filteredKhs as $index => $item)
            @php
            $mataKuliah = optional($item->kurikulum->mataKuliah);
            $sks = $mataKuliah->sks ?? 0;
            $bobot = calculateWeight($item->khs);
            $bobotTotal = $sks * $bobot;
            @endphp
            <tr style="text-align: center;">
                <td style="padding: 8px; border: 1px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $mataKuliah->matakuliah_id ?? '-' }}</td>
                <td style="padding: 8px; border: 1px solid #000; text-align: left;">{{ $mataKuliah->nama ?? 'Tidak ada
                    data' }}
                </td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $sks }}</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $item->khs }}</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ number_format($bobot, 2) }}</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ number_format($bobotTotal, 2) }}</td>
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
            <tr style="border-top: 2px solid black; font-weight: bold;">
                <td colspan="3" style="text-align: left; padding: 10px;">Total</td>
                <td style="text-align: center; padding: 10px;">{{ $totalSks }}</td>
                <td colspan="2" style="text-align: center; padding: 10px;"></td>
                <td style="text-align: right; padding: 10px;">{{ number_format($totalBobot, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Tabel IPS, IPK, dan Predikat -->
    <table style="width: 100%; border-collapse: collapse; border: none; font-size: 12px;">
        <tr>
            <td colspan="3" style="text-align: left; border: none;"><strong>IPK (Indeks Prestasi Kumulatif)</strong>
            </td>
            <td colspan="3" style="text-align: left; border: none;">: <strong>{{ number_format($ipk, 2) }}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left; border: none;"><strong>Predikat</strong></td>
            <td colspan="3" style="text-align: left; border: none;">: <strong>{{ getPredikat($ipk) }}</strong></td>
        </tr>
    </table>
    <div class="info">
        <h3><strong>Keterangan / Information: <br>
                Indeks Prestasi Kumulatif / Grade Point Average</strong></h3>

        <ul style="list-style-type: none; padding: 0; font-size: 12px;">
            <li>0.00 - 1.99 &nbsp; <strong>Gagal</strong> (<em>Fail</em>)</li>
            <li>2.00 - 2.75 &nbsp; <strong>Kurang Memuaskan</strong> (<em>Less Satisfactory</em>)</li>
            <li>2.76 - 3.00 &nbsp; <strong>Memuaskan</strong> (<em>Satisfactory</em>)</li>
            <li>3.01 - 3.50 &nbsp; <strong>Sangat Memuaskan</strong> (<em>Very Satisfactory</em>)</li>
            <li>3.51 - 4.00 &nbsp; <strong>Pujian</strong> (<em>Cum Laude</em>)</li>
        </ul>
    </div>
    @php
    function getPredikat($ipk) {
    return match (true) {
    $ipk >= 3.51 => 'Pujian (Cum Laude)',
    $ipk >= 3.01 => 'Sangat Memuaskan (Very Satisfactory)',
    $ipk >= 2.76 => 'Memuaskan (Satisfactory)',
    $ipk >= 2.00 => 'Kurang Memuaskan (Less Satisfactory)',
    default => 'Gagal (Fail)',
    };
    }
    @endphp
    <!-- Footer Section -->
    <style>
        .footer {
            width: 100%;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            border-top: 1px solid #ddd;
        }

        .verified {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            /* Rata kanan */
        }

        .verified img {
            width: 16px;
            height: 16px;
            margin-right: 4px;
        }
    </style>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d-m-Y H:i') }}</p>

        <div class="verified">
            <img src="{{ public_path('assets/img/header/verfikasi.png') }}" alt="Verified Icon">
            <span>Diverifikasi oleh BAAK STIKes Bogor Husada</span>
        </div>
    </div>
</body>

</html>