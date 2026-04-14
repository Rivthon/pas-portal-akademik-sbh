# Implementasi Export Laporan Nilai Mahasiswa

Berfungsi untuk memfasilitasi pencetakan dan ekspor rekapitulasi nilai mahasiswa berdasarkan komponen (UTS, UAS, Tugas, Absen, Praktik) beserta bobot kustomisasi yang dikonfigurasi pada tampilan `input-nilai`.

## User Review Required

> [!IMPORTANT]
> **Format Export**: Di dalam proyek ini telah terpasang *library* `maatwebsite/excel` (untuk Excel) dan `barryvdh/laravel-dompdf` (untuk PDF). 
> Saya bermaksud untuk memberikan tombol **"Export PDF"** dan **"Export Excel"** sekaligus di halaman *Input Nilai* agar fleksibel. Apakah Bapak setuju dengan 2 format ini, atau ada format khusus yang lebih diutamakan?

> [!NOTE]
> Desain laporan yang diekspor akan menggunakan format tabel resmi (mengandung nama Mahasiswa, NIM, Rincian Nilai per Komponen, Absolute, dan Huruf Mutu) beserta info *header* berupa Nama Mata Kuliah dan Tahun Ajaran.

## Proposed Changes

### 1. Route Layer
Akan ditambahkan dua endpoint baru (atau satu dinamis) untuk *handle* perintah *export*.

#### [MODIFY] `routes/web.php`
- Menambahkan route baru `GET /admin/nilai/export/{matakuliah}/{ta}/{format}` yang terhubung ke `InputNilaiController@export`.

### 2. View Layer (Frontend UI)
Tombol export akan dimunculkan secara dinamis saat tabel nilai mahasiswa berhasil dimuat, sejajar dengan tombol "Simpan Nilai" atau di samping info total mahasiswa.

#### [MODIFY] `resources/views/layouts/master.blade.php`
- Menambahkan elemen tombol export Excel dan PDF.
- Memperbarui file JavaScript agar tombol-tombol JS ini otomatis memiliki `href` menuju route export yang benar berdasarkan *Mata Kuliah* dan *Tahun Ajaran* yang sedang dipilih.

### 3. Controller Layer
Logika untuk mengambil data, memodelkan *Grading Tiered Bobot*, dan mengeksekusi *render* file download.

#### [MODIFY] `app/Http/Controllers/InputNilaiController.php`
- Membuat method `export($matakuliahId, $tahunAjaranId, $format)`.
- Re-use logika pengecekan bobot dan data mahasiswa dari `getMahasiswa`.
- Mereturn tampilan langsung via `DomPDF` PDF atau `maatwebsite/excel`.

#### [NEW] `resources/views/input-nilai/export.blade.php` (Jika dibutuhkan)
- Sebuah desain blade sederhana (*printable*) berisikan tabel nilai khusus untuk diekspor menjadi format laporan resmi.

## Verification Plan

### Manual Verification
1. Masuk ke halaman **Input Nilai** dan pilih Matakuliah yang aktif.
2. Pastikan tabel ter-*load* otomatis bersamaan dengan interaksi memunculkan tombol **Export Excel** dan **Export PDF**.
3. Klik kedua tombol dan pastikan file berektensi `.xlsx` dan `.pdf` berhasil terunduh dengan data nilai dan perhitungan bobot yang sinkron 100% dengan tampilan layar.
