-- Pembersihan NIM mahasiswa duplikat untuk MySQL 8.
-- Dibuat berdasarkan audit db_pas (1).sql tanggal 11 September 2026.
-- Hasil audit: 467 baris, 386 NIM unik, 44 grup duplikat, 81 baris duplikat.
--
-- ATURAN:
-- 1. mahasiswa_id paling kecil dipertahankan agar identitas lama tetap stabil.
-- 2. Nama, prodi, kelas, email, dan dospem memakai data import paling baru.
-- 3. Password, semester, status akademik, dan histori pada ID utama tidak direset.
-- 4. Proses dibatalkan jika ID duplikat sudah mempunyai data akademik/keuangan/LMS.
-- 5. Baris yang dihapus dicadangkan ke backup_mahasiswa_duplikat_20260911.

SET @cleanup_old_sql_mode = @@SESSION.sql_mode;
SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP PROCEDURE IF EXISTS cleanup_duplicate_mahasiswa_by_nim;

DELIMITER $$
CREATE PROCEDURE cleanup_duplicate_mahasiswa_by_nim()
BEGIN
    DECLARE related_rows BIGINT DEFAULT 0;

    DROP TEMPORARY TABLE IF EXISTS tmp_mahasiswa_duplicate_map;
    CREATE TEMPORARY TABLE tmp_mahasiswa_duplicate_map (
        duplicate_id INT NOT NULL PRIMARY KEY,
        keep_id INT NOT NULL,
        latest_id INT NOT NULL,
        nim_key VARCHAR(30) NOT NULL
    );

    INSERT INTO tmp_mahasiswa_duplicate_map (duplicate_id, keep_id, latest_id, nim_key)
    SELECT m.mahasiswa_id, grouped.keep_id, grouped.latest_id, grouped.nim_key
    FROM mahasiswa m
    JOIN (
        SELECT UPPER(TRIM(nim)) AS nim_key,
               MIN(mahasiswa_id) AS keep_id,
               MAX(mahasiswa_id) AS latest_id,
               COUNT(*) AS total
        FROM mahasiswa
        GROUP BY UPPER(TRIM(nim))
        HAVING COUNT(*) > 1
    ) grouped ON grouped.nim_key = UPPER(TRIM(m.nim))
    WHERE m.mahasiswa_id <> grouped.keep_id;

    SELECT COUNT(*) INTO related_rows
    FROM (
        SELECT mahasiswa_id FROM absensi
        UNION ALL SELECT mahasiswa_id FROM absensi_praktik
        UNION ALL SELECT mahasiswa_id FROM kegiatan_tambahan
        UNION ALL SELECT mahasiswa_id FROM krs
        UNION ALL SELECT mahasiswa_id FROM krs_guidance_messages
        UNION ALL SELECT mahasiswa_id FROM lms_pengumpulan_tugas
        UNION ALL SELECT mahasiswa_id FROM lms_quiz_attempts
        UNION ALL SELECT mahasiswa_id FROM p2mw
        UNION ALL SELECT mahasiswa_id FROM pengajuan_transkrip
        UNION ALL SELECT mahasiswa_id FROM penguasaan_bahasa
        UNION ALL SELECT mahasiswa_id FROM penilaian
        UNION ALL SELECT mahasiswa_id FROM permintaan_perubahans
        UNION ALL SELECT mahasiswa_id FROM pkm
        UNION ALL SELECT mahasiswa_id FROM ppsm
        UNION ALL SELECT mahasiswa_id FROM saran
        UNION ALL SELECT mahasiswa_id FROM sertifikasi_profesi_kompetensi
        UNION ALL SELECT mahasiswa_id FROM tagihan_mahasiswa
        UNION ALL SELECT mahasiswa_id FROM transaksi_pembayaran
        UNION ALL SELECT mahasiswa_id FROM uap_nilai
    ) related
    JOIN tmp_mahasiswa_duplicate_map d ON d.duplicate_id=related.mahasiswa_id;

    IF related_rows > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Dibatalkan: ID mahasiswa duplikat sudah memiliki data terkait. Audit dan gabungkan relasi secara manual.';
    END IF;

    CREATE TABLE IF NOT EXISTS backup_mahasiswa_duplikat_20260911
    AS SELECT * FROM mahasiswa WHERE 1=0;
    INSERT INTO backup_mahasiswa_duplikat_20260911
    SELECT m.*
    FROM mahasiswa m
    JOIN tmp_mahasiswa_duplicate_map d ON d.duplicate_id=m.mahasiswa_id
    WHERE NOT EXISTS (
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
    JOIN tmp_mahasiswa_duplicate_map d ON d.duplicate_id=duplicate_row.mahasiswa_id;

    COMMIT;

    SELECT ROW_COUNT() AS info_rows_affected_last_statement;
    SELECT COUNT(*) AS duplicate_groups_remaining
    FROM (
        SELECT UPPER(TRIM(nim)) AS nim_key
        FROM mahasiswa
        GROUP BY UPPER(TRIM(nim))
        HAVING COUNT(*) > 1
    ) remaining;
END$$
DELIMITER ;

CALL cleanup_duplicate_mahasiswa_by_nim();
DROP PROCEDURE cleanup_duplicate_mahasiswa_by_nim;
SET SESSION sql_mode = @cleanup_old_sql_mode;

-- Sesudah hasil duplicate_groups_remaining = 0:
-- 1. git pull kode terbaru
-- 2. php artisan migrate --force
-- Migration akan menambahkan UNIQUE INDEX pada kolom mahasiswa.nim.
