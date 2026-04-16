@extends('layouts.master')
@section('title', 'Dashboard')

@push('head')
    <style>
        /* ===== Dashboard Premium Styles ===== */

        /* Hero Banner */
        .dash-hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #ffd485ff 0%, #fc7b11ff 40%, #d9daecff 100%);
            border-radius: 16px;
            padding: 2.5rem 2.5rem;
            color: #fff;
            box-shadow: 0 12px 40px rgba(105, 108, 255, .18);
        }

        .dash-hero::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
            transform: translate(30%, -50%);
            filter: blur(60px);
        }

        .dash-hero::after {
            content: '';
            position: absolute;
            left: 25%;
            bottom: 0;
            width: 160px;
            height: 160px;
            background: rgba(99, 102, 241, .2);
            border-radius: 50%;
            filter: blur(40px);
        }

        .dash-hero .hero-content {
            position: relative;
            z-index: 2;
        }

        .dash-hero .hero-illustration {
            position: absolute;
            right: 30px;
            bottom: 0;
            height: 175px;
            opacity: .85;
            z-index: 1;
        }

        .dash-hero .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 193, 7, .85);
            color: #5d3a00;
            font-size: .65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .12em;
            padding: 4px 14px;
            border-radius: 99px;
        }

        .dash-hero .hero-title {
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: -.02em;
            margin-bottom: .5rem;
        }

        .dash-hero .hero-sub {
            opacity: .85;
            font-size: .9rem;
            font-weight: 400;
        }

        /* Stat Cards */
        .dash-stat {
            border: 1px solid rgba(0, 0, 0, .04);
            border-radius: 14px;
            padding: 1.5rem;
            background: #fff;
            transition: box-shadow .25s ease, transform .15s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .02);
        }

        .dash-stat:hover {
            box-shadow: 0 8px 28px rgba(105, 108, 255, .1);
            transform: translateY(-3px);
        }

        .dash-stat .stat-icon-circle {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 1.3rem;
        }

        .dash-stat .stat-label {
            font-size: .62rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: #8592a3;
        }

        .dash-stat .stat-value {
            font-size: 1.85rem;
            font-weight: 800;
            color: #1a1c1e;
            letter-spacing: -.02em;
            line-height: 1.1;
        }

        /* Chart Cards */
        .dash-chart-card {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .04);
            border-radius: 14px;
            box-shadow: 0 20px 40px rgba(69, 69, 85, .04);
            overflow: hidden;
        }

        .dash-chart-card .chart-header {
            padding: 1.5rem 1.75rem 0;
        }

        .dash-chart-card .chart-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #1a1c1e;
            letter-spacing: -.01em;
        }

        .dash-chart-card .chart-sub {
            font-size: .78rem;
            color: #8592a3;
            font-weight: 500;
        }

        .dash-chart-card .chart-body {
            padding: 1rem 1.75rem 1.75rem;
        }

        /* Doughnut Center */
        .doughnut-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .doughnut-center {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .doughnut-center .center-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1a1c1e;
        }

        .doughnut-center .center-label {
            font-size: .6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .15em;
            color: #8592a3;
        }

        /* Legend */
        .legend-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .5rem 0;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .legend-label {
            font-size: .82rem;
            font-weight: 600;
            color: #566a7f;
        }

        .legend-pct {
            font-size: .82rem;
            font-weight: 700;
            color: #1a1c1e;
        }

        /* Schedule Table */
        .dash-schedule {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .04);
            border-radius: 14px;
            box-shadow: 0 20px 40px rgba(69, 69, 85, .04);
            overflow: hidden;
        }

        .dash-schedule .schedule-header {
            padding: 1.5rem 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dash-schedule .schedule-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #1a1c1e;
        }

        .dash-schedule table thead th {
            font-size: .62rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #8592a3;
            border: none;
            padding-bottom: 1rem;
        }

        .dash-schedule table tbody td {
            padding: 1.1rem 1rem;
            vertical-align: middle;
            border-color: rgba(0, 0, 0, .04);
        }

        .dash-schedule table tbody tr {
            transition: background .15s ease;
        }

        .dash-schedule table tbody tr:hover {
            background: #f7f9fb;
        }

        .schedule-mk-name {
            font-weight: 700;
            font-size: .875rem;
            color: #1a1c1e;
        }

        .schedule-mk-sub {
            font-size: .72rem;
            color: #8592a3;
        }

        .schedule-room {
            display: inline-block;
            background: #f3f3f7;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: .72rem;
            font-weight: 700;
            color: #566a7f;
            text-transform: uppercase;
        }

        .schedule-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: .7rem;
            font-weight: 700;
        }

        .schedule-status .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .schedule-status.berlangsung {
            background: rgba(16, 185, 129, .1);
            color: #059669;
        }

        .schedule-status.berlangsung .dot {
            background: #10b981;
        }

        .schedule-status.menunggu {
            background: rgba(105, 108, 255, .1);
            color: #696cff;
        }

        .schedule-status.menunggu .dot {
            background: #696cff;
        }

        .schedule-status.selesai {
            background: rgba(133, 146, 163, .1);
            color: #8592a3;
        }

        .schedule-status.selesai .dot {
            background: #8592a3;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-page">

        {{-- ===== Hero Banner ===== --}}
        <section class="mb-4">
            <div class="dash-hero">
                <div class="hero-content">
                    <div class="mb-3">
                        @foreach (auth()->user()->getRoleNames() as $role)
                            <span class="role-badge">
                                <i class="bx bx-shield-quarter" style="font-size: .8rem;"></i> Role {{ ucfirst($role) }}
                            </span>
                        @endforeach
                    </div>
                    <h2 class="hero-title">
                        Selamat Datang di Sistem Informasi Akademik, {{ auth()->user()->name }}!
                    </h2>
                    <p class="hero-sub mb-0">
                        Semoga hari Anda menyenangkan dan penuh produktivitas.
                        @if($tahunAkademikAktif)
                            <br><small style="opacity:.7;"><i class="bx bx-calendar-check"></i> Tahun Akademik
                                {{ $tahunAkademikAktif->nama ?? '' }} —
                                {{ ucfirst($tahunAkademikAktif->semester ?? '') }}</small>
                        @endif
                    </p>
                </div>
                <img src="{{ asset('dashboard_assets/assets/img/front-pages/landing-page/cta-dashboard-2.png') }}"
                    alt="Illustration" class="hero-illustration d-none d-lg-block">
            </div>
        </section>

        {{-- ===== Summary Cards ===== --}}
        <section class="row g-4 mb-4">
            @php
                $cards = [
                    ['label' => 'Total Mahasiswa', 'value' => $totalMahasiswa, 'icon' => 'bx-group', 'color' => '#696cff', 'bg' => 'rgba(105,108,255,.08)'],
                    ['label' => 'Aktif', 'value' => $totalMahasiswaAktif, 'icon' => 'bx-user-check', 'color' => '#8b5cf6', 'bg' => 'rgba(139,92,246,.08)'],
                    ['label' => 'Non Aktif', 'value' => $totalMahasiswaTidakAktif, 'icon' => 'bx-user-x', 'color' => '#ff3e1d', 'bg' => 'rgba(255,62,29,.06)'],
                    ['label' => 'Lulus', 'value' => $totalMahasiswaLulus, 'icon' => 'bx-award', 'color' => '#10b981', 'bg' => 'rgba(16,185,129,.08)'],
                ];
            @endphp

            @foreach($cards as $card)
                <div class="col-6 col-lg-3">
                    <div class="dash-stat">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon-circle" style="background: {{ $card['bg'] }}; color: {{ $card['color'] }};">
                                <i class="bx {{ $card['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="stat-value">{{ number_format($card['value']) }}</div>
                        <div class="stat-label">{{ $card['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- ===== Charts Row ===== --}}
        <section class="row g-4 mb-4">
            {{-- Bar Chart: Mahasiswa per Tahun Masuk (2/3) --}}
            <div class="col-12 col-lg-8">
                <div class="dash-chart-card h-100">
                    <div class="chart-header d-flex align-items-center justify-content-between">
                        <div>
                            <div class="chart-title">Total Mahasiswa</div>
                            <div class="chart-sub">Berdasarkan Tahun Masuk</div>
                        </div>
                        <span class="badge bg-label-warning" style="font-size: .7rem;">{{ count($labels) }} Angkatan</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="mahasiswaBarChart" height="115"></canvas>
                    </div>
                </div>
            </div>

            {{-- Doughnut Chart: Mahasiswa per Prodi (1/3) --}}
            <div class="col-12 col-lg-4">
                <div class="dash-chart-card h-100">
                    <div class="chart-header">
                        <div class="chart-title">Program Studi</div>
                        <div class="chart-sub">Distribusi Mahasiswa Aktif</div>
                    </div>
                    <div class="chart-body d-flex flex-column align-items-center">
                        <div class="doughnut-wrapper mb-3" style="width: 200px; height: 200px;">
                            <canvas id="programStudiChart"></canvas>
                            <div class="doughnut-center">
                                <span class="center-value">{{ number_format($totalMahasiswaAktif) }}</span>
                                <span class="center-label">Total</span>
                            </div>
                        </div>
                        {{-- Legend --}}
                        <div class="w-100 mt-2" id="prodi-legend"></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== Schedule Table ===== --}}
        <section class="mb-4">
            <div class="dash-schedule">
                <div class="schedule-header">
                    <div>
                        <div class="schedule-title">
                            <i class="bx bx-calendar me-1" style="color: #696cff;"></i>
                            Jadwal Perkuliahan Hari Ini
                        </div>
                        <span style="font-size:.72rem; color: #8592a3; font-weight: 500;">
                            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                    <a href="{{ route('admin.jadwal.index') }}" class="btn btn-sm btn-label-primary"
                        style="font-size: .78rem; font-weight: 700;">
                        Lihat Semua <i class="bx bx-right-arrow-alt ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="padding-left: 1.75rem;">Waktu</th>
                                <th>Mata Kuliah</th>
                                <th>Dosen</th>
                                <th>Ruangan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jadwalHariIni as $jdw)
                                @php
                                    $jamMulai = \Carbon\Carbon::parse($jdw->jam_mulai)->format('H:i');
                                    $jamSelesai = \Carbon\Carbon::parse($jdw->jam_selesai)->format('H:i');
                                    $now = \Carbon\Carbon::now();

                                    // Determine status
                                    $mulai = \Carbon\Carbon::parse($jdw->jam_mulai);
                                    $selesai = \Carbon\Carbon::parse($jdw->jam_selesai);
                                    if ($now->between($mulai, $selesai)) {
                                        $statusClass = 'berlangsung';
                                        $statusText = 'Berlangsung';
                                    } elseif ($now->lt($mulai)) {
                                        $statusClass = 'menunggu';
                                        $statusText = 'Menunggu';
                                    } else {
                                        $statusClass = 'selesai';
                                        $statusText = 'Selesai';
                                    }

                                    $mkNama = $jdw->kurikulum->mataKuliah->nama ?? '-';
                                    $prodiNama = $jdw->programStudi->singkat ?? ($jdw->programStudi->nama ?? '-');
                                    $smt = $jdw->kurikulum->mataKuliah->smt ?? '-';

                                    $dosenNames = collect();
                                    if ($jdw->kurikulum && $jdw->kurikulum->dosenToMatakuliah) {
                                        $dosenNames = $jdw->kurikulum->dosenToMatakuliah->map(function ($dm) {
                                            return $dm->dosen->nama ?? '-';
                                        });
                                    }
                                    $dosenDisplay = $dosenNames->isNotEmpty() ? $dosenNames->first() : '-';

                                    $ruangan = $jdw->ruangan->nama ?? '-';
                                @endphp
                                <tr>
                                    <td style="padding-left: 1.75rem;">
                                        <span class="fw-bold" style="color: #1a1c1e;">{{ $jamMulai }} - {{ $jamSelesai }}</span>
                                    </td>
                                    <td>
                                        <div class="schedule-mk-name">{{ $mkNama }}</div>
                                        <div class="schedule-mk-sub">Prodi {{ $prodiNama }} — Semester {{ $smt }}</div>
                                    </td>
                                    <td>
                                        <span style="font-weight: 500; color: #566a7f;">{{ $dosenDisplay }}</span>
                                    </td>
                                    <td>
                                        <span class="schedule-room">{{ $ruangan }}</span>
                                    </td>
                                    <td>
                                        <span class="schedule-status {{ $statusClass }}">
                                            <span class="dot"></span>
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center gap-2">
                                            <i class="bx bx-calendar-x" style="font-size: 2.5rem; color: #c2c6de;"></i>
                                            <span class="text-muted fw-semibold">Tidak ada jadwal untuk hari ini.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
@endsection

@push('head')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ===== Doughnut Chart: Program Studi =====
            (function () {
                const ctx = document.getElementById('programStudiChart').getContext('2d');
                const labels = @json($programStudiLabels);
                const data = @json($programStudiCounts);
                const jurusanIds = @json($programStudiJurusanIds);

                const colorMap = {
                    13211: '#f35a13ff',   // primary
                    48201: '#ffb782',   // tertiary
                    15401: '#b7b8fd',   // secondary
                };
                const fallbackColors = ['#696cff', '#b7b8fd', '#ffb782', '#03c3ec', '#71dd37', '#8592a3'];
                let fi = 0;
                const colors = jurusanIds.map(id => colorMap[id] || fallbackColors[(fi++) % fallbackColors.length]);

                const totalAktif = data.reduce((a, b) => a + b, 0);

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: colors,
                            borderWidth: 0,
                            hoverOffset: 6,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#2d3133',
                                cornerRadius: 8,
                                padding: 12,
                                titleFont: { size: 13, weight: '700' },
                                bodyFont: { size: 12 },
                                callbacks: {
                                    label: function (ctx) {
                                        const val = ctx.raw || 0;
                                        const pct = totalAktif > 0 ? ((val / totalAktif) * 100).toFixed(1) : 0;
                                        return ` ${ctx.label}: ${val} mhs (${pct}%)`;
                                    }
                                }
                            },
                        },
                    },
                });

                // Custom legend
                const legendEl = document.getElementById('prodi-legend');
                if (legendEl) {
                    let html = '';
                    labels.forEach((lbl, i) => {
                        const pct = totalAktif > 0 ? ((data[i] / totalAktif) * 100).toFixed(0) : 0;
                        html += `<div class="legend-item">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="legend-dot" style="background:${colors[i]}"></div>
                                                <span class="legend-label">${lbl}</span>
                                            </div>
                                            <span class="legend-pct">${pct}%</span>
                                        </div>`;
                    });
                    legendEl.innerHTML = html;
                }
            })();

            // ===== Bar Chart: Mahasiswa per Tahun Masuk =====
            (function () {
                const ctx = document.getElementById('mahasiswaBarChart').getContext('2d');
                const labels = @json($labels);
                const data = @json($values);

                // Gradient
                const gradient = ctx.createLinearGradient(0, 0, 0, 320);
                gradient.addColorStop(0, 'rgba(243,90,19,.80)');
                gradient.addColorStop(1, 'rgba(255,171,0,.25)');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Mahasiswa',
                            data: data,
                            backgroundColor: gradient,
                            borderColor: 'rgba(243,90,19,.9)',
                            borderWidth: 0,
                            borderRadius: 8,
                            borderSkipped: false,
                            maxBarThickness: 42,
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#2d3133',
                                cornerRadius: 8,
                                padding: 12,
                                titleFont: { size: 13, weight: '700' },
                                bodyFont: { size: 12 },
                                callbacks: {
                                    label: function (ctx) {
                                        return ` ${ctx.raw} Mahasiswa`;
                                    }
                                }
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 11, weight: '700' },
                                    color: '#8592a3',
                                },
                            },
                            y: {
                                grid: {
                                    color: 'rgba(0,0,0,.04)',
                                    drawBorder: false,
                                },
                                ticks: {
                                    font: { size: 11 },
                                    color: '#8592a3',
                                },
                                beginAtZero: true,
                            },
                        },
                    },
                });
            })();

        });
    </script>
@endpush