<table>
    <thead>
        <tr>
            <th>NIM</th>
            <th>Nama</th>
            <th>Program Studi</th>
            <th>Tahun Masuk</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($mahasiswa as $m)
        <tr>
            <td>{{ $m->nim }}</td>
            <td>{{ $m->nama }}</td>
            <td>{{ $m->programStudi->nama ?? '-' }}</td>
            <td>{{ $m->tahun_masuk }}</td>
            <td>{{ $m->status_mhs }}</td>
        </tr>
        @endforeach
    </tbody>
</table>