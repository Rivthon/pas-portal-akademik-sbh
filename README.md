# 🎓 Portal Akademik SBH

**Portal Akademik SBH (PAS)** adalah sistem informasi akademik terpadu milik **Sekolah Tinggi Ilmu Kesehatan Bogor Husada (STIKes Bogor Husada)**.

Aplikasi ini dirancang untuk mengintegrasikan berbagai proses akademik dalam satu platform, mulai dari **administrasi akademik, KRS, jadwal perkuliahan, absensi, penilaian, LMS, evaluasi dosen, hingga pengelolaan data akademik dan keuangan mahasiswa**.

🌐 **Production:** https://pas.sbh.ac.id

---

## 📋 Daftar Isi

* [Tentang Aplikasi](#-tentang-aplikasi)
* [Fitur Utama](#-fitur-utama)

  * [Mahasiswa](#mahasiswa)
  * [Dosen](#dosen)
  * [Administrator](#administrator)
* [Teknologi](#-teknologi)
* [Persyaratan Sistem](#-persyaratan-sistem)
* [Instalasi Lokal](#-instalasi-lokal)
* [Menjalankan Pengujian](#-menjalankan-pengujian)
* [Deployment Produksi](#-deployment-produksi)
* [Struktur Akses](#-struktur-akses)
* [Penyimpanan File](#-penyimpanan-file)
* [Keamanan](#-keamanan)
* [Kontribusi](#-kontribusi)
* [Lisensi](#-lisensi)

---

## 📖 Tentang Aplikasi

**Portal Akademik SBH** dikembangkan untuk membantu sivitas akademika mengelola aktivitas akademik secara terintegrasi, terstruktur, dan terdokumentasi.

Sistem menyediakan beberapa area akses berdasarkan peran pengguna:

* 👨‍🎓 **Mahasiswa** — mengelola aktivitas akademik pribadi, KRS, jadwal, absensi, nilai, LMS, dan dokumen akademik.
* 👨‍🏫 **Dosen** — mengelola kegiatan pembelajaran, absensi, RPS, materi, tugas, kuis, dan penilaian.
* 🛠️ **Administrator** — mengelola master data, kurikulum, jadwal, pengguna, keuangan, laporan, serta konfigurasi sistem.

Dengan pendekatan tersebut, proses akademik yang sebelumnya tersebar dapat dikelola melalui satu sistem terpusat.

---

# 🚀 Fitur Utama

## 👨‍🎓 Mahasiswa

### Akademik

* Dashboard mahasiswa
* Profil mahasiswa
* Kartu Rencana Studi (**KRS**)
* Status persetujuan KRS
* Arsip KRS
* Kartu Hasil Studi (**KHS**)
* Nilai UTS
* Nilai UAS
* Nilai UAP
* Nilai akhir
* Jadwal kuliah
* Jadwal praktik
* Jadwal UTS
* Jadwal UAS
* Jadwal UAP

### Kehadiran

* Absensi perkuliahan teori
* Riwayat absensi
* Riwayat absensi praktik

### Learning Management System

* Materi pembelajaran
* Tugas
* Pengumpulan tugas
* Kuis
* Kalender pembelajaran
* Rekap nilai LMS

### Layanan Akademik

* Rencana Pembelajaran Semester (**RPS**)
* Evaluasi Dosen oleh Mahasiswa (**EDOM**)
* Pengajuan transkrip
* Permintaan perubahan data
* Pencatatan aktivitas dan prestasi untuk **SKPI**
* Cetak dokumen akademik dalam format PDF

---

## 👨‍🏫 Dosen

### Perkuliahan

* Dashboard dosen
* Profil dosen
* Jadwal mengajar teori
* Jadwal mengajar praktik
* Pengelolaan pertemuan
* Pengelolaan absensi mahasiswa

### Pembelajaran

* Pengelolaan RPS
* Materi pembelajaran
* Tugas
* Pengumpulan tugas mahasiswa
* Penilaian tugas
* Kuis

### Penilaian

* Input nilai mahasiswa
* Rekap nilai
* Pengelolaan nilai akhir

### Administrasi

* Persetujuan KRS mahasiswa
* Rekap kegiatan pembelajaran
* Laporan kegiatan mengajar

---

## 🛠️ Administrator

### Manajemen Pengguna

* Manajemen pengguna
* Role dan permission
* Manajemen akun mahasiswa
* Manajemen akun dosen
* Pengaturan hak akses

### Master Data

* Mahasiswa
* Dosen
* Program studi
* Mata kuliah
* Ruangan
* Tahun ajaran
* Data pendukung akademik lainnya

### Kurikulum & Perkuliahan

* Pengelolaan kurikulum
* Penugasan dosen
* Pembuatan jadwal kuliah
* Jadwal praktik
* Jadwal UTS
* Jadwal UAS
* Jadwal UAP
* Kalender akademik

### Modul Akademik

* KRS
* KHS dan nilai
* EDOM
* RPS
* LMS
* BAP pengajaran

### Keuangan

* Pengelolaan tarif
* Tenor pembayaran
* Tagihan mahasiswa
* Pembayaran mahasiswa

### Data & Laporan

* Import data Excel
* Export data Excel
* Laporan PDF
* Activity log
* Impersonasi mahasiswa secara terbatas

### Konfigurasi Sistem

* Identitas portal
* Logo
* Favicon
* Konfigurasi aplikasi

---

# 🧰 Teknologi

Portal Akademik SBH dibangun menggunakan teknologi berikut:

| Teknologi       | Versi / Keterangan        |
| --------------- | ------------------------- |
| PHP             | 8.3+                      |
| Laravel         | 13                        |
| Database        | MySQL 8                   |
| Template Engine | Blade                     |
| CSS Framework   | Bootstrap & Tailwind CSS  |
| Build Tool      | Vite                      |
| Authorization   | Spatie Laravel Permission |
| PDF             | Laravel DomPDF            |
| Excel           | Laravel Excel             |
| Data Table      | Yajra DataTables          |
| QR Code         | Simple QR Code            |
| Chart           | ApexCharts                |

---

# 💻 Persyaratan Sistem

Sebelum menjalankan aplikasi secara lokal, pastikan environment telah menyediakan:

* **PHP 8.3 atau lebih baru**
* Composer
* MySQL 8 atau MariaDB yang kompatibel
* Node.js
* npm
* Apache atau Nginx
* PHP extensions yang dibutuhkan oleh Laravel

Untuk memeriksa versi PHP:

```bash
php -v
```

Memeriksa Composer:

```bash
composer -V
```

Memeriksa Node.js dan npm:

```bash
node -v
npm -v
```

---

# 📦 Instalasi Lokal

## 1. Clone Repository

Clone repository kemudian masuk ke direktori project:

```bash
git clone <repository-url>
cd <repository-directory>
```

---

## 2. Install Dependency

Install dependency backend:

```bash
composer install
```

Kemudian install dependency frontend:

```bash
npm install
```

---

## 3. Konfigurasi Environment

Buat file `.env` berdasarkan konfigurasi environment yang tersedia:

```bash
cp .env.production.example .env
```

Kemudian generate application key:

```bash
php artisan key:generate
```

Sesuaikan konfigurasi berikut pada `.env`:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

> Sesuaikan konfigurasi database dengan environment lokal masing-masing.

---

## 4. Setup Database

Pastikan database sudah dibuat, kemudian jalankan migration:

```bash
php artisan migrate
```

Jika project menggunakan seeder dan data awal tersedia, jalankan:

```bash
php artisan db:seed
```

> Jangan menjalankan seeder yang membuat akun administrator produksi tanpa memastikan konfigurasi credential dan environment sudah aman.

---

## 5. Setup Storage

Buat symbolic link untuk file publik:

```bash
php artisan storage:link
```

Symbolic link ini digunakan agar file seperti:

* Logo
* Favicon
* Avatar
* Dokumen RPS
* Materi LMS
* File tugas
* Dokumen akademik

dapat diakses melalui URL:

```text
/storage
```

---

## 6. Build Frontend

Untuk development/build production:

```bash
npm run build
```

Jika membutuhkan hot reload selama pengembangan:

```bash
npm run dev
```

---

## 7. Jalankan Aplikasi

Untuk menjalankan server Laravel secara lokal:

```bash
php artisan serve
```

Secara default aplikasi dapat diakses melalui:

```text
http://127.0.0.1:8000
```

---

# 🧪 Menjalankan Pengujian

Jalankan automated test dengan:

```bash
php artisan test
```

Untuk melakukan pemeriksaan format kode menggunakan Laravel Pint:

```bash
./vendor/bin/pint --test
```

Jika diperlukan untuk memperbaiki format kode secara otomatis:

```bash
./vendor/bin/pint
```

---

# 🚀 Deployment Produksi

Untuk environment production, gunakan konfigurasi yang aman.

Contoh konfigurasi `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pas.sbh.ac.id

SESSION_SECURE_COOKIE=true
```

Setelah source code dan dependency tersedia di server, jalankan:

```bash
composer install --no-dev --optimize-autoloader
```

Kemudian:

```bash
php artisan migrate --force
php artisan storage:link
npm run build
php artisan optimize
```

Jika diperlukan, cache konfigurasi dapat dibangun dengan:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🌐 Web Server

Pastikan document root domain diarahkan ke folder:

```text
/public
```

**Bukan** ke root project Laravel.

Contoh struktur:

```text
portal-akademik/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          ← Document Root
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
└── artisan
```

Direktori berikut harus dapat ditulis oleh web server:

```text
storage/
bootstrap/cache/
```

---

## ⚠️ Backup Sebelum Migration

Sebelum melakukan migration pada server production:

1. Backup database terlebih dahulu.
2. Pastikan migration telah diuji.
3. Gunakan salinan database terbaru untuk testing.
4. Jalankan migration menggunakan:

```bash
php artisan migrate --force
```

> **Jangan menjalankan migration production tanpa backup database.**

---

# 🔐 Struktur Akses

Portal menggunakan area login terpisah berdasarkan jenis pengguna.

| Pengguna          | URL Login          |
| ----------------- | ------------------ |
| 👨‍🎓 Mahasiswa   | `/mahasiswa/login` |
| 👨‍🏫 Dosen       | `/dosen/login`     |
| 🛠️ Administrator | `/admin/login`     |

Setiap area dilindungi oleh mekanisme:

* Authentication
* Role
* Permission
* Middleware
* Authorization

sehingga pengguna hanya dapat mengakses fitur yang sesuai dengan hak aksesnya.

---

# 📁 Penyimpanan File

File yang diunggah oleh aplikasi disimpan pada:

```text
storage/app/public
```

Kemudian dipublikasikan melalui symbolic link:

```text
public/storage
```

Jika file menghasilkan **404 Not Found**, periksa symbolic link:

```bash
ls -ld public/storage
```

Periksa target symbolic link:

```bash
readlink -f public/storage
```

Jika diperlukan, buat ulang symbolic link:

```bash
php artisan storage:link
```

Target yang benar:

```text
public/storage
        ↓
storage/app/public
```

`public/storage` harus berupa **symbolic link**, bukan direktori kosong biasa.

---

# 🔒 Keamanan

Beberapa praktik keamanan yang wajib diperhatikan:

* Jangan commit file `.env` ke repository.
* Jangan menyimpan credential database di source code.
* Gunakan `APP_DEBUG=false` pada production.
* Gunakan HTTPS pada production.
* Aktifkan secure session cookie.
* Gunakan password yang kuat untuk akun administrator.
* Batasi permission direktori server.
* Gunakan database user dengan privilege sesuai kebutuhan.
* Jangan menyimpan API key atau secret langsung di repository.
* Jangan menjalankan seeder akun administrator production secara sembarangan.
* Selalu lakukan backup database sebelum perubahan struktur database.

Pastikan file berikut **tidak pernah masuk ke repository**:

```text
.env
.env.production
.env.local
```

---

# 🤝 Kontribusi

Pengembangan dilakukan menggunakan branch terpisah untuk menjaga stabilitas branch utama.

Alur pengembangan yang disarankan:

```text
Create Branch
     ↓
Development
     ↓
Testing
     ↓
Code Review
     ↓
Merge
     ↓
Deployment
```

Sebelum perubahan digabungkan, pastikan:

* Migration dapat dijalankan dengan aman.
* Automated test berhasil.
* Tidak terdapat error pada fitur terkait.
* Tidak terdapat credential atau data sensitif dalam commit.
* Perubahan tidak merusak fitur yang sudah berjalan.
* Dokumentasi diperbarui jika terdapat perubahan konfigurasi atau fitur.

---

# 📄 Lisensi

Project ini dikembangkan untuk mendukung layanan akademik digital di:

**Sekolah Tinggi Ilmu Kesehatan Bogor Husada**

Penggunaan, distribusi, dan modifikasi source code mengikuti kebijakan dan ketentuan yang berlaku di lingkungan institusi.

---

## 🏫 Sekolah Tinggi Ilmu Kesehatan Bogor Husada

**Portal Akademik SBH**

> *Integrated Academic Information System for STIKes Bogor Husada*

🌐 **Website:** https://pas.sbh.ac.id

---

**Developed for STIKes Bogor Husada**
