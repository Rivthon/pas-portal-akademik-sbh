<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Verification Status - STUDENTS PORTAL SBH</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#4546da",
                        "primary-container": "#5f61f4",
                        "secondary": "#575896",
                        "background": "#f7f9fb",
                        "surface": "#f7f9fb",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f2f4f6",
                        "on-surface": "#191c1e",
                        "on-surface-variant": "#464555",
                        "outline-variant": "#c6c4d7",
                    },
                    "fontFamily": {
                        "headline": ["Plus Jakarta Sans"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    }
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: max(884px, 100dvh);
        }

        h1,
        h2,
        h3,
        .headline {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        /* Checkmark Sequence Animation */
        .scale-in-center {
            animation: scaleInCenter 0.4s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
        }

        @keyframes scaleInCenter {
            0% {
                transform: scale(0);
                opacity: 1;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .circle-draw {
            stroke-dasharray: 166;
            animation: circleDrawLoop 3s cubic-bezier(0.65, 0, 0.45, 1) infinite;
        }

        .check-draw {
            stroke-dasharray: 48;
            animation: checkDrawLoop 3s cubic-bezier(0.65, 0, 0.45, 1) infinite;
        }

        @keyframes circleDrawLoop {
            0%, 10% { stroke-dashoffset: 166; }
            30%, 80% { stroke-dashoffset: 0; }
            90%, 100% { stroke-dashoffset: 166; }
        }

        @keyframes checkDrawLoop {
            0%, 25% { stroke-dashoffset: 48; }
            40%, 80% { stroke-dashoffset: 0; }
            90%, 100% { stroke-dashoffset: 48; }
        }
    </style>
</head>

<body class="bg-background text-on-surface min-h-screen flex flex-col">
    <!-- TopAppBar Section -->
    <header class="fixed top-0 w-full z-50 bg-[#f7f9fb]/80 backdrop-blur-3xl border-b border-outline-variant/30">
        <div class="flex items-center justify-between px-6 py-4 w-full">
            <div class="flex items-center gap-4">
                <button onclick="window.history.back()"
                    class="material-symbols-outlined text-[#696CFF] p-2 hover:bg-[#e6e8ea] transition-colors rounded-full active:scale-95 duration-200">
                    arrow_back
                </button>
                <h1 class="font-['Plus_Jakarta_Sans'] font-bold text-lg tracking-tight text-on-surface">Verification
                    Details</h1>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-['Plus_Jakarta_Sans'] font-bold text-[#191c1e]">STUDENTS PORTAL SBH</span>
            </div>
        </div>
    </header>

    <!-- Main Content Canvas -->
    <main class="flex-grow pt-24 pb-12 px-6 flex flex-col items-center">
        @if($status === 'valid')
            <!-- Hero Success Section -->
            <div class="w-full max-w-md text-center mb-10">
                <div class="inline-flex items-center justify-center w-24 h-24 mb-6 rounded-full bg-emerald-100/50 scale-in-center">
                    <div
                        class="flex items-center justify-center w-16 h-16 rounded-full bg-emerald-500 shadow-lg shadow-emerald-200">
                        <svg class="w-8 h-8 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="circle-draw" cx="26" cy="26" r="25" fill="none" stroke="currentColor" stroke-width="4" />
                            <path class="check-draw" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                        </svg>
                    </div>
                </div>
                <h2 class="font-headline font-extrabold text-3xl tracking-tight text-emerald-600 mb-2">DOKUMEN VALID</h2>
                <p class="text-on-surface-variant font-medium">Kartu Ujian ini tercatat sah di sistem Students Portal SBH.</p>
            </div>

            <!-- Details Card -->
            <div
                class="w-full max-w-md bg-surface-container-lowest rounded-xl p-8 shadow-[0_20px_40px_rgba(69,69,85,0.06)] relative overflow-hidden">
                <!-- Subtle Decorative Gradient -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>

                <!-- Section Header: Informasi Kartu -->
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="w-1 h-6 bg-primary rounded-full"></span>
                        <h3 class="font-headline font-bold text-lg text-on-surface">Informasi Kartu Ujian</h3>
                    </div>

                    <div class="space-y-6">
                        <!-- Exam Details -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <span
                                    class="font-label text-[10px] uppercase tracking-widest text-on-surface-variant/60 font-semibold">Jenis
                                    Ujian</span>
                                <span class="font-headline font-bold text-on-surface">{{ $type }}
                                    ({{ $type === 'UTS' ? 'Ujian Tengah Semester' : 'Ujian Akhir Semester' }})</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span
                                    class="font-label text-[10px] uppercase tracking-widest text-on-surface-variant/60 font-semibold">Tahun
                                    Akademik</span>
                                <span class="font-headline font-bold text-on-surface">{{ $ta->nama }} -
                                    {{ $ta->semester }}</span>
                            </div>
                        </div>

                        <!-- Student Header -->
                        <div class="pt-4 mt-4 border-t border-surface-container-low">
                            <div class="flex items-center gap-2 mb-6">
                                <span class="w-1 h-6 bg-secondary rounded-full"></span>
                                <h3 class="font-headline font-bold text-lg text-on-surface">Data Mahasiswa</h3>
                            </div>
                            <div class="space-y-5">
                                <div class="flex flex-col gap-1">
                                    <span
                                        class="font-label text-[10px] uppercase tracking-widest text-on-surface-variant/60 font-semibold">Nama
                                        Lengkap</span>
                                    <span
                                        class="font-headline font-bold text-xl text-primary">{{ strtoupper($mahasiswa->nama) }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="font-label text-[10px] uppercase tracking-widest text-on-surface-variant/60 font-semibold">NIM</span>
                                        <span class="font-headline font-bold text-on-surface">{{ $mahasiswa->nim }}</span>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="font-label text-[10px] uppercase tracking-widest text-on-surface-variant/60 font-semibold">Program
                                            Studi</span>
                                        <span
                                            class="font-headline font-bold text-on-surface text-sm">{{ $mahasiswa->programStudi->nama ?? '-' }}
                                            (Angkatan {{ $mahasiswa->tahun_masuk }})</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Date Footer -->
                        <div
                            class="pt-6 mt-6 border-t border-dashed border-outline-variant/30 flex items-center justify-between">
                            <div class="flex flex-col gap-1">
                                <span
                                    class="font-label text-[10px] uppercase tracking-widest text-on-surface-variant/60 font-semibold">Waktu
                                    Verifikasi</span>
                                <span
                                    class="font-body text-xs font-medium text-on-surface-variant">{{ date('d M Y H:i:s') }}
                                    WIB</span>
                            </div>
                            <div class="w-12 h-12 bg-surface-container-low rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary-container">qr_code_2</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <!-- Hero Error Section -->
            <div class="w-full max-w-md text-center mb-10">
                <div class="inline-flex items-center justify-center w-24 h-24 mb-6 rounded-full bg-red-100/50">
                    <div
                        class="flex items-center justify-center w-16 h-16 rounded-full bg-red-500 shadow-lg shadow-red-200">
                        <span class="material-symbols-outlined text-white text-4xl"
                            style="font-variation-settings: 'FILL' 1;">cancel</span>
                    </div>
                </div>
                <h2 class="font-headline font-extrabold text-3xl tracking-tight text-red-600 mb-2">DOKUMEN TIDAK SAH</h2>
                <p class="text-on-surface-variant font-medium">Peringatan: Kartu Ujian ini tidak valid atau telah
                    dimanipulasi.</p>
            </div>

            <!-- Error Details Card -->
            <div
                class="w-full max-w-md bg-surface-container-lowest rounded-xl p-8 shadow-[0_20px_40px_rgba(69,69,85,0.06)] relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-red-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="w-1 h-6 bg-red-500 rounded-full"></span>
                        <h3 class="font-headline font-bold text-lg text-on-surface">Error Verifikasi</h3>
                    </div>

                    <div class="bg-red-50 border border-red-100 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-red-500 mt-0.5">error</span>
                            <div class="flex flex-col">
                                <span class="font-headline font-bold text-red-800 text-sm">Kesalahan:</span>
                                <span class="font-body text-red-700 text-sm mt-1">{{ $message }}</span>
                            </div>
                        </div>
                    </div>

                    <p class="text-sm text-on-surface-variant leading-relaxed">
                        Dokumen yang ditunjukkan ditolak oleh otoritas sistem Students Portal SBH karena tidak ditemukan tanda tangan
                        kriptografi yang sesuai dengan rekam jejak akademik institusi.
                    </p>

                    <div
                        class="pt-6 mt-6 border-t border-dashed border-outline-variant/30 flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span
                                class="font-label text-[10px] uppercase tracking-widest text-on-surface-variant/60 font-semibold">Waktu
                                Pengecekan</span>
                            <span class="font-body text-xs font-medium text-on-surface-variant">{{ date('d M Y H:i:s') }}
                                WIB</span>
                        </div>
                        <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-400">gpp_maybe</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </main>

    <!-- Footer Section -->
    <footer class="w-full py-8 bg-[#f7f9fb] flex flex-col items-center justify-center space-y-4">
        <div class="flex items-center gap-2 text-[#696CFF]">
            <span class="material-symbols-outlined text-sm">lock</span>
            <span class="font-['Inter'] text-xs font-medium tracking-widest uppercase">Secured by Students Portal SBH</span>
        </div>
        <div class="flex gap-6">
            <a class="font-['Inter'] text-xs font-medium tracking-widest uppercase text-[#454555] opacity-60 hover:text-[#696CFF] transition-colors"
                href="#">Privacy Policy</a>
            <a class="font-['Inter'] text-xs font-medium tracking-widest uppercase text-[#454555] opacity-60 hover:text-[#696CFF] transition-colors"
                href="#">Support</a>
        </div>
        <p class="font-['Inter'] text-[10px] text-[#454555] opacity-40">© {{ date('Y') }} Students Portal SBH. All Rights
            Reserved.</p>
    </footer>

    <!-- Background Decoration -->
    <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-primary/5 rounded-full blur-[120px]"></div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-secondary/5 rounded-full blur-[120px]"></div>
    </div>
</body>

</html>