<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Aktivitas Pengguna</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2, .header h3 { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #000; padding: 6px; }
        table th { background-color: #f2f2f2; text-align: left; }
        .text-center { text-align: center; }
        .badge { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Aktivitas Sistem (Activity Logs)</h2>
        <h3>Tanggal Cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="40px">No</th>
                <th width="150px">User Info</th>
                <th width="80px">Role</th>
                <th width="120px">Aktivitas</th>
                <th>Deskripsi</th>
                <th width="100px">IP Address</th>
                <th width="120px">Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $key => $log)
            <tr>
                <td class="text-center">{{ $key + 1 }}</td>
                <td>
                    <b>{{ optional($log->user)->name ?? optional($log->user)->nama ?? 'User tidak ditemukan' }}</b><br>
                    <small>ID: {{ $log->user_id }}</small>
                </td>
                <td>{{ ucfirst($log->user_type) }}</td>
                <td>{{ str_replace('_', ' ', ucwords($log->aktivitas, '_')) }}</td>
                <td>{{ $log->deskripsi }}</td>
                <td>{{ $log->ip_address ?? '-' }}</td>
                <td>{{ $log->created_at->translatedFormat('d M Y, H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada data aktivitas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
