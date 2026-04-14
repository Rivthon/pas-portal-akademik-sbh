# Penyelesaian Fitur Export Laporan Nilai

Sistem Export Nilai untuk Excel dan PDF telah sukses dipersiapkan dan dieksekusi.

## Yang Telah Dikerjakan

1. **Frontend Event Binding**
   - Menambahkan event listener JavaScript pada tombol **Excel** (`#btn-export-excel`) dan **PDF** (`#btn-export-pdf`) di halaman `resources/views/input-nilai/index.blade.php`.
   - JavaScript ini akan secara otomatis membaca filter `Tahun Ajaran` dan `Mata Kuliah` yang saat ini sedang aktif, lalu memvalidasi ketersediaannya sebelum memanggil *route* export dengan `window.open()`.
   - Terdapat penanganan *Error/Validation* dimana sistem akan memberikan alert `Swal.fire` (jika tersedia) apabila pengguna mengklik tombol Export tanpa memilih mata kuliah atau tahun ajaran.

2. **Pengecekan Komponen (Sudah Tersedia Sebelumnya)**
   - **Route Layer**: Endpoint dynamic dinamis berupa `GET /admin/mahasiswa/input-nilai/export/{mataKuliahId}/{tahunAjaranId}/{format}` sudah terdaftar dengan sempurna.
   - **Controller**: Fungsi `InputNilaiController@export()` telah diprogram dengan optimal, memadukan perhitungan bobot secara modular seperti komponen UTS, UAS, Praktik, Absen dan Tugas yang ada di `getMahasiswa()`.
   - **Export Class**: Kelas `App\Exports\NilaiMahasiswaExport` yang bertugas mendaur ulang view menjadi raw Excel sudah *ready*.
   - **Report Template**: View di `resources/views/input-nilai/export.blade.php` telah dirancang sedemikian rupa menggunakan CSS inline sederhana, demi memastikan kompabilitas penuh saat di-*render* menjadi PDF via DOMPDF dan dibaca menjadi Sheet Excel oleh package Maatwebsite.

## Cara Pengujian (Manual Verification)

1. Masuk ke halaman **Input Nilai**.
2. Pilih filter **Tahun Ajaran**, **Program Studi**, lalu pilih **Mata Kuliah** yang diinginkan.
3. Setelah data mahasiswa dan tabel nilai tampil, tombol **Excel** dan **PDF** di bagian kanan atas pojok tabel harus muncul.
4. Klik tombol **Excel** — sistem akan membuahkan unduhan file `.xlsx` dengan nama mata kuliah (contoh: `Laporan_Nilai_Dasar_Pemrograman.xlsx`).
5. Klik tombol **PDF** — sistem akan membuahkan unduhan file `.pdf` yang berisi rekapitulasi nilai PDF *Landscape* dengan kop institusi dan kolom Tanda Tangan.
