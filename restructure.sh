#!/bin/bash

echo "============================================"
echo "Phase 1: Restructure Views - SIAKAD SBH"
echo "============================================"

VIEWS="resources/views"

echo ""
echo "[Step 1] Creating admin directory structure..."
mkdir -p "$VIEWS/admin/akademik"
mkdir -p "$VIEWS/admin/penilaian"
mkdir -p "$VIEWS/admin/kemahasiswaan"
mkdir -p "$VIEWS/admin/keuangan"
mkdir -p "$VIEWS/admin/master-data"

echo ""
echo "[Step 2] Copying AKADEMIK views..."
cp -r "$VIEWS/jadwal" "$VIEWS/admin/akademik/" 2>/dev/null
cp -r "$VIEWS/jadwal-praktik" "$VIEWS/admin/akademik/" 2>/dev/null
cp -r "$VIEWS/jadwal-uts" "$VIEWS/admin/akademik/" 2>/dev/null
cp -r "$VIEWS/jadwal-uas" "$VIEWS/admin/akademik/" 2>/dev/null
cp -r "$VIEWS/jadwal-uap" "$VIEWS/admin/akademik/" 2>/dev/null
cp -r "$VIEWS/kurikulum" "$VIEWS/admin/akademik/" 2>/dev/null
cp -r "$VIEWS/matakuliah" "$VIEWS/admin/akademik/" 2>/dev/null
cp -r "$VIEWS/tahun-ajaran" "$VIEWS/admin/akademik/" 2>/dev/null
# Rename folder calender-akademik -> calendar-akademik
cp -r "$VIEWS/calender-akademik" "$VIEWS/admin/akademik/calendar-akademik" 2>/dev/null 
cp -r "$VIEWS/absensi" "$VIEWS/admin/akademik/" 2>/dev/null
cp -r "$VIEWS/pertemuan" "$VIEWS/admin/akademik/" 2>/dev/null
cp -r "$VIEWS/ruangan" "$VIEWS/admin/akademik/" 2>/dev/null

echo ""
echo "[Step 3] Copying PENILAIAN views..."
cp -r "$VIEWS/input-nilai" "$VIEWS/admin/penilaian/" 2>/dev/null
cp -r "$VIEWS/nilai" "$VIEWS/admin/penilaian/" 2>/dev/null
cp -r "$VIEWS/nilai-uap" "$VIEWS/admin/penilaian/" 2>/dev/null
cp -r "$VIEWS/penilaian" "$VIEWS/admin/penilaian/" 2>/dev/null
cp -r "$VIEWS/evaluasi" "$VIEWS/admin/penilaian/" 2>/dev/null

echo ""
echo "[Step 4] Copying KEMAHASISWAAN views..."
cp -r "$VIEWS/mahasiswa" "$VIEWS/admin/kemahasiswaan/" 2>/dev/null
cp -r "$VIEWS/aktivasi-mhs" "$VIEWS/admin/kemahasiswaan/" 2>/dev/null
cp -r "$VIEWS/pengajuan-transkrip" "$VIEWS/admin/kemahasiswaan/" 2>/dev/null
cp -r "$VIEWS/permintaan" "$VIEWS/admin/kemahasiswaan/" 2>/dev/null

echo ""
echo "[Step 5] Copying KEUANGAN views..."
cp -r "$VIEWS/tagihan-mahasiswa" "$VIEWS/admin/keuangan/" 2>/dev/null
cp -r "$VIEWS/tarif" "$VIEWS/admin/keuangan/" 2>/dev/null
cp -r "$VIEWS/tenor-pembayaran" "$VIEWS/admin/keuangan/" 2>/dev/null
cp -r "$VIEWS/gelombang" "$VIEWS/admin/keuangan/" 2>/dev/null

echo ""
echo "[Step 6] Copying MASTER-DATA views..."
cp -r "$VIEWS/dosen" "$VIEWS/admin/master-data/" 2>/dev/null
cp -r "$VIEWS/program-studi" "$VIEWS/admin/master-data/" 2>/dev/null
cp -r "$VIEWS/users" "$VIEWS/admin/master-data/" 2>/dev/null
cp -r "$VIEWS/roles" "$VIEWS/admin/master-data/" 2>/dev/null

echo ""
echo "[Step 7] Copying standalone admin views..."
cp -r "$VIEWS/validator" "$VIEWS/admin/" 2>/dev/null
cp -r "$VIEWS/laporan" "$VIEWS/admin/" 2>/dev/null
cp -r "$VIEWS/settings" "$VIEWS/admin/" 2>/dev/null
cp -r "$VIEWS/profile" "$VIEWS/admin/" 2>/dev/null
cp -r "$VIEWS/products" "$VIEWS/admin/" 2>/dev/null
cp "$VIEWS/home.blade.php" "$VIEWS/admin/home.blade.php" 2>/dev/null

echo ""
echo "[Step 8] Safely copying panel folders..."
cp -r "$VIEWS/students" "$VIEWS/mahasiswa-panel" 2>/dev/null
cp -r "$VIEWS/pages-dosen" "$VIEWS/dosen-panel" 2>/dev/null

echo ""
echo "[Step 9] Validating move and deleting old folders safely..."
# Delete on success
rm -rf "$VIEWS/jadwal" "$VIEWS/jadwal-praktik" "$VIEWS/jadwal-uts" "$VIEWS/jadwal-uas" "$VIEWS/jadwal-uap"
rm -rf "$VIEWS/kurikulum" "$VIEWS/matakuliah" "$VIEWS/tahun-ajaran" "$VIEWS/calender-akademik" "$VIEWS/calendar-akademik"
rm -rf "$VIEWS/absensi" "$VIEWS/pertemuan" "$VIEWS/ruangan"
rm -rf "$VIEWS/input-nilai" "$VIEWS/nilai" "$VIEWS/nilai-uap" "$VIEWS/penilaian" "$VIEWS/evaluasi"
rm -rf "$VIEWS/mahasiswa" "$VIEWS/aktivasi-mhs" "$VIEWS/pengajuan-transkrip" "$VIEWS/permintaan"
rm -rf "$VIEWS/tagihan-mahasiswa" "$VIEWS/tarif" "$VIEWS/tenor-pembayaran" "$VIEWS/gelombang"
rm -rf "$VIEWS/dosen" "$VIEWS/program-studi" "$VIEWS/users" "$VIEWS/roles"
rm -rf "$VIEWS/validator" "$VIEWS/laporan" "$VIEWS/settings" "$VIEWS/profile" "$VIEWS/products"
rm -rf "$VIEWS/students" "$VIEWS/pages-dosen"
rm -f "$VIEWS/home.blade.php"

echo ""
echo "[Step 10] Finalizing panel folders..."
mv "$VIEWS/mahasiswa-panel" "$VIEWS/mahasiswa"
mv "$VIEWS/dosen-panel" "$VIEWS/dosen"

echo ""
echo "============================================"
echo "DONE! Files moved successfully."
echo "============================================"
