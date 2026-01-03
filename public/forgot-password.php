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
    <title>Recuperar Contraseña - AgroConecta</title>
    <meta name="description" content="Recupera tu contraseña de AgroConecta">
    
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
        .auth-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 3rem 2.5rem;
            max-width: 500px;
            margin: 2rem auto;
            animation: fadeInUp 0.5s ease;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 0.8rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }
        .btn-success {
            background: #28a745;
            border: none;
            border-radius: 12px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            background: #1e7e34;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .btn-outline-primary {
            border-radius: 12px;
            border-width: 2px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img src="images/logo.png" alt="AgroConecta" height="40">
                <strong>AgroConecta</strong>
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">Inicio</a>
                <a class="nav-link" href="login.php">Iniciar Sesión</a>
                <a class="nav-link btn btn-success text-white px-3" href="register.php">Registrarse</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-container">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-key text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h2 class="fw-bold text-dark">Recuperar Contraseña</h2>
                        <p class="text-muted">Te enviaremos un enlace para recuperar tu contraseña</p>
                    </div>

                    <?php if (SessionManager::getFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo SessionManager::getFlash('error'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <?php if (SessionManager::getFlash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo SessionManager::getFlash('success'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form id="forgotPasswordForm" method="POST" action="../app/controllers/AuthController.php?action=forgotPassword">
                        <input type="hidden" name="_token" value="<?php echo SessionManager::generateCSRF(); ?>">
                        
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-success"></i>Correo Electrónico
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   placeholder="Ingresa tu correo electrónico">
                        </div>

                        <button type="submit" class="btn btn-success w-100 mb-4">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Enlace de Recuperación
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">
                            ¿Recordaste tu contraseña? 
                            <a href="login.php" class="text-success fw-semibold text-decoration-none">
                                Iniciar Sesión
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand img {
            height: 40px;
        }
        .navbar-custom .nav-link {
            color: #2c3e50 !important;
            font-weight: 600;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin: 0 0.25rem;
        }
        .navbar-custom .nav-link:hover {
            color: #28a745 !important;
            background-color: rgba(40, 167, 69, 0.1);
            transform: translateY(-1px);
        }
        .navbar-custom .nav-link:focus {
            color: #28a745 !important;
        }
        .navbar-custom .nav-link.btn.btn-success {
            color: white !important;
        }
        .navbar-custom .nav-link.btn.btn-success:hover {
            color: white !important;
            background-color: #1e7e34 !important;
            transform: translateY(-1px);
        }
        body {
            padding-top: 76px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        .auth-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 3rem 2.5rem;
            max-width: 500px;
            margin: 2rem auto;
            animation: fadeInUp 0.5s ease;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="img/logo.png" alt="AgroConecta" class="me-2">
                <span class="fw-bold text-success">AgroConecta</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php#productos">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php#nosotros">Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Iniciar Sesión
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-success text-white px-3 rounded-pill" href="register.php">
                            <i class="fas fa-user-plus me-1"></i>
                            Registrarse
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="auth-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="auth-container">
                        <!-- Header -->
                        <div class="auth-header">
                            <div class="auth-logo">
                                <img src="img/logo.png" alt="AgroConecta" class="logo-image">
                                <h1 class="logo-text">AgroConecta</h1>
                            </div>
                            <h2 class="auth-title">¿Olvidaste tu contraseña?</h2>
                            <p class="auth-subtitle">
                                No te preocupes, te enviaremos instrucciones para recuperarla
                            </p>
                        </div>

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

                        <!-- Forgot Password Form -->
                        <form class="auth-form" id="forgotPasswordForm" action="/AgroConecta_v2/public/olvide-password" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            
                            <!-- Email -->
                            <div class="form-group">
                                <label for="email">
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           placeholder="tu@correo.com"
                                           required 
                                           autofocus>
                                </div>
                                <small class="form-text text-muted">
                                    Ingresa el correo con el que te registraste
                                </small>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Botón de Enviar -->
                            <button type="submit" class="btn btn-primary" id="resetBtn">
                                <i class="fas fa-paper-plane"></i>
                                Enviar Instrucciones
                            </button>
                        </form>

                        <!-- Footer -->
                        <div class="auth-footer">
                            <p>
                                <a href="login.php">
                                    <i class="fas fa-arrow-left"></i>
                                    Volver al inicio de sesión
                                </a>
                            </p>
                            <p style="margin-top: 1rem;">
                                ¿No tienes cuenta? 
                                <a href="register.php">Regístrate aquí</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/public/js/auth.js"></script>
</body>
</html>
