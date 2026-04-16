<?php

$baseDir = __DIR__;

$fileRenames = [
    'app/Models/Jadwaluts.php' => 'app/Models/JadwalUts.php',
    'app/Models/Jadwaluas.php' => 'app/Models/JadwalUas.php',
    'app/Models/Jadwaluap.php' => 'app/Models/JadwalUap.php',
    'app/Models/P2mw_program.php' => 'app/Models/P2mwProgram.php',
    'app/Models/Pkm_program.php' => 'app/Models/PkmProgram.php',
    'app/Models/CalenderAkademik.php' => 'app/Models/CalendarAkademik.php',
];

$replacements = [
    'App\Models\Jadwaluts' => 'App\Models\JadwalUts',
    ' Jadwaluts ' => ' JadwalUts ', // Type hints
    ' Jadwaluts::' => ' JadwalUts::',
    '(Jadwaluts ' => '(JadwalUts ',
    '<Jadwaluts>' => '<JadwalUts>',
    'class Jadwaluts ' => 'class JadwalUts ',
    
    'App\Models\Jadwaluas' => 'App\Models\JadwalUas',
    ' Jadwaluas ' => ' JadwalUas ',
    ' Jadwaluas::' => ' JadwalUas::',
    '(Jadwaluas ' => '(JadwalUas ',
    '<Jadwaluas>' => '<JadwalUas>',
    'class Jadwaluas ' => 'class JadwalUas ',
    
    'App\Models\Jadwaluap' => 'App\Models\JadwalUap',
    ' Jadwaluap ' => ' JadwalUap ',
    ' Jadwaluap::' => ' JadwalUap::',
    '(Jadwaluap ' => '(JadwalUap ',
    '<Jadwaluap>' => '<JadwalUap>',
    'class Jadwaluap ' => 'class JadwalUap ',
    
    'App\Models\P2mw_program' => 'App\Models\P2mwProgram',
    ' P2mw_program ' => ' P2mwProgram ',
    ' P2mw_program::' => ' P2mwProgram::',
    '(P2mw_program ' => '(P2mwProgram ',
    '<P2mw_program>' => '<P2mwProgram>',
    'class P2mw_program ' => 'class P2mwProgram ',
    // Adding standalone P2mw_program since it's an odd snake_case class name which might be used without spaces
    'P2mw_program::' => 'P2mwProgram::',
    
    'App\Models\Pkm_program' => 'App\Models\PkmProgram',
    ' Pkm_program ' => ' PkmProgram ',
    ' Pkm_program::' => ' PkmProgram::',
    '(Pkm_program ' => '(PkmProgram ',
    '<Pkm_program>' => '<PkmProgram>',
    'class Pkm_program ' => 'class PkmProgram ',
    'Pkm_program::' => 'PkmProgram::',
    
    'App\Models\CalenderAkademik' => 'App\Models\CalendarAkademik',
    ' CalenderAkademik ' => ' CalendarAkademik ',
    ' CalenderAkademik::' => ' CalendarAkademik::',
    '(CalenderAkademik ' => '(CalendarAkademik ',
    '<CalenderAkademik>' => '<CalendarAkademik>',
    'class CalenderAkademik ' => 'class CalendarAkademik ',
    'CalenderAkademik::' => 'CalendarAkademik::',
    
    // Specifically targeting use statements without trailing spaces
    'use App\Models\Jadwaluts;' => 'use App\Models\JadwalUts;',
    'use App\Models\Jadwaluas;' => 'use App\Models\JadwalUas;',
    'use App\Models\Jadwaluap;' => 'use App\Models\JadwalUap;',
    'use App\Models\P2mw_program;' => 'use App\Models\P2mwProgram;',
    'use App\Models\Pkm_program;' => 'use App\Models\PkmProgram;',
    'use App\Models\CalenderAkademik;' => 'use App\Models\CalendarAkademik;',
];

echo "============================================\n";
echo "Phase 3: Fixing Model Names\n";
echo "============================================\n\n";

// Recursive function to get all php and blade files
function getFiles($dir) {
    $files = [];
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iter as $file) {
        if ($file->isFile()) {
            $ext = $file->getExtension();
            if ($ext === 'php') {
                $files[] = $file->getPathname();
            }
        }
    }
    return $files;
}

$dirsToScan = [
    $baseDir . '/app',
    $baseDir . '/database',
    $baseDir . '/routes',
    $baseDir . '/resources/views',
];

$allFiles = [];
foreach ($dirsToScan as $dir) {
    if (is_dir($dir)) {
        $allFiles = array_merge($allFiles, getFiles($dir));
    }
}

$changedFilesCount = 0;

foreach ($allFiles as $file) {
    $content = file_get_contents($file);
    $newContent = strtr($content, $replacements);
    
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Updated references in: " . str_replace($baseDir . '/', '', $file) . "\n";
        $changedFilesCount++;
    }
}

echo "\n[Step 2] Renaming physical Model files...\n";

foreach ($fileRenames as $old => $new) {
    $oldPath = $baseDir . '/' . $old;
    $newPath = $baseDir . '/' . $new;
    
    if (file_exists($oldPath)) {
        rename($oldPath, $newPath);
        echo "Renamed: $old -> $new\n";
    } else {
        echo "Skipped (not found): $old\n";
    }
}

echo "\n============================================\n";
echo "DONE! Phase 3 complete. Modified $changedFilesCount files.\n";
echo "============================================\n";
