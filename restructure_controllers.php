<?php

$baseDir = __DIR__ . '/app/Http/Controllers';
$routesFile = __DIR__ . '/routes/web.php';

$map = [
    // Akademik
    'JadwalController.php' => ['Admin/Akademik', 'JadwalController'],
    'JadwalPraktikController.php' => ['Admin/Akademik', 'JadwalPraktikController'],
    'JadwalutsController.php' => ['Admin/Akademik', 'JadwalUtsController'], 
    'JadwaluasController.php' => ['Admin/Akademik', 'JadwalUasController'], 
    'JadwaluapController.php' => ['Admin/Akademik', 'JadwalUapController'], 
    'KurikulumController.php' => ['Admin/Akademik', 'KurikulumController'],
    'MatakuliahController.php' => ['Admin/Akademik', 'MatakuliahController'],
    'TahunAkademikController.php' => ['Admin/Akademik', 'TahunAkademikController'],
    'CalenderAkademikController.php' => ['Admin/Akademik', 'CalendarAkademikController'], 
    'AbsensiController.php' => ['Admin/Akademik', 'AbsensiController'],
    'PertemuanController.php' => ['Admin/Akademik', 'PertemuanController'],
    'RuanganController.php' => ['Admin/Akademik', 'RuanganController'],
    
    // Penilaian
    'InputNilaiController.php' => ['Admin/Penilaian', 'InputNilaiController'],
    'NilaiController.php' => ['Admin/Penilaian', 'NilaiController'],
    'UapNilaiController.php' => ['Admin/Penilaian', 'UapNilaiController'],
    'PenilaianController.php' => ['Admin/Penilaian', 'PenilaianController'],
    'EvaluasiController.php' => ['Admin/Penilaian', 'EvaluasiController'],
    
    // Kemahasiswaan
    'MahasiswaController.php' => ['Admin/Kemahasiswaan', 'MahasiswaController'],
    'AktivasiController.php' => ['Admin/Kemahasiswaan', 'AktivasiController'],
    'PengajuanTranskripController.php' => ['Admin/Kemahasiswaan', 'PengajuanTranskripController'],
    'PermintaanController.php' => ['Admin/Kemahasiswaan', 'PermintaanController'],
    
    // Keuangan
    'TagihanMahasiswaController.php' => ['Admin/Keuangan', 'TagihanMahasiswaController'],
    'TarifController.php' => ['Admin/Keuangan', 'TarifController'],
    'TenorPembayaranController.php' => ['Admin/Keuangan', 'TenorPembayaranController'],
    'GelombangController.php' => ['Admin/Keuangan', 'GelombangController'],
    
    // Master-Data
    'DosenController.php' => ['Admin/MasterData', 'DosenController'],
    'ProgramStudiController.php' => ['Admin/MasterData', 'ProgramStudiController'],
    'UserController.php' => ['Admin/MasterData', 'UserController'],
    'RoleController.php' => ['Admin/MasterData', 'RoleController'],
    
    // Admin Root
    'HomeController.php' => ['Admin', 'HomeController'],
    'ValidatorController.php' => ['Admin', 'ValidatorController'],
    'LaporanController.php' => ['Admin', 'LaporanController'],
    'SettingController.php' => ['Admin', 'SettingController'],
    'ProfileController.php' => ['Admin', 'ProfileController'],
    'ProductController.php' => ['Admin', 'ProductController'],
    'BeritaController.php' => ['Admin', 'BeritaController'],
    'DosenKurikulumController.php' => ['Admin', 'DosenKurikulumController'],
];

echo "============================================\n";
echo "Phase 2: Restructuring Controllers\n";
echo "============================================\n\n";

$routesContent = file_get_contents($routesFile);

foreach ($map as $oldFile => $info) {
    $newSubPath = $info[0];
    $newClass = $info[1];
    $oldClass = str_replace('.php', '', $oldFile);
    
    $oldPath = $baseDir . '/' . $oldFile;
    $newDir = $baseDir . '/' . $newSubPath;
    $newPath = $newDir . '/' . $newClass . '.php';
    
    if (file_exists($oldPath)) {
        // Create dir if not exists
        if (!is_dir($newDir)) {
            mkdir($newDir, 0755, true);
        }
        
        $content = file_get_contents($oldPath);
        
        // 1. Update Namespace
        $newNamespace = 'namespace App\Http\Controllers\\' . str_replace('/', '\\', $newSubPath) . ';';
        $content = preg_replace('/namespace\s+App\\\\Http\\\\Controllers\s*;/', $newNamespace, $content);
        
        // 2. Update Class Name (if renamed)
        if ($oldClass !== $newClass) {
            $content = preg_replace('/class\s+' . $oldClass . '\s+extends/', 'class ' . $newClass . ' extends', $content);
        }
        
        // 3. Update Controller calls to use standard Controller
        // Because they moved to a subnamespace, they need to import the base controller
        if (!preg_match('/use\s+App\\\\Http\\\\Controllers\\\\Controller\s*;/', $content)) {
            $content = preg_replace('/(namespace\s+.*?;)/', "$1\n\nuse App\\Http\\Controllers\\Controller;", $content);
        }
        
        // Save new file
        file_put_contents($newPath, $content);
        
        // Delete old file
        unlink($oldPath);
        
        echo "Moved & namespaced: $oldFile -> $newSubPath/$newClass.php\n";
        
        // 4. Update routes/web.php
        $oldUse = 'use App\Http\Controllers\\' . $oldClass . ';';
        $newUse = 'use App\Http\Controllers\\' . str_replace('/', '\\', $newSubPath) . '\\' . $newClass . ';';
        
        $routesContent = str_replace($oldUse, $newUse, $routesContent);
        
        // Replace direct string references if any (e.g. static calls)
        $routesContent = str_replace('[' . $oldClass . '::class', '[' . $newClass . '::class', $routesContent);
        
    } else {
        echo "Skip (not found): $oldFile\n";
    }
}

// Fix Mahasiswa PerminataanController typo
$mhsOldPath = $baseDir . '/Mahasiswa/PerminataanController.php';
$mhsNewPath = $baseDir . '/Mahasiswa/PermintaanController.php';
if (file_exists($mhsOldPath)) {
    $content = file_get_contents($mhsOldPath);
    $content = str_replace('class PerminataanController', 'class PermintaanController', $content);
    file_put_contents($mhsNewPath, $content);
    unlink($mhsOldPath);
    echo "Renamed: Mahasiswa/PerminataanController.php -> Mahasiswa/PermintaanController.php\n";
    
    // update routes
    $routesContent = str_replace('use App\Http\Controllers\Mahasiswa\PerminataanController;', 'use App\Http\Controllers\Mahasiswa\PermintaanController;', $routesContent);
    $routesContent = str_replace('[PerminataanController::class', '[PermintaanController::class', $routesContent);
}

// Rename UjianController method typos
$mhsUjianPath = $baseDir . '/Mahasiswa/UjianController.php';
if (file_exists($mhsUjianPath)) {
    $content = file_get_contents($mhsUjianPath);
    $content = str_replace('tampikanNilaiUts', 'tampilkanNilaiUts', $content);
    $content = str_replace('tampikanNilaiUas', 'tampilkanNilaiUas', $content);
    $content = str_replace('tampikanNilaiAkhir', 'tampilkanNilaiAkhir', $content);
    file_put_contents($mhsUjianPath, $content);
    echo "Fixed typos in Mahasiswa/UjianController.php methods\n";
    
    $routesContent = str_replace('tampikanNilaiUts', 'tampilkanNilaiUts', $routesContent);
    $routesContent = str_replace('tampikanNilaiUas', 'tampilkanNilaiUas', $routesContent);
    $routesContent = str_replace('tampikanNilaiAkhir', 'tampilkanNilaiAkhir', $routesContent);
}

file_put_contents($routesFile, $routesContent);
echo "Routes updated successfully.\n";

echo "============================================\n";
echo "DONE! Controllers Restructured.\n";
echo "============================================\n";

