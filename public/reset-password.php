<?php
require_once '../core/SessionManager.php';
require_once '../core/Database.php';
SessionManager::startSecureSession();

// Obtener token de la URL
$token = $_GET['token'] ?? '';

// Si no hay token, redirigir
if (empty($token)) {
    SessionManager::setFlash('error', 'Token de recuperación inválido');
    header('Location: login.php');
    exit;
}

// Verificar que el token sea válido
require_once '../app/models/Usuario.php';
$userModel = new Usuario();
$user = $userModel->verifyResetToken($token);

if (!$user) {
    SessionManager::setFlash('error', 'Token de recuperación inválido o expirado');
    header('Location: login.php');
    exit;
}

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
    <title>Nueva Contraseña - AgroConecta</title>
    <meta name="description" content="Establece tu nueva contraseña para AgroConecta">
    
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
        .navbar-brand {
            display: flex;
            align-items: center;
            color: #2c3e50 !important;
            font-weight: bold;
            text-decoration: none;
        }
        .navbar-nav .nav-link {
            color: #2c3e50 !important;
            font-weight: 600;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin: 0 0.25rem;
        }
        .navbar-nav .nav-link:hover {
            color: #28a745 !important;
            background-color: rgba(40, 167, 69, 0.1);
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
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #6c757d;
            cursor: pointer;
        }
        .password-toggle:hover {
            color: #28a745;
        }
        .password-field {
            position: relative;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <span style="font-size: 1.5rem; color: #28a745; margin-right: 8px;">🌱</span>
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
                        <h2 class="fw-bold text-dark">Nueva Contraseña</h2>
                        <p class="text-muted">Ingresa tu nueva contraseña para acceder a tu cuenta</p>
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

                    <form id="resetPasswordForm" method="POST" action="process-reset-password.php">
                        <input type="hidden" name="_token" value="<?php echo SessionManager::generateCSRF(); ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-success"></i>Nueva Contraseña
                            </label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="password" name="password" required 
                                       minlength="6" placeholder="Mínimo 6 caracteres" autofocus>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="password-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirm" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-success"></i>Confirmar Contraseña
                            </label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required 
                                       minlength="6" placeholder="Repite tu nueva contraseña">
                                <button type="button" class="password-toggle" onclick="togglePassword('password_confirm')">
                                    <i class="fas fa-eye" id="password_confirm-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 mb-4">
                            <i class="fas fa-key me-2"></i>Actualizar Contraseña
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">
                            <a href="login.php" class="text-success fw-semibold text-decoration-none">
                                <i class="fas fa-arrow-left me-2"></i>Volver al inicio de sesión
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eye = document.getElementById(fieldId + '-eye');
            
            if (field.type === 'password') {
                field.type = 'text';
                eye.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                eye.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Validate form
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirm').value;
            
            if (password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
                return;
            }
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return;
            }
        });
    </script>
</body>
</html>