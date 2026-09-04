<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gradebook {{ $jadwal->kurikulum?->mataKuliah?->nama }}</title>
    <style>
        body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#222}
        h2{margin:0 0 4px;text-align:center}.meta{text-align:center;margin-bottom:14px;color:#555}
        table{width:100%;border-collapse:collapse}th,td{border:1px solid #bbb;padding:5px}
        th{background:#696cff;color:#fff;text-align:center}.left{text-align:left}.center{text-align:center}
        tfoot td{font-weight:bold;background:#f1f2f6}
    </style>
</head>
<body>
    <h2>GRADEBOOK LMS</h2>
    <div class="meta">
        {{ $jadwal->kurikulum?->mataKuliah?->matakuliah_id }} — {{ $jadwal->kurikulum?->mataKuliah?->nama }}<br>
        {{ $jadwal->kurikulum?->programStudi?->nama }} | Kelas {{ jenis_kelas_label($jadwal->jenis_kelas ?? '-') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>No</th><th>NIM</th><th>Nama Mahasiswa</th>
                @foreach($tugasList as $tugas)
                    <th>{{ $tugas->judul }}<br>Maks. {{ $tugas->nilai_maksimal }}</th>
                @endforeach
                @foreach($quizList as $quiz)
                    <th>Quiz: {{ $quiz->judul }}<br>Maks. {{ number_format($quiz->nilai_maksimal_gradebook, 0) }}</th>
                @endforeach
                <th>Total<br>{{ number_format($totalMaksimal, 0) }}</th><th>%</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekap as $data)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td><td>{{ $data->mahasiswa->nim ?? '-' }}</td><td class="left">{{ $data->mahasiswa->nama ?? '-' }}</td>
                    @foreach($tugasList as $tugas)
                        @php($pengumpulan = $data->nilai_per_tugas[$tugas->tugas_id] ?? null)
                        <td class="center">{{ $pengumpulan?->nilai ?? 0 }}</td>
                    @endforeach
                    @foreach($quizList as $quiz)
                        @php($attemptQuiz = $data->nilai_per_quiz[$quiz->quiz_id] ?? null)
                        <td class="center">{{ $attemptQuiz?->status === 'graded' ? number_format($attemptQuiz->nilai_total, 0) : 0 }}</td>
                    @endforeach
                    <td class="center">{{ number_format($data->nilai_diperoleh, 0) }}</td><td class="center">{{ $data->persentase }}%</td>
                </tr>
            @empty
                <tr><td colspan="{{ $tugasList->count() + $quizList->count() + 5 }}" class="center">Belum ada mahasiswa.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><td colspan="{{ $tugasList->count() + $quizList->count() + 4 }}" class="left">Rata-rata kelas</td><td class="center">{{ $rataRata }}%</td></tr></tfoot>
    </table>
</body>
</html>
