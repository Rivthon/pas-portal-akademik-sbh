<table>
    <thead>
        <tr><th colspan="{{ $tugasList->count() + $quizList->count() + 4 }}"><strong>GRADEBOOK LMS</strong></th></tr>
        <tr><th>Mata Kuliah</th><td colspan="{{ $tugasList->count() + $quizList->count() + 3 }}">{{ $jadwal->kurikulum?->mataKuliah?->nama }}</td></tr>
        <tr><th>Kode</th><td colspan="{{ $tugasList->count() + $quizList->count() + 3 }}">{{ $jadwal->kurikulum?->mataKuliah?->matakuliah_id }}</td></tr>
        <tr><th>Program Studi</th><td colspan="{{ $tugasList->count() + $quizList->count() + 3 }}">{{ $jadwal->kurikulum?->programStudi?->nama }}</td></tr>
        <tr><th>Kelas</th><td colspan="{{ $tugasList->count() + $quizList->count() + 3 }}">{{ strtoupper($jadwal->jenis_kelas ?? '-') }}</td></tr>
        <tr></tr>
        <tr>
            <th>No</th><th>NIM</th><th>Nama Mahasiswa</th>
            @foreach($tugasList as $tugas)
                <th>{{ $tugas->judul }} (Maks. {{ $tugas->nilai_maksimal }})</th>
            @endforeach
            @foreach($quizList as $quiz)
                <th>Quiz: {{ $quiz->judul }} (Maks. {{ number_format($quiz->nilai_maksimal_gradebook, 0) }})</th>
            @endforeach
            <th>Total ({{ number_format($totalMaksimal, 0) }})</th><th>Persentase</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rekap as $data)
            <tr>
                <td>{{ $loop->iteration }}</td><td>{{ $data->mahasiswa->nim ?? '-' }}</td><td>{{ $data->mahasiswa->nama ?? '-' }}</td>
                @foreach($tugasList as $tugas)
                    @php($pengumpulan = $data->nilai_per_tugas[$tugas->tugas_id] ?? null)
                    <td>{{ $pengumpulan?->nilai ?? 0 }}</td>
                @endforeach
                @foreach($quizList as $quiz)
                    @php($attemptQuiz = $data->nilai_per_quiz[$quiz->quiz_id] ?? null)
                    <td>{{ $attemptQuiz?->status === 'graded' ? $attemptQuiz->nilai_total : 0 }}</td>
                @endforeach
                <td>{{ $data->nilai_diperoleh }}</td><td>{{ $data->persentase }}%</td>
            </tr>
        @endforeach
    </tbody>
</table>
