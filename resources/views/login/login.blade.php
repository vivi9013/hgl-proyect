<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital - Iniciar Sesión</title>
    @vite(['resources/css/app.css'])
</head>
<body class="login-container">
    <div class="login-card">
        <h2 class="login-title">Hospital</h2>
        
        <form action="#" method="POST">
            @csrf
            <div class="form-group">
                <label for="email" class="login-label">Correo Electrónico</label>
                <input id="email" name="email" type="email" required class="login-input" placeholder="usuario@hospital.com">
            </div>

            <div class="form-group">
                <label for="password" class="login-label">Contraseña</label>
                <input id="password" name="password" type="password" required class="login-input" placeholder="••••••••">
            </div>

            <button type="submit" class="login-button">
                Entrar al sistema
            </button>
        </form>
    </div>
</body>
</html>
