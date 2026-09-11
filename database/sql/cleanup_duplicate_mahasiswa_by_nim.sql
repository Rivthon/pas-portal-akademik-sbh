-- Pembersihan NIM mahasiswa duplikat untuk MySQL 8 / phpMyAdmin.
-- Dibuat berdasarkan audit db_pas (1).sql tanggal 11 September 2026.
-- Hasil audit awal: 467 baris, 386 NIM unik, 44 grup duplikat, 81 baris duplikat.
--
-- ATURAN:
-- 1. mahasiswa_id paling kecil dipertahankan agar identitas lama tetap stabil.
-- 2. Nama, prodi, kelas, email, dan dospem memakai data import paling baru.
-- 3. Password, semester, status akademik, dan histori pada ID utama tidak direset.
-- 4. ID duplikat yang sudah mempunyai data akademik/keuangan/LMS tidak dihapus.
-- 5. Baris yang dihapus dicadangkan ke backup_mahasiswa_duplikat_20260911.

SET @cleanup_old_sql_mode = @@SESSION.sql_mode;
SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- Menghapus procedure yang mungkin tertinggal dari versi skrip sebelumnya.
DROP PROCEDURE IF EXISTS cleanup_duplicate_mahasiswa_by_nim;

DROP TEMPORARY TABLE IF EXISTS tmp_mahasiswa_duplicate_map;
CREATE TEMPORARY TABLE tmp_mahasiswa_duplicate_map (
    duplicate_id INT NOT NULL PRIMARY KEY,
    keep_id INT NOT NULL,
    latest_id INT NOT NULL,
    nim_key VARCHAR(30) NOT NULL,
    safe_to_delete TINYINT(1) NOT NULL DEFAULT 0
);

INSERT INTO tmp_mahasiswa_duplicate_map (
    duplicate_id,
    keep_id,
    latest_id,
    nim_key,
    safe_to_delete
)
SELECT
    m.mahasiswa_id,
    grouped.keep_id,
    grouped.latest_id,
    grouped.nim_key,
    CASE WHEN
        NOT EXISTS (SELECT 1 FROM absensi x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM absensi_praktik x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM kegiatan_tambahan x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM krs x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM krs_guidance_messages x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM lms_pengumpulan_tugas x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM lms_quiz_attempts x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM p2mw x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM pengajuan_transkrip x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM penguasaan_bahasa x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM penilaian x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM permintaan_perubahans x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM pkm x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM ppsm x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM saran x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM sertifikasi_profesi_kompetensi x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM tagihan_mahasiswa x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM transaksi_pembayaran x WHERE x.mahasiswa_id=m.mahasiswa_id) AND
        NOT EXISTS (SELECT 1 FROM uap_nilai x WHERE x.mahasiswa_id=m.mahasiswa_id)
    THEN 1 ELSE 0 END
FROM mahasiswa m
JOIN (
    SELECT
        UPPER(TRIM(nim)) AS nim_key,
        MIN(mahasiswa_id) AS keep_id,
        MAX(mahasiswa_id) AS latest_id
    FROM mahasiswa
    GROUP BY UPPER(TRIM(nim))
    HAVING COUNT(*) > 1
) grouped ON grouped.nim_key=UPPER(TRIM(m.nim))
WHERE m.mahasiswa_id<>grouped.keep_id;

CREATE TABLE IF NOT EXISTS backup_mahasiswa_duplikat_20260911
AS SELECT * FROM mahasiswa WHERE 1=0;

INSERT INTO backup_mahasiswa_duplikat_20260911
SELECT m.*
FROM mahasiswa m
JOIN tmp_mahasiswa_duplicate_map d ON d.duplicate_id=m.mahasiswa_id
WHERE d.safe_to_delete=1
  AND NOT EXISTS (
      SELECT 1
      FROM backup_mahasiswa_duplikat_20260911 backup
      WHERE backup.mahasiswa_id=m.mahasiswa_id
  );

START TRANSACTION;

UPDATE mahasiswa keep_row
JOIN (
    SELECT DISTINCT keep_id, latest_id
    FROM tmp_mahasiswa_duplicate_map
) map ON map.keep_id=keep_row.mahasiswa_id
JOIN mahasiswa latest ON latest.mahasiswa_id=map.latest_id
SET keep_row.nim=TRIM(latest.nim),
    keep_row.nama=latest.nama,
    keep_row.email=latest.email,
    keep_row.jurusan_id=latest.jurusan_id,
    keep_row.kelas=latest.kelas,
    keep_row.dosen_id=COALESCE(latest.dosen_id, keep_row.dosen_id),
    keep_row.updated_at=GREATEST(keep_row.updated_at, latest.updated_at);

DELETE duplicate_row
FROM mahasiswa duplicate_row
JOIN tmp_mahasiswa_duplicate_map d ON d.duplicate_id=duplicate_row.mahasiswa_id
WHERE d.safe_to_delete=1;

COMMIT;

-- Jika blocked_duplicate_rows > 0, data tersebut sengaja tidak dihapus agar histori aman.
SELECT
    (SELECT COUNT(*) FROM backup_mahasiswa_duplikat_20260911) AS deleted_or_previously_cleaned,
    (SELECT COUNT(*) FROM tmp_mahasiswa_duplicate_map WHERE safe_to_delete=0) AS blocked_duplicate_rows,
    (
        SELECT COUNT(*)
        FROM (
            SELECT UPPER(TRIM(nim)) AS nim_key
            FROM mahasiswa
            GROUP BY UPPER(TRIM(nim))
            HAVING COUNT(*) > 1
        ) remaining
    ) AS duplicate_groups_remaining;

DROP TEMPORARY TABLE IF EXISTS tmp_mahasiswa_duplicate_map;
SET SESSION sql_mode = @cleanup_old_sql_mode;

-- Sesudah duplicate_groups_remaining = 0:
-- php artisan migrate --force
-- php artisan optimize:clear
