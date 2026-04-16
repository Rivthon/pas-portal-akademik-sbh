# 📁 Views Directory Structure — SIAKAD SBH

## Konvensi Penamaan & Organisasi

### 🏗️ Struktur Komponen (components/)
```
components/
├── admin/          ← Komponen untuk layout Admin (master.blade.php)
│   ├── sidebar.blade.php
│   ├── navbar.blade.php
│   ├── footer.blade.php
│   └── notification.blade.php
├── dosen/          ← Komponen untuk layout Dosen (dosen.blade.php)
│   ├── sidebar.blade.php
│   ├── navbar.blade.php
│   ├── footer.blade.php
│   └── notification.blade.php
└── mahasiswa/      ← Komponen untuk layout Mahasiswa (mahasiswa.blade.php)
    ├── sidebar.blade.php
    ├── navbar.blade.php
    ├── footer.blade.php
    └── notification.blade.php
```

### 🔐 Panel Berdasarkan Role

| Role       | Layout                    | Views Folder     | Route Prefix  |
|------------|---------------------------|------------------|---------------|
| Admin      | `layouts.master`          | Root views/*     | `admin.*`     |
| Dosen      | `layouts.dosen`           | `pages-dosen/*`  | `dosen.*`     |
| Mahasiswa  | `layouts.mahasiswa`       | `students/*`     | `mahasiswa.*` |

### 📂 Modul Admin (Root Level)
```
├── absensi/             ← Manajemen absensi
├── aktivasi-mhs/        ← Aktivasi mahasiswa
├── calendar-akademik/   ← Kalender akademik (✅ typo 'calender' sudah difix)
├── dosen/               ← CRUD data dosen
├── evaluasi/            ← Evaluasi dosen
├── exports/             ← Template export (PDF/Excel)
├── gelombang/           ← Gelombang pendaftaran
├── input-nilai/         ← Input nilai
├── jadwal/              ← Jadwal kuliah
├── jadwal-praktik/      ← Jadwal praktik
├── jadwal-uap/          ← Jadwal UAP
├── jadwal-uas/          ← Jadwal UAS
├── jadwal-uts/          ← Jadwal UTS
├── kurikulum/           ← Kurikulum & mata kuliah
├── laporan/             ← Laporan
├── mahasiswa/           ← CRUD data mahasiswa
├── matakuliah/          ← Mata kuliah
├── nilai/               ← Transkrip nilai
├── nilai-uap/           ← Nilai UAP
├── penilaian/           ← Penilaian dosen
├── pengajuan-transkrip/ ← Pengajuan transkrip
├── permintaan/          ← Permintaan/feedback
├── pertemuan/           ← Pertemuan kelas
├── products/            ← (Template scaffold - optional)
├── profile/             ← Profile admin
├── program-studi/       ← CRUD program studi
├── roles/               ← CRUD roles
├── ruangan/             ← CRUD ruangan
├── settings/            ← Pengaturan aplikasi
├── tagihan-mahasiswa/   ← Tagihan mahasiswa
├── tahun-ajaran/        ← Tahun ajaran
├── tarif/               ← Tarif pembayaran
├── tenor-pembayaran/    ← Tenor pembayaran
└── users/               ← CRUD users
```

### 📂 Panel Dosen (pages-dosen/)
```
pages-dosen/
├── dashboard.blade.php
├── profile.blade.php
├── absensi/         ← Rekap & input absensi
├── berita/          ← Berita kampus
├── jadwal/          ← Jadwal mengajar
├── jadwal-praktik/  ← Jadwal praktik
├── materi/          ← Bahan ajar
├── nilai/           ← Input & lihat nilai
└── profile/         ← Edit profile
```

### 📂 Panel Mahasiswa (students/)
```
students/
├── dashboard.blade.php
├── absensi.blade.php
├── profile.blade.php
├── administrasi/    ← Info administrasi
├── berita/          ← Berita kampus
├── edom/            ← Evaluasi dosen
├── jadwal/          ← Jadwal kuliah
├── jadwal-uap/      ← Jadwal & kartu UAP
├── jadwal-uas/      ← Jadwal & kartu UAS
├── jadwal-uts/      ← Jadwal & kartu UTS
├── khs/             ← Kartu Hasil Studi
├── krs/             ← Kartu Rencana Studi
├── nilai-akhir/     ← Nilai akhir
├── nilai-uas/       ← Nilai UAS
├── nilai-uts/       ← Nilai UTS
├── pengajuan/       ← Pengajuan transkrip
├── permintaan/      ← Feedback/permintaan
├── profile/         ← Edit profile
├── sertifikasi/     ← (kosong)
├── skpi/            ← Surat Keterangan Pendamping Ijazah
│   ├── bahasa/
│   ├── kegiatan-tambahan/
│   ├── pkm/
│   ├── ppsm/
│   ├── sertifikasi/
│   └── wirausaha/
└── uap/             ← Nilai UAP
```

### 📂 Lainnya
```
├── auth/            ← Login, register, password reset
├── errors/          ← 404, 500, dll
├── layouts/         ← Layout templates (master, dosen, mahasiswa)
├── public/          ← Halaman publik (verifikasi ujian)
├── validator/       ← Validator SKPI
└── vendor/          ← Vendor overrides (pagination, sweetalert)
```

## ⚠️ File Deprecated (Dapat Dihapus)
- `jadwal/index-tes.blade.php` — file tes tidak terpakai
- `permintaan/index2.blade.php` — duplikat tidak terpakai
- `laporan/pdf_2.blade.php` — duplikat tidak terpakai
- `students/bahasa/` — folder kosong
- `students/sertifikasi/` — folder kosong

## 📝 Konvensi Penamaan Partial
- `partial_list.blade.php` atau `partials_list.blade.php` — AJAX-loaded list partial
- `table.blade.php` — AJAX-loaded table partial
- `form.blade.php` — Shared create/edit form
