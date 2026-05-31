<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hospital - Iniciar Sesión')</title>
    
    <!-- Activos procesados por Vite (Bootstrap + Custom CSS + JS) -->
    @vite(['resources/css/app.css', 'resources/css/loginstyle.css', 'resources/js/app.js'])

@push('styles')
<link rel="stylesheet" href="{{ asset('public/assets/css/login/loginstyle.css') }}">
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/login/loginstyle.css') }}">
@endpush
</head>
<body class="login-container">

    @yield('content')
    <footer id="foot">
        <label class="foot">MGTI. Pablo Adrián Pérez Briseño</label>
    </footer>

</body>
</html>
