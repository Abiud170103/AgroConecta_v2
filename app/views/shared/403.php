<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 403 - Acceso Prohibido | AgroConecta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .error-container {
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 90%;
            backdrop-filter: blur(10px);
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #fd7e14;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        .error-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .error-message {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn-home, .btn-login {
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin: 0 10px;
        }
        .btn-home {
            background: linear-gradient(45deg, #28a745, #20c997);
        }
        .btn-login {
            background: linear-gradient(45deg, #007bff, #0056b3);
        }
        .btn-home:hover, .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            color: white;
        }
        .icon-container {
            font-size: 4rem;
            color: #fd7e14;
            margin-bottom: 1rem;
            animation: shake 1s infinite;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .brand-logo {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 1.5rem;
            font-weight: bold;
            color: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body>
    <div class="brand-logo">
        <i class="fas fa-seedling"></i> AgroConecta
    </div>
    
    <div class="error-container">
        <div class="error-box">
            <div class="icon-container">
                <i class="fas fa-lock"></i>
            </div>
            
            <div class="error-code">403</div>
            <h1 class="error-title">Acceso Prohibido</h1>
            <p class="error-message">
                Lo sentimos, no tienes permisos para acceder a esta página.<br>
                Es posible que necesites iniciar sesión o que no tengas los privilegios necesarios.
            </p>
            
            <div class="mt-4">
                <a href="/" class="btn-home">
                    <i class="fas fa-home me-2"></i>
                    Ir al Inicio
                </a>
                <a href="/login" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Iniciar Sesión
                </a>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    Si crees que esto es un error, contacta al administrador del sistema
                </small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>