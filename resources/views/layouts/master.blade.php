<!DOCTYPE html>

<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('dashboard_assets/assets/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    @php
        $settings = \App\Models\Setting::first();
    @endphp
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $settings->name }} - @yield('title')</title>

    <meta name="description" content="{{ $settings->name }}">

    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="{{ $settings->name }}">
    <meta itemprop="description" content="{{ $settings->name }}">

    <!-- Facebook Meta Tags -->
    <meta property="og:url" content="{{ $settings->website_url }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $settings->name }}">
    <meta property="og:description" content="{{ $settings->name }}">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $settings->name }}">
    <meta name="twitter:description" content="{{ $settings->name }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Favicon -->
    <link rel="icon"
        href="{{ $settings->favicon ? asset('storage/' . $settings->favicon) : asset('default/favicon.ico') }}"
        type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/fonts/boxicons.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/core.css') }}"
        class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/custom.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/pages/page-profile.css') }}">

    <!-- Vendors CSS -->
    <link rel="stylesheet"
        href="{{ asset('dashboard_assets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    {{-- Datatables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    {{-- Select2 CSS --}}
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/select2/css/select2.min.css') }}">

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('dashboard_assets/assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('dashboard_assets/assets/js/config.js') }}"></script>


    @stack('head')
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">

        <div class="layout-container">
            <!-- Menu -->
            <div class="pre-loader is-load" id="preloader">
                <div class="circle-loader"></div>
            </div>
            @include('components.sidebar')
            <!-- / Menu -->
            @include('sweetalert::alert')
            @include('components.notification')
            <!-- Layout container -->
            <div class="layout-page">

                <!-- Navbar -->
                @include('components.navbar')
                <!-- / Navbar -->
                <div id="toast-overlay"></div>

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('components.footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src="{{ asset('dashboard_assets/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/js/menu.js') }}"></script>

    <!-- endbuild -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Vendors JS -->
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/chartjs/chartjs.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/chartjs/charts-chartjs.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    {{-- Select2 JS --}}
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/select2/js/select2.full.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Main JS -->
    <script src="{{ asset('dashboard_assets/assets/js/main.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/custom.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('dashboard_assets/assets/js/dashboards-analytics.js') }}"></script>
    @include('sweetalert::alert', ['cdn' => "https://cdn.jsdelivr.net/npm/sweetalert2@9"])

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- @include('components.notification') --}}
    <script>
        function formatJam(jam) {
            if (!jam) return '';
            const [h, m] = jam.split(':');
            return `${h}:${m} WIB`;
        }

        $('#jam_mulai').on('input', function () {
            $('#keterangan_jam_mulai').text(formatJam(this.value));
        }).trigger('input');

        $('#jam_selesai').on('input', function () {
            $('#keterangan_jam_selesai').text(formatJam(this.value));
        }).trigger('input');

    </script>

    <script>
        $(document).ready(function () {
            // Inisialisasi Select2
            $('.select2').select2();
        });

        // Hide preloader when page loads
        window.addEventListener('load', function () {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.display = 'none';
            }
        });
    </script>
 @stack('script')

</body>

</html>