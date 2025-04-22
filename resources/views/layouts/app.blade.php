<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php
        $settings = \App\Models\Setting::first();
    @endphp

    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Title & Favicon -->
    <title>{{ $settings->name ?? config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ $settings->favicon ? asset('storage/' . $settings->favicon) : asset('default/favicon.ico') }}" type="image/x-icon">

    <!-- Fonts and Icons -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('assets/css/material-dashboard.css?v=3.2.0') }}" rel="stylesheet" />

    <!-- Toastr Notifications -->
    <script>
        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif
    </script>
</head>

<body class="g-sidenav-show  bg-gray-100">
    <div id="app">
        @auth
            @include('layouts.sidebar')
        @endauth

        <!-- Main Content -->
         <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
            @yield('content')
        </main>

        @include('layouts.footer')
    </div>
</body>
</html>

