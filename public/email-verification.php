<?php
require_once '../core/SessionManager.php';
require_once '../core/Database.php';
SessionManager::startSecureSession();

// Si ya está logueado, redirigir
if (SessionManager::isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Email - AgroConecta</title>
    <meta name="description" content="Verifica tu cuenta de AgroConecta">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/app.css">
    <link rel="stylesheet" href="css/auth.css">
    
    <style>
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        body {
            padding-top: 76px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        .verification-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 3rem 2.5rem;
            max-width: 500px;
            margin: 2rem auto;
            text-align: center;
        }
        .verification-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .verification-title {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .verification-subtitle {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        .btn-resend {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 15px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        .btn-resend:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }
        .btn-login {
            background: transparent;
            border: 2px solid #28a745;
            color: #28a745;
            border-radius: 15px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: #28a745;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <span class="me-2">🌱</span>
                <span>AgroConecta</span>
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="login.php">
                    <i class="fas fa-sign-in-alt me-1"></i>
                    Iniciar Sesión
                </a>
                <a class="nav-link btn btn-success text-white ms-2" href="register.php">
                    <i class="fas fa-user-plus me-1"></i>
                    Registrarse
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="verification-container">
            <!-- Header -->
            <div class="verification-icon">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <h1 class="verification-title">Verificación de Email</h1>
            <p class="verification-subtitle">
                Te hemos enviado un enlace de verificación a tu correo electrónico. 
                Revisa tu bandeja de entrada (y la carpeta de spam) y haz clic en el enlace para activar tu cuenta.
            </p>

            <!-- Alertas -->
            <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>

            <!-- Reenviar Email Form -->
            <form action="resend-verification.php" method="POST" class="mb-4">
                <input type="hidden" name="csrf_token" value="<?= SessionManager::generateCSRF() ?>">
                <div class="mb-3">
                    <label for="email" class="form-label text-start d-block">
                        Correo Electrónico
                    </label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="tu@correo.com"
                           required>
                </div>
                <button type="submit" class="btn btn-resend btn-success">
                    <i class="fas fa-paper-plane me-2"></i>
                    Reenviar Email de Verificación
                </button>
            </form>

            <div class="mt-4">
                <a href="login.php" class="btn btn-login">
                    <i class="fas fa-arrow-left me-2"></i>
                    Volver al Login
                </a>
            </div>

            <!-- Info adicional -->
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="mb-2">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    ¿No recibes el email?
                </h6>
                <ul class="list-unstyled text-muted small mb-0">
                    <li>• Revisa tu carpeta de spam o correo no deseado</li>
                    <li>• Verifica que el email esté escrito correctamente</li>
                    <li>• Espera unos minutos, puede haber demora</li>
                    <li>• Usa el formulario de arriba para reenviar</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>