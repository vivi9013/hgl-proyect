<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hospital System')</title>
    
    <!-- Bootstrap & Icons (Via Vite/NPM) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- FontAwesome 4.7 CDN for legacy module icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    {{-- Variable CSS dinámica del tema activo del usuario --}}
    @php
        $themeColor = session('s_colGr', '#2980b9');
        $hex = str_replace('#', '', $themeColor);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        
        // Threshold: YIQ >= 150 means light background, so text must be black/dark
        $themeText = $yiq >= 150 ? '#000000' : '#ffffff';
        $themeTextMuted = $yiq >= 150 ? 'rgba(0, 0, 0, 0.65)' : 'rgba(255, 255, 255, 0.7)';
        $themeHoverBg = $yiq >= 150 ? 'rgba(0, 0, 0, 0.08)' : 'rgba(255, 255, 255, 0.1)';
        $themeActiveBg = $yiq >= 150 ? 'rgba(0, 0, 0, 0.15)' : 'rgba(255, 255, 255, 0.22)';
    @endphp
    <style>
        :root {
            --theme-primary: {{ $themeColor }};
            --theme-text: {{ $themeText }};
            --theme-text-muted: {{ $themeTextMuted }};
            --theme-hover-bg: {{ $themeHoverBg }};
            --theme-active-bg: {{ $themeActiveBg }};
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="main-wrapper">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Content Area -->
        <div class="content-area">
            <!-- Header -->
            @include('layouts.header')

            <!-- Main Content -->
            <main class="main-content">
                @yield('content')
            </main>

            <!-- Footer -->
            @include('layouts.footer')
        </div>
    </div>


@stack('scripts')
</body>
</html>
