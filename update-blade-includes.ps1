# =======================================================
# Update Blade @include directives after file migration
# Run this AFTER running restructure.bat
# =======================================================

$viewsDir = "resources\views"

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Updating @include/@extends in Blade files"
Write-Host "============================================" -ForegroundColor Cyan

# --- ADMIN views: update @include inside moved blade files ---

# Validator: validator.* -> admin.validator.*
$validatorDir = "$viewsDir\admin\validator"
if (Test-Path $validatorDir) {
    Get-ChildItem -Path $validatorDir -Recurse -Filter "*.blade.php" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace "@include\('validator\.", "@include('admin.validator."
        if ($content -ne $updated) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
            Write-Host "  Updated: $($_.FullName)" -ForegroundColor Green
        }
    }
}

# Tarif: tarif.* -> admin.keuangan.tarif.*
$tarifDir = "$viewsDir\admin\keuangan\tarif"
if (Test-Path $tarifDir) {
    Get-ChildItem -Path $tarifDir -Recurse -Filter "*.blade.php" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace "@include\('tarif\.", "@include('admin.keuangan.tarif."
        if ($content -ne $updated) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
            Write-Host "  Updated: $($_.FullName)" -ForegroundColor Green
        }
    }
}

# Tenor-pembayaran: tenor-pembayaran.* -> admin.keuangan.tenor-pembayaran.*
$tenorDir = "$viewsDir\admin\keuangan\tenor-pembayaran"
if (Test-Path $tenorDir) {
    Get-ChildItem -Path $tenorDir -Recurse -Filter "*.blade.php" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace "@include\('tenor-pembayaran\.", "@include('admin.keuangan.tenor-pembayaran."
        if ($content -ne $updated) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
            Write-Host "  Updated: $($_.FullName)" -ForegroundColor Green
        }
    }
}

# Matakuliah: matakuliah.* -> admin.akademik.matakuliah.*
$mkDir = "$viewsDir\admin\akademik\matakuliah"
if (Test-Path $mkDir) {
    Get-ChildItem -Path $mkDir -Recurse -Filter "*.blade.php" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace "@include\('matakuliah\.", "@include('admin.akademik.matakuliah."
        if ($content -ne $updated) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
            Write-Host "  Updated: $($_.FullName)" -ForegroundColor Green
        }
    }
}

# Tahun-ajaran
$taDir = "$viewsDir\admin\akademik\tahun-ajaran"
if (Test-Path $taDir) {
    Get-ChildItem -Path $taDir -Recurse -Filter "*.blade.php" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace "@include\('tahun-ajaran\.", "@include('admin.akademik.tahun-ajaran."
        if ($content -ne $updated) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
            Write-Host "  Updated: $($_.FullName)" -ForegroundColor Green
        }
    }
}

# Aktivasi-mhs
$aktDir = "$viewsDir\admin\kemahasiswaan\aktivasi-mhs"
if (Test-Path $aktDir) {
    Get-ChildItem -Path $aktDir -Recurse -Filter "*.blade.php" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace "@include\('aktivasi-mhs\.", "@include('admin.kemahasiswaan.aktivasi-mhs."
        if ($content -ne $updated) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
            Write-Host "  Updated: $($_.FullName)" -ForegroundColor Green
        }
    }
}

# Permintaan
$permDir = "$viewsDir\admin\kemahasiswaan\permintaan"
if (Test-Path $permDir) {
    Get-ChildItem -Path $permDir -Recurse -Filter "*.blade.php" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace "@include\('permintaan\.", "@include('admin.kemahasiswaan.permintaan."
        if ($content -ne $updated) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
            Write-Host "  Updated: $($_.FullName)" -ForegroundColor Green
        }
    }
}

# Pengajuan-transkrip
$pengDir = "$viewsDir\admin\kemahasiswaan\pengajuan-transkrip"
if (Test-Path $pengDir) {
    Get-ChildItem -Path $pengDir -Recurse -Filter "*.blade.php" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace "@include\('pengajuan-transkrip\.", "@include('admin.kemahasiswaan.pengajuan-transkrip."
        if ($content -ne $updated) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
            Write-Host "  Updated: $($_.FullName)" -ForegroundColor Green
        }
    }
}

# --- DOSEN views: pages-dosen.* -> dosen.* ---
$dosenDir = "$viewsDir\dosen"
if (Test-Path $dosenDir) {
    Get-ChildItem -Path $dosenDir -Recurse -Filter "*.blade.php" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace "@include\('pages-dosen\.", "@include('dosen."
        $updated = $updated -replace "@extends\('pages-dosen\.", "@extends('dosen."
        if ($content -ne $updated) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
            Write-Host "  Updated: $($_.FullName)" -ForegroundColor Green
        }
    }
}

# --- MAHASISWA views: students.* -> mahasiswa.* ---
$mhsDir = "$viewsDir\mahasiswa"
if (Test-Path $mhsDir) {
    Get-ChildItem -Path $mhsDir -Recurse -Filter "*.blade.php" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace "@include\('students\.", "@include('mahasiswa."
        $updated = $updated -replace "@extends\('students\.", "@extends('mahasiswa."
        if ($content -ne $updated) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
            Write-Host "  Updated: $($_.FullName)" -ForegroundColor Green
        }
    }
}

# --- MAHASISWA views in admin: mahasiswa.* -> admin.kemahasiswaan.mahasiswa.* ---
$mhsAdminDir = "$viewsDir\admin\kemahasiswaan\mahasiswa"
if (Test-Path $mhsAdminDir) {
    Get-ChildItem -Path $mhsAdminDir -Recurse -Filter "*.blade.php" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace "@include\('mahasiswa\.", "@include('admin.kemahasiswaan.mahasiswa."
        if ($content -ne $updated) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
            Write-Host "  Updated: $($_.FullName)" -ForegroundColor Green
        }
    }
}

# --- Dosen admin views: dosen.* -> admin.master-data.dosen.* ---
$dosenAdminDir = "$viewsDir\admin\master-data\dosen"
if (Test-Path $dosenAdminDir) {
    Get-ChildItem -Path $dosenAdminDir -Recurse -Filter "*.blade.php" | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        $updated = $content -replace "@include\('dosen\.", "@include('admin.master-data.dosen."
        if ($content -ne $updated) {
            Set-Content -Path $_.FullName -Value $updated -NoNewline
            Write-Host "  Updated: $($_.FullName)" -ForegroundColor Green
        }
    }
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Blade @include updates complete!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
