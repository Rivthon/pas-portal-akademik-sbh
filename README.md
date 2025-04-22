# 🌟 Absensi Kampus

Selamat datang di **Absensi Kampus** – solusi cerdas untuk mengelola kehadiran mahasiswa!  
Aplikasi ini dirancang khusus untuk memenuhi kebutuhan kampus dalam mencatat kehadiran, manajemen pengguna, dan pembuatan laporan dengan teknologi terkini menggunakan **Laravel 11**.

---
# 📚 Aplikasi Absensi Mahasiswa

Aplikasi Absensi Mahasiswa adalah solusi digital untuk manajemen absensi di lingkungan akademik. Dengan fitur lengkap dan antarmuka yang ramah pengguna, aplikasi ini dirancang untuk memudahkan pengelolaan kehadiran mahasiswa serta administrasi kampus.

---

## 🚀 Fitur Utama

✨ **Manajemen Pengguna**  
Kelola pengguna dengan mudah: tambahkan, ubah, atau hapus pengguna berdasarkan peran seperti Admin, Dosen, dan Mahasiswa.  

🔒 **Manajemen Role & Hak Akses**  
Atur peran dan izin pengguna menggunakan **spatie/laravel-permission**:  
- Tetapkan peran seperti Admin, Dosen, atau Mahasiswa.  
- Sesuaikan hak akses untuk fitur tertentu guna menjamin keamanan data.  

🕒 **Absensi Mahasiswa**  
Rekap kehadiran mahasiswa berdasarkan Jadwal, lengkap dengan opsi pencarian dan filter.  

📄 **Laporan Absensi PDF**  
Ekspor laporan absensi ke format PDF untuk dokumentasi atau kebutuhan administratif.  

👥 **Manajemen User**  
Pantau dan kelola semua pengguna dalam sistem dengan fitur untuk:  
- Menambah, mengedit, dan menghapus pengguna.  
- Reset kata sandi atau mengatur ulang akun pengguna.  

🛡️ **Otentikasi yang Aman**  
Akses aplikasi hanya oleh pengguna yang berwenang, dilindungi oleh middleware berbasis peran.

---

## 🗂️ Modul-Modul Utama

### 1. **Manajemen Role & Hak Akses**  
- Buat dan kelola berbagai peran pengguna (Admin, Dosen, Mahasiswa).  
- Tentukan hak akses spesifik untuk setiap peran menggunakan **spatie/laravel-permission**.

### 2. **Manajemen User**  
- Tambahkan pengguna baru dengan detail lengkap.  
- Kelola akun pengguna, termasuk peran dan status aktif/nonaktif.  

### 3. **Mahasiswa**  
- Kelola data mahasiswa secara manual atau dengan fitur **import mahasiswa** menggunakan file Excel atau CSV.  

### 4. **Mata Kuliah**  
- Tambahkan, ubah, dan hapus data mata kuliah sesuai kebutuhan kurikulum.  

### 5. **Program Studi**  
- Atur program studi yang tersedia di kampus dengan detail yang lengkap.  

### 6. **Jadwal Kuliah**  
- Buat jadwal perkuliahan yang terorganisir untuk setiap mata kuliah dan program studi.  

### 7. **Manajemen Pertemuan dan Absensi**  
- Catat kehadiran mahasiswa pada setiap pertemuan dengan dukungan pencarian dan filter data.  

### 8. **Laporan Absensi PDF**  
- Ekspor laporan absensi ke format PDF untuk dokumentasi dan keperluan administratif lainnya.  

---

## 🛠️ Teknologi yang Digunakan

- **Framework:** Laravel  
- **Permission Management:** spatie/laravel-permission  
- **PDF Generator:** DomPDF  
- **Database:** MySQL  

---


## 🛠️ Instalasi Cepat

Ikuti langkah-langkah sederhana ini untuk menjalankan **Absensi Kampus** di mesin Anda:

### 1️⃣ Clone Repository
```bash
git clone https://github.com/username/absensi-kampus.git
cd absensi-kampus
