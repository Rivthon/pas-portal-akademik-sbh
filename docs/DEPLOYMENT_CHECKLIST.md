# Checklist Deployment Production PAS

## Sebelum deployment

- Pastikan backup terakhir berstatus normal pada menu **Kesehatan Sistem**.
- Pastikan perubahan sudah diuji di lokal atau staging.
- Pastikan `.env` production tidak ikut masuk Git.
- Pastikan `APP_ENV=production` dan `APP_DEBUG=false`.
- Pastikan ruang disk mencukupi untuk source, cache, upload, dan backup.

## Deployment

Jalankan dari direktori aplikasi:

```bash
bash scripts/deploy-production.sh
```

Script akan mengaktifkan maintenance mode, melakukan `git pull --ff-only`, memasang dependency production,
menjalankan migration, membangun cache, mencatat versi deployment, lalu membuka aplikasi kembali.

## Cron wajib

Tambahkan satu cron untuk user pemilik aplikasi:

```cron
* * * * * cd /var/www/html/pas && php artisan schedule:run >> /dev/null 2>&1
```

Cron tersebut menjalankan heartbeat setiap menit, backup setiap hari, dan pembersihan activity log lama.

## Variabel `.env` operasional

```env
APP_ENV=production
APP_DEBUG=false
CACHE_STORE=file
SESSION_DRIVER=file
SYSTEM_BACKUP_DISK=private
SYSTEM_BACKUP_DIRECTORY=system-backups
SYSTEM_BACKUP_RETENTION_DAYS=14
SYSTEM_BACKUP_SCHEDULE=01:30
SYSTEM_BACKUP_INCLUDE_UPLOADS=true
MYSQLDUMP_PATH=/usr/bin/mysqldump
```

## Setelah deployment

- Login sebagai Super Admin.
- Buka **Kesehatan Sistem** dan pastikan database, storage, cache, runtime, serta disk normal.
- Tunggu maksimal lima menit dan pastikan heartbeat scheduler berubah menjadi normal.
- Jalankan satu backup manual dan pastikan ZIP dapat diunduh.
- Uji login admin, dosen, dan mahasiswa.
- Uji KRS, LMS, RPS, absensi, nilai, serta permission utama.
- Periksa error production terbaru pada dashboard sistem.

## Queue production

Jika `QUEUE_CONNECTION=sync`, dashboard menampilkan peringatan. Untuk pekerjaan berat gunakan database atau Redis
dan jalankan worker melalui Supervisor/systemd. Jangan menjalankan worker manual dari sesi SSH jangka panjang.

## Pemulihan backup

- Unduh ZIP dari menu Kesehatan Sistem.
- Simpan salinan ZIP di lokasi berbeda dari VPS.
- File `database/database.sql` berisi dump database.
- Folder `uploads/public` dan `uploads/private` berisi file aplikasi.
- Lakukan restore hanya pada maintenance mode dan setelah membuat backup kondisi terakhir.
