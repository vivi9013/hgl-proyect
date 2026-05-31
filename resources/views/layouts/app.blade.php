<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hospital System')</title>
    
    <!-- Bootstrap & Icons (Via Vite/NPM) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- FontAwesome 4.7 CDN for legacy module icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    {{-- Variable CSS dinámica del tema activo del usuario --}}
    <style>
        :root {
            --theme-primary: {{ session('s_colGr', '#2980b9') }};
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
