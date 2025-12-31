<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Denegado | AgroConecta</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .error-container {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: #e17055;
            margin: 0;
            line-height: 1;
        }
        .error-title {
            font-size: 2rem;
            margin: 1rem 0;
            color: #2c3e50;
        }
        .error-message {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin: 1.5rem 0;
            line-height: 1.6;
        }
        .home-link {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
            margin: 0.5rem;
        }
        .home-link:hover {
            background: #219a52;
        }
        .login-link {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
            margin: 0.5rem;
        }
        .login-link:hover {
            background: #2980b9;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #e17055;
            padding: 1rem;
            margin: 1.5rem 0;
            text-align: left;
        }
        .info-box h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .agro-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .suggestions {
            margin-top: 1.5rem;
            text-align: left;
        }
        .suggestions h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        .suggestions ul {
            color: #7f8c8d;
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="agro-icon">🔒</div>
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Acceso Denegado</h2>
        <p class="error-message">
            No tienes permisos para acceder a esta sección de AgroConecta. 
            Esta área está restringida a usuarios autorizados.
        </p>
        
        <div class="info-box">
            <h3>💡 ¿Por qué veo este mensaje?</h3>
            <ul>
                <li><strong>No has iniciado sesión:</strong> Algunas páginas requieren que te identifiques</li>
                <li><strong>No tienes permisos suficientes:</strong> Tu cuenta no tiene acceso a esta funcionalidad</li>
                <li><strong>Sesión expirada:</strong> Tu sesión pudo haber caducado por inactividad</li>
                <li><strong>Área administrativa:</strong> Solo los administradores pueden acceder</li>
            </ul>
        </div>
        
        <div class="suggestions">
            <h3>🚀 ¿Qué puedes hacer?</h3>
            <ul>
                <li>Iniciar sesión con una cuenta autorizada</li>
                <li>Contactar al administrador si necesitas acceso</li>
                <li>Verificar que tienes los permisos correctos</li>
                <li>Regresar a la página principal</li>
            </ul>
        </div>
        
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="/login" class="login-link">🔑 Iniciar Sesión</a>
        <?php else: ?>
            <a href="/perfil" class="login-link">👤 Mi Perfil</a>
        <?php endif; ?>
        <a href="/" class="home-link">🏠 Volver al Inicio</a>
        
        <div style="margin-top: 2rem; font-size: 0.9rem; color: #95a5a6;">
            Si crees que esto es un error, contacta nuestro 
            <a href="/contacto" style="color: #3498db;">soporte técnico</a>
        </div>
    </div>

    <script>
        // Redireccionar después de 10 segundos si no hay sesión
        <?php if(!isset($_SESSION['user_id'])): ?>
        let countdown = 10;
        const timer = setInterval(() => {
            countdown--;
            if(countdown <= 0) {
                window.location.href = '/login';
                clearInterval(timer);
            }
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>