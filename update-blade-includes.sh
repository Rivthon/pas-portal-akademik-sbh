#!/bin/bash

echo "============================================"
echo "Updating @include/@extends in Blade files"
echo "============================================"

# Helper function buat find & replace cross-platform
replace_in_files() {
    local dir=$1
    local search=$2
    local replace=$3
    
    if [ -d "$dir" ]; then
        find "$dir" -type f -name "*.blade.php" -exec sed -i "s/$search/$replace/g" {} +
    fi
}

VIEWS="resources/views"

echo "[1/11] Updating validator @includes..."
replace_in_files "$VIEWS/admin/validator" "@include('validator." "@include('admin.validator."

echo "[2/11] Updating tarif @includes..."
replace_in_files "$VIEWS/admin/keuangan/tarif" "@include('tarif." "@include('admin.keuangan.tarif."

echo "[3/11] Updating tenor-pembayaran @includes..."
replace_in_files "$VIEWS/admin/keuangan/tenor-pembayaran" "@include('tenor-pembayaran." "@include('admin.keuangan.tenor-pembayaran."

echo "[4/11] Updating matakuliah @includes..."
replace_in_files "$VIEWS/admin/akademik/matakuliah" "@include('matakuliah." "@include('admin.akademik.matakuliah."

echo "[5/11] Updating tahun-ajaran @includes..."
replace_in_files "$VIEWS/admin/akademik/tahun-ajaran" "@include('tahun-ajaran." "@include('admin.akademik.tahun-ajaran."

echo "[6/11] Updating aktivasi-mhs @includes..."
replace_in_files "$VIEWS/admin/kemahasiswaan/aktivasi-mhs" "@include('aktivasi-mhs." "@include('admin.kemahasiswaan.aktivasi-mhs."

echo "[7/11] Updating permintaan @includes..."
replace_in_files "$VIEWS/admin/kemahasiswaan/permintaan" "@include('permintaan." "@include('admin.kemahasiswaan.permintaan."

echo "[8/11] Updating pengajuan-transkrip @includes..."
replace_in_files "$VIEWS/admin/kemahasiswaan/pengajuan-transkrip" "@include('pengajuan-transkrip." "@include('admin.kemahasiswaan.pengajuan-transkrip."

echo "[9/11] Updating dosen panel @includes/@extends..."
replace_in_files "$VIEWS/dosen" "@include('pages-dosen." "@include('dosen."
replace_in_files "$VIEWS/dosen" "@extends('pages-dosen." "@extends('dosen."

echo "[10/11] Updating mahasiswa panel @includes/@extends..."
replace_in_files "$VIEWS/mahasiswa" "@include('students." "@include('mahasiswa."
replace_in_files "$VIEWS/mahasiswa" "@extends('students." "@extends('mahasiswa."

echo "[11/11] Updating admin mahasiswa/dosen @includes..."
replace_in_files "$VIEWS/admin/kemahasiswaan/mahasiswa" "@include('mahasiswa." "@include('admin.kemahasiswaan.mahasiswa."
replace_in_files "$VIEWS/admin/master-data/dosen" "@include('dosen." "@include('admin.master-data.dosen."

echo ""
echo "============================================"
echo "DONE! Blade @include updates complete."
echo "============================================"
