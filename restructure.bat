@echo off
echo ============================================
echo Phase 1: Restructure Views - SIAKAD SBH
echo ============================================

cd /d "%~dp0"
set VIEWS=resources\views

echo.
echo [Step 1] Creating admin directory structure...
mkdir "%VIEWS%\admin\akademik" 2>nul
mkdir "%VIEWS%\admin\penilaian" 2>nul
mkdir "%VIEWS%\admin\kemahasiswaan" 2>nul
mkdir "%VIEWS%\admin\keuangan" 2>nul
mkdir "%VIEWS%\admin\master-data" 2>nul

echo.
echo [Step 2] Moving AKADEMIK views...
xcopy "%VIEWS%\jadwal" "%VIEWS%\admin\akademik\jadwal\" /E /I /Y /Q
xcopy "%VIEWS%\jadwal-praktik" "%VIEWS%\admin\akademik\jadwal-praktik\" /E /I /Y /Q
xcopy "%VIEWS%\jadwal-uts" "%VIEWS%\admin\akademik\jadwal-uts\" /E /I /Y /Q
xcopy "%VIEWS%\jadwal-uas" "%VIEWS%\admin\akademik\jadwal-uas\" /E /I /Y /Q
xcopy "%VIEWS%\jadwal-uap" "%VIEWS%\admin\akademik\jadwal-uap\" /E /I /Y /Q
xcopy "%VIEWS%\kurikulum" "%VIEWS%\admin\akademik\kurikulum\" /E /I /Y /Q
xcopy "%VIEWS%\matakuliah" "%VIEWS%\admin\akademik\matakuliah\" /E /I /Y /Q
xcopy "%VIEWS%\tahun-ajaran" "%VIEWS%\admin\akademik\tahun-ajaran\" /E /I /Y /Q
xcopy "%VIEWS%\calender-akademik" "%VIEWS%\admin\akademik\calendar-akademik\" /E /I /Y /Q
xcopy "%VIEWS%\absensi" "%VIEWS%\admin\akademik\absensi\" /E /I /Y /Q
xcopy "%VIEWS%\pertemuan" "%VIEWS%\admin\akademik\pertemuan\" /E /I /Y /Q
xcopy "%VIEWS%\ruangan" "%VIEWS%\admin\akademik\ruangan\" /E /I /Y /Q

echo.
echo [Step 3] Moving PENILAIAN views...
xcopy "%VIEWS%\input-nilai" "%VIEWS%\admin\penilaian\input-nilai\" /E /I /Y /Q
xcopy "%VIEWS%\nilai" "%VIEWS%\admin\penilaian\nilai\" /E /I /Y /Q
xcopy "%VIEWS%\nilai-uap" "%VIEWS%\admin\penilaian\nilai-uap\" /E /I /Y /Q
xcopy "%VIEWS%\penilaian" "%VIEWS%\admin\penilaian\penilaian\" /E /I /Y /Q
xcopy "%VIEWS%\evaluasi" "%VIEWS%\admin\penilaian\evaluasi\" /E /I /Y /Q

echo.
echo [Step 4] Moving KEMAHASISWAAN views...
xcopy "%VIEWS%\mahasiswa" "%VIEWS%\admin\kemahasiswaan\mahasiswa\" /E /I /Y /Q
xcopy "%VIEWS%\aktivasi-mhs" "%VIEWS%\admin\kemahasiswaan\aktivasi-mhs\" /E /I /Y /Q
xcopy "%VIEWS%\pengajuan-transkrip" "%VIEWS%\admin\kemahasiswaan\pengajuan-transkrip\" /E /I /Y /Q
xcopy "%VIEWS%\permintaan" "%VIEWS%\admin\kemahasiswaan\permintaan\" /E /I /Y /Q

echo.
echo [Step 5] Moving KEUANGAN views...
xcopy "%VIEWS%\tagihan-mahasiswa" "%VIEWS%\admin\keuangan\tagihan-mahasiswa\" /E /I /Y /Q
xcopy "%VIEWS%\tarif" "%VIEWS%\admin\keuangan\tarif\" /E /I /Y /Q
xcopy "%VIEWS%\tenor-pembayaran" "%VIEWS%\admin\keuangan\tenor-pembayaran\" /E /I /Y /Q
xcopy "%VIEWS%\gelombang" "%VIEWS%\admin\keuangan\gelombang\" /E /I /Y /Q

echo.
echo [Step 6] Moving MASTER-DATA views...
xcopy "%VIEWS%\dosen" "%VIEWS%\admin\master-data\dosen\" /E /I /Y /Q
xcopy "%VIEWS%\program-studi" "%VIEWS%\admin\master-data\program-studi\" /E /I /Y /Q
xcopy "%VIEWS%\users" "%VIEWS%\admin\master-data\users\" /E /I /Y /Q
xcopy "%VIEWS%\roles" "%VIEWS%\admin\master-data\roles\" /E /I /Y /Q

echo.
echo [Step 7] Moving standalone admin views...
xcopy "%VIEWS%\validator" "%VIEWS%\admin\validator\" /E /I /Y /Q
xcopy "%VIEWS%\laporan" "%VIEWS%\admin\laporan\" /E /I /Y /Q
xcopy "%VIEWS%\settings" "%VIEWS%\admin\settings\" /E /I /Y /Q
xcopy "%VIEWS%\profile" "%VIEWS%\admin\profile\" /E /I /Y /Q
xcopy "%VIEWS%\products" "%VIEWS%\admin\products\" /E /I /Y /Q
copy "%VIEWS%\home.blade.php" "%VIEWS%\admin\home.blade.php" /Y

echo.
echo [Step 8] Renaming panel folders...
REM First rename students to mahasiswa-panel (temp), then pages-dosen
xcopy "%VIEWS%\students" "%VIEWS%\mahasiswa-panel\" /E /I /Y /Q
xcopy "%VIEWS%\pages-dosen" "%VIEWS%\dosen-panel\" /E /I /Y /Q

echo.
echo [Step 9] Deleting old folders...
rmdir /s /q "%VIEWS%\jadwal"
rmdir /s /q "%VIEWS%\jadwal-praktik"
rmdir /s /q "%VIEWS%\jadwal-uts"
rmdir /s /q "%VIEWS%\jadwal-uas"
rmdir /s /q "%VIEWS%\jadwal-uap"
rmdir /s /q "%VIEWS%\kurikulum"
rmdir /s /q "%VIEWS%\matakuliah"
rmdir /s /q "%VIEWS%\tahun-ajaran"
rmdir /s /q "%VIEWS%\calender-akademik"
rmdir /s /q "%VIEWS%\calendar-akademik"
rmdir /s /q "%VIEWS%\absensi"
rmdir /s /q "%VIEWS%\pertemuan"
rmdir /s /q "%VIEWS%\ruangan"
rmdir /s /q "%VIEWS%\input-nilai"
rmdir /s /q "%VIEWS%\nilai"
rmdir /s /q "%VIEWS%\nilai-uap"
rmdir /s /q "%VIEWS%\penilaian"
rmdir /s /q "%VIEWS%\evaluasi"
rmdir /s /q "%VIEWS%\mahasiswa"
rmdir /s /q "%VIEWS%\aktivasi-mhs"
rmdir /s /q "%VIEWS%\pengajuan-transkrip"
rmdir /s /q "%VIEWS%\permintaan"
rmdir /s /q "%VIEWS%\tagihan-mahasiswa"
rmdir /s /q "%VIEWS%\tarif"
rmdir /s /q "%VIEWS%\tenor-pembayaran"
rmdir /s /q "%VIEWS%\gelombang"
rmdir /s /q "%VIEWS%\dosen"
rmdir /s /q "%VIEWS%\program-studi"
rmdir /s /q "%VIEWS%\users"
rmdir /s /q "%VIEWS%\roles"
rmdir /s /q "%VIEWS%\validator"
rmdir /s /q "%VIEWS%\laporan"
rmdir /s /q "%VIEWS%\settings"
rmdir /s /q "%VIEWS%\profile"
rmdir /s /q "%VIEWS%\products"
rmdir /s /q "%VIEWS%\students"
rmdir /s /q "%VIEWS%\pages-dosen"
del "%VIEWS%\home.blade.php"

echo.
echo [Step 10] Final rename panel folders...
rename "%VIEWS%\mahasiswa-panel" "mahasiswa"
rename "%VIEWS%\dosen-panel" "dosen"

echo.
echo ============================================
echo DONE! Files moved successfully.
echo Now update controller view paths.
echo ============================================
pause
