<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Verifikasi Dokumen - SIAKAD SBH</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <style>
        body {
            background-color: #f5f5f9;
        }
        .verify-card {
            max-width: 450px;
            margin: 50px auto;
            border-top: 5px solid;
            border-radius: 0.5rem;
        }
        .status-icon {
            font-size: 5rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container text-center">
        @if($status === 'valid')
        <div class="card verify-card shadow-lg" style="border-top-color: #71dd37;">
            <div class="card-body p-4">
                <i class="bx bxs-check-circle text-success status-icon"></i>
                <h3 class="text-success fw-bold mb-1">DOKUMEN VALID</h3>
                <p class="text-muted mb-4">Kartu Ujian ini tercatat sah di sistem SIAKAD.</p>
                
                <hr>
                
                <div class="text-start mt-4">
                    <h5 class="fw-bold mb-3"><i class="bx bx-scan me-1"></i> Informasi Kartu Ujian</h5>
                    
                    <div class="mb-2">
                        <small class="text-muted d-block">Jenis Ujian</small>
                        <span class="fw-bold text-dark fs-5">{{ $type }} ({{ $type === 'UTS' ? 'Ujian Tengah Semester' : 'Ujian Akhir Semester' }})</span>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted d-block">Tahun Akademik / Semester</small>
                        <span class="fw-bold text-dark">{{ $ta->nama }} - {{ $ta->semester }}</span>
                    </div>
                </div>

                <div class="text-start mt-4 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-3"><i class="bx bx-user me-1"></i> Data Mahasiswa</h6>
                    
                    <div class="mb-2">
                        <small class="text-muted d-block">Milik (Nama Mahasiswa)</small>
                        <span class="fw-bold text-primary">{{ strtoupper($mahasiswa->nama) }}</span>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted d-block">Nomor Induk Mahasiswa (NIM)</small>
                        <span class="fw-bold text-dark">{{ $mahasiswa->nim }}</span>
                    </div>

                    <div class="mb-0">
                        <small class="text-muted d-block">Program Studi & Angkatan</small>
                        <span class="fw-bold text-dark">{{ $mahasiswa->programStudi->nama ?? '-' }} (Angkatan {{ $mahasiswa->tahun_masuk }})</span>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <small class="text-muted">Terverifikasi pada: {{ date('d M Y H:i:s') }} WIB</small>
                </div>
            </div>
        </div>
        @else
        <div class="card verify-card shadow-lg" style="border-top-color: #ff3e1d;">
            <div class="card-body p-4">
                <i class="bx bxs-x-circle text-danger status-icon"></i>
                <h3 class="text-danger fw-bold mb-1">DOKUMEN TIDAK SAH</h3>
                <p class="text-muted mb-4">Peringatan: Kartu Ujian ini tidak valid atau telah dimanipulasi.</p>
                
                <hr>
                
                <div class="alert alert-danger text-start mt-4">
                    <h6 class="fw-bold alert-heading mb-1"><i class="bx bx-error-circle"></i> Error Verifikasi</h6>
                    <small>{{ $message }}</small>
                </div>

                <div class="mt-4">
                    <p class="mb-0 text-muted"><small>Dokumen yang ditunjukkan ditolak oleh otoritas sistem SIAKAD karena tidak ditemukan tanda tangan kriptografi yang sesuai.</small></p>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Footer / Logo -->
        <div class="mt-2 mb-5">
            <span class="text-muted"><i class="bx bx-shield-quarter"></i> Secured by SIAKAD SBH System</span>
        </div>
    </div>
</body>
</html>
