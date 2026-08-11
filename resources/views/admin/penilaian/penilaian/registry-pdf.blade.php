<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Evaluation Registry</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header img {
            max-height: 80px;
            margin-bottom: 5px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .data-table th {
            background-color: #f4f4f4;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
            color: #fff;
        }

        .bg-success {
            background-color: #1cc88a;
        }

        .bg-danger {
            background-color: #e74a3b;
        }

        .bg-secondary {
            background-color: #858796;
        }
    </style>
</head>

<body>

    <div class="header">
        @if(isset($logoBase64))
            <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo">
        @endif
        <div class="title">LEMBAR REKAPITULASI EVALUASI DOSEN MENGAJAR (EDOM)</div>
        <div style="font-size: 12px;">{{ $settings->nama_institusi ?? 'Institusi Pendidikan' }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Tahun Ajaran</strong></td>
            <td width="2%">:</td>
            <td width="33%">{{ $tahunAjaranNama }}</td>
            <td width="15%"><strong>Dicetak Pada</strong></td>
            <td width="2%">:</td>
            <td width="33%">{{ date('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Program Studi</strong></td>
            <td>:</td>
            <td>{{ $programStudiNama }}</td>
            <td><strong>Total Data</strong></td>
            <td>:</td>
            <td>{{ $assignments->count() }} Record(s)</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="25%">Nama Dosen</th>
                <th width="12%">NIDN / NIP</th>
                <th width="25%">Mata Kuliah</th>
                <th width="5%" class="text-center">SKS</th>
                <th width="5%" class="text-center">SMT</th>
                <th width="10%" class="text-center">Metode</th>
                <th width="13%" class="text-center">Status EDOM</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->dosen->nama ?? '-' }}</td>
                    <td>{{ $row->dosen->nidn ?? '-' }}</td>
                    <td>{{ $row->kurikulum->mataKuliah->nama ?? '-' }}</td>
                    <td class="text-center">{{ $row->kurikulum->mataKuliah->sks ?? '-' }}</td>
                    <td class="text-center">{{ $row->kurikulum->mataKuliah->smt ?? '-' }}</td>
                    <td class="text-center">
                        {{ ucfirst($row->jenis_dosen) }}
                    </td>
                    <td class="text-center">
                        @if($row->status_edom)
                            <span class="badge bg-success">SUDAH</span>
                        @else
                            <span class="badge bg-danger">BELUM</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data ditemukan untuk kriteria ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>