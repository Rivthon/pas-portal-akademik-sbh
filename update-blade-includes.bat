@echo off
echo ============================================
echo Updating @include/@extends in Blade files
echo ============================================

cd /d "%~dp0"
set VIEWS=resources\views

echo.
echo [1/11] Updating validator @includes...
powershell -Command "Get-ChildItem -Path '%VIEWS%\admin\validator' -Recurse -Filter '*.blade.php' -ErrorAction SilentlyContinue | ForEach-Object { $c = Get-Content $_.FullName -Raw; $u = $c -replace \"@include\('validator\.\", \"@include('admin.validator.\"; if ($c -ne $u) { Set-Content $_.FullName $u -NoNewline; Write-Host '  Updated:' $_.Name } }"

echo [2/11] Updating tarif @includes...
powershell -Command "Get-ChildItem -Path '%VIEWS%\admin\keuangan\tarif' -Recurse -Filter '*.blade.php' -ErrorAction SilentlyContinue | ForEach-Object { $c = Get-Content $_.FullName -Raw; $u = $c -replace \"@include\('tarif\.\", \"@include('admin.keuangan.tarif.\"; if ($c -ne $u) { Set-Content $_.FullName $u -NoNewline; Write-Host '  Updated:' $_.Name } }"

echo [3/11] Updating tenor-pembayaran @includes...
powershell -Command "Get-ChildItem -Path '%VIEWS%\admin\keuangan\tenor-pembayaran' -Recurse -Filter '*.blade.php' -ErrorAction SilentlyContinue | ForEach-Object { $c = Get-Content $_.FullName -Raw; $u = $c -replace \"@include\('tenor-pembayaran\.\", \"@include('admin.keuangan.tenor-pembayaran.\"; if ($c -ne $u) { Set-Content $_.FullName $u -NoNewline; Write-Host '  Updated:' $_.Name } }"

echo [4/11] Updating matakuliah @includes...
powershell -Command "Get-ChildItem -Path '%VIEWS%\admin\akademik\matakuliah' -Recurse -Filter '*.blade.php' -ErrorAction SilentlyContinue | ForEach-Object { $c = Get-Content $_.FullName -Raw; $u = $c -replace \"@include\('matakuliah\.\", \"@include('admin.akademik.matakuliah.\"; if ($c -ne $u) { Set-Content $_.FullName $u -NoNewline; Write-Host '  Updated:' $_.Name } }"

echo [5/11] Updating tahun-ajaran @includes...
powershell -Command "Get-ChildItem -Path '%VIEWS%\admin\akademik\tahun-ajaran' -Recurse -Filter '*.blade.php' -ErrorAction SilentlyContinue | ForEach-Object { $c = Get-Content $_.FullName -Raw; $u = $c -replace \"@include\('tahun-ajaran\.\", \"@include('admin.akademik.tahun-ajaran.\"; if ($c -ne $u) { Set-Content $_.FullName $u -NoNewline; Write-Host '  Updated:' $_.Name } }"

echo [6/11] Updating aktivasi-mhs @includes...
powershell -Command "Get-ChildItem -Path '%VIEWS%\admin\kemahasiswaan\aktivasi-mhs' -Recurse -Filter '*.blade.php' -ErrorAction SilentlyContinue | ForEach-Object { $c = Get-Content $_.FullName -Raw; $u = $c -replace \"@include\('aktivasi-mhs\.\", \"@include('admin.kemahasiswaan.aktivasi-mhs.\"; if ($c -ne $u) { Set-Content $_.FullName $u -NoNewline; Write-Host '  Updated:' $_.Name } }"

echo [7/11] Updating permintaan @includes...
powershell -Command "Get-ChildItem -Path '%VIEWS%\admin\kemahasiswaan\permintaan' -Recurse -Filter '*.blade.php' -ErrorAction SilentlyContinue | ForEach-Object { $c = Get-Content $_.FullName -Raw; $u = $c -replace \"@include\('permintaan\.\", \"@include('admin.kemahasiswaan.permintaan.\"; if ($c -ne $u) { Set-Content $_.FullName $u -NoNewline; Write-Host '  Updated:' $_.Name } }"

echo [8/11] Updating pengajuan-transkrip @includes...
powershell -Command "Get-ChildItem -Path '%VIEWS%\admin\kemahasiswaan\pengajuan-transkrip' -Recurse -Filter '*.blade.php' -ErrorAction SilentlyContinue | ForEach-Object { $c = Get-Content $_.FullName -Raw; $u = $c -replace \"@include\('pengajuan-transkrip\.\", \"@include('admin.kemahasiswaan.pengajuan-transkrip.\"; if ($c -ne $u) { Set-Content $_.FullName $u -NoNewline; Write-Host '  Updated:' $_.Name } }"

echo [9/11] Updating dosen panel @includes/@extends...
powershell -Command "Get-ChildItem -Path '%VIEWS%\dosen' -Recurse -Filter '*.blade.php' -ErrorAction SilentlyContinue | ForEach-Object { $c = Get-Content $_.FullName -Raw; $u = $c -replace \"@include\('pages-dosen\.\", \"@include('dosen.\"; $u = $u -replace \"@extends\('pages-dosen\.\", \"@extends('dosen.\"; if ($c -ne $u) { Set-Content $_.FullName $u -NoNewline; Write-Host '  Updated:' $_.Name } }"

echo [10/11] Updating mahasiswa panel @includes/@extends...
powershell -Command "Get-ChildItem -Path '%VIEWS%\mahasiswa' -Recurse -Filter '*.blade.php' -ErrorAction SilentlyContinue | ForEach-Object { $c = Get-Content $_.FullName -Raw; $u = $c -replace \"@include\('students\.\", \"@include('mahasiswa.\"; $u = $u -replace \"@extends\('students\.\", \"@extends('mahasiswa.\"; if ($c -ne $u) { Set-Content $_.FullName $u -NoNewline; Write-Host '  Updated:' $_.Name } }"

echo [11/11] Updating admin mahasiswa/dosen @includes...
powershell -Command "Get-ChildItem -Path '%VIEWS%\admin\kemahasiswaan\mahasiswa' -Recurse -Filter '*.blade.php' -ErrorAction SilentlyContinue | ForEach-Object { $c = Get-Content $_.FullName -Raw; $u = $c -replace \"@include\('mahasiswa\.\", \"@include('admin.kemahasiswaan.mahasiswa.\"; if ($c -ne $u) { Set-Content $_.FullName $u -NoNewline; Write-Host '  Updated:' $_.Name } }"
powershell -Command "Get-ChildItem -Path '%VIEWS%\admin\master-data\dosen' -Recurse -Filter '*.blade.php' -ErrorAction SilentlyContinue | ForEach-Object { $c = Get-Content $_.FullName -Raw; $u = $c -replace \"@include\('dosen\.\", \"@include('admin.master-data.dosen.\"; if ($c -ne $u) { Set-Content $_.FullName $u -NoNewline; Write-Host '  Updated:' $_.Name } }"

echo.
echo ============================================
echo DONE! Blade @include updates complete.
echo ============================================
pause
