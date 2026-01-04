<?php
require_once '../core/SessionManager.php';
require_once '../core/Database.php';
SessionManager::startSecureSession();

// Si ya está logueado, redirigir al dashboard
if (SessionManager::isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - AgroConecta</title>
    <meta name="description" content="Inicia sesión en AgroConecta para acceder a productos frescos y naturales directo del campo">
    
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
            color: #2c3e50 !important;
            text-decoration: none;
            font-size: 1.25rem;
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
        .navbar-custom .nav-link.active {
            color: #28a745 !important;
            background-color: rgba(40, 167, 69, 0.1);
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
        
        /* Forzar visibilidad del texto */
        .auth-container label,
        .auth-container p,
        .auth-container span,
        .form-check-label,
        .auth-footer p {
            color: #2c3e50 !important;
        }
        
        .auth-subtitle {
            color: #6c757d !important;
        }
        
        .forgot-password-link {
            color: #28a745 !important;
        }
        
        .form-control::placeholder {
            color: #9ca3af !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <span style="font-size: 1.5rem; color: #28a745; margin-right: 8px;">🌱</span>
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
                        <a class="nav-link active" href="login.php">
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
                            <h2 class="auth-title">¡Bienvenido de vuelta!</h2>
                            <p class="auth-subtitle">
                                Inicia sesión para acceder a los mejores productos frescos del campo
                            </p>
                        </div>

                        <!-- Alertas -->
                        <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                            
                            <!-- Mensaje especial para cuentas no verificadas -->
                            <?php if(strpos($_SESSION['last_error'] ?? '', 'verificar tu cuenta') !== false): ?>
                            <div class="mt-3">
                                <a href="email-verification.php" class="btn btn-sm btn-warning">
                                    <i class="fas fa-envelope me-1"></i>
                                    Reenviar Email de Verificación
                                </a>
                            </div>
                            <?php unset($_SESSION['last_error']); endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form class="auth-form" id="loginForm" action="process-login.php" method="POST">
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
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Contraseña -->
                            <div class="form-group">
                                <label for="password">
                                    Contraseña <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Tu contraseña"
                                           required>
                                    <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Recordarme y Olvidé contraseña -->
                            <div class="remember-forgot">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">
                                        Recordarme
                                    </label>
                                </div>
                                <a href="forgot-password.php" class="forgot-password-link">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>

                            <!-- Botón de Login -->
                            <button type="submit" class="btn btn-primary" id="loginBtn">
                                Iniciar Sesión
                            </button>
                        </form>

                        <!-- Footer -->
                        <div class="auth-footer">
                            <p>
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
                            <i class="fas fa-lock"></i>
                            <span>Nunca compartimos tu información personal</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Benefits Sidebar -->
            <div class="col-lg-6 d-none d-lg-block">
                <div class="auth-benefits">
                    <div class="benefits-content">
                        <h3>¿Por qué unirse a AgroConecta?</h3>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Productos 100% Frescos</h4>
                                <p>Acceso directo a productos recién cosechados de agricultores locales</p>
                            </div>
                        </div>

                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Entrega Rápida</h4>
                                <p>Entregas el mismo día en área metropolitana</p>
                            </div>
                        </div>

                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Apoyo a Agricultores</h4>
                                <p>Contribuyes directamente al sustento de familias agrícolas</p>
                            </div>
                        </div>

                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Calidad Garantizada</h4>
                                <p>Garantía de satisfacción del 100% o te devolvemos tu dinero</p>
                            </div>
                        </div>

                        <div class="testimonial">
                            <div class="testimonial-content">
                                <i class="fas fa-quote-left"></i>
                                <p>"AgroConecta cambió mi forma de comprar alimentos. Productos frescos, precios justos y apoyo a productores locales. ¡Excelente!"</p>
                            </div>
                            <div class="testimonial-author">
                                <img src="img/customers/testimonial1.jpg" alt="María González" class="author-image">
                                <div class="author-info">
                                    <h5>María González</h5>
                                    <p>Cliente desde 2023</p>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="stats">
                            <div class="stat-item">
                                <span class="stat-number">50K+</span>
                                <span class="stat-label">Clientes Activos</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">500+</span>
                                <span class="stat-label">Agricultores</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">1M+</span>
                                <span class="stat-label">Productos Entregados</span>
                            </div>
                        </div>
                    </div>

                    <div class="benefits-image">
                        <img src="img/auth/farmer-vegetables.jpg" 
                             alt="Agricultor con vegetales frescos" 
                             class="img-fluid">
                        <div class="image-overlay">
                            <div class="overlay-content">
                                <h4>¡Únete a nuestra comunidad!</h4>
                                <p>Más de 50,000 familias confían en nosotros</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Loading Overlay -->
<div class="auth-loading" id="authLoading" style="display: none;">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <p>Iniciando sesión...</p>
    </div>
</div>

<script>
// Demo data for development
const demoAccounts = {
    cliente: {
        email: 'cliente@demo.com',
        password: 'demo123'
    },
    vendedor: {
        email: 'vendedor@demo.com',
        password: 'demo123'
    },
    admin: {
        email: 'admin@demo.com',
        password: 'demo123'
    }
};

function fillDemoData(type) {
    if (demoAccounts[type]) {
        document.getElementById('email').value = demoAccounts[type].email;
        document.getElementById('password').value = demoAccounts[type].password;
        
        // Show toast
        if (typeof AgroConectaUtils !== 'undefined') {
            AgroConectaUtils.showToast(`Datos de ${type} demo cargados`, 'info');
        }
    }
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggle = input.nextElementSibling;
    const icon = toggle.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'far fa-eye';
    }
}

function loginWithGoogle() {
    // Implementar login con Google OAuth
    console.log('Login with Google');
    if (typeof AgroConectaUtils !== 'undefined') {
        AgroConectaUtils.showToast('Función de Google OAuth próximamente disponible', 'info');
    }
}

function loginWithFacebook() {
    // Implementar login con Facebook
    console.log('Login with Facebook');
    if (typeof AgroConectaUtils !== 'undefined') {
        AgroConectaUtils.showToast('Función de Facebook Login próximamente disponible', 'info');
    }
}

// Form validation and submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Show loading state
            const btnText = loginBtn.querySelector('.btn-text');
            const btnLoading = loginBtn.querySelector('.btn-loading');
            
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
            loginBtn.disabled = true;
            
            // Show loading overlay
            document.getElementById('authLoading').style.display = 'flex';
        });
    }
    
});
</script>

</body>
</html>