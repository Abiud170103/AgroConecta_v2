<?php 
$title = "Iniciar Sesión";
$currentPage = "login";
$metaDescription = "Inicia sesión en AgroConecta para acceder a productos frescos y naturales directo del campo";
$metaKeywords = "login, iniciar sesión, agroconecta, productos frescos";
$additionalCSS = ['auth.css'];
$additionalJS = ['auth.js'];
$bodyClass = "auth-page";

ob_start();
?>

<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-container">
                    <!-- Header -->
                    <div class="auth-header">
                        <div class="auth-logo">
                            <img src="<?= asset('img/logo.png') ?>" alt="AgroConecta" class="logo-image">
                            <h1 class="logo-text">AgroConecta</h1>
                        </div>
                        <h2 class="auth-title">¡Bienvenido de vuelta!</h2>
                        <p class="auth-subtitle">
                            Inicia sesión para acceder a los mejores productos frescos del campo
                        </p>
                    </div>

                    <!-- Social Login -->
                    <div class="social-login">
                        <button type="button" class="btn btn-social btn-google" onclick="loginWithGoogle()">
                            <i class="fab fa-google"></i>
                            Continuar con Google
                        </button>
                        <button type="button" class="btn btn-social btn-facebook" onclick="loginWithFacebook()">
                            <i class="fab fa-facebook-f"></i>
                            Continuar con Facebook
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="auth-divider">
                        <span>o inicia sesión con tu correo</span>
                    </div>

                    <!-- Login Form -->
                    <form class="auth-form" id="loginForm" action="<?= url('/login') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i>
                                Correo Electrónico
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" 
                                   placeholder="tu@correo.com"
                                   value="<?= old('email') ?>"
                                   required 
                                   autofocus>
                            <?php if (has_error('email')): ?>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <?= error('email') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Contraseña
                            </label>
                            <div class="password-input">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" 
                                       placeholder="Tu contraseña"
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <?php if (has_error('password')): ?>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <?= error('password') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-options">
                            <label class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input" value="1">
                                <span class="form-check-label">Recordarme</span>
                            </label>
                            <a href="<?= url('/password/reset') ?>" class="forgot-password">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>

                        <!-- Login Button -->
                        <button type="submit" class="btn btn-primary btn-auth" id="loginBtn">
                            <span class="btn-text">
                                <i class="fas fa-sign-in-alt"></i>
                                Iniciar Sesión
                            </span>
                            <span class="btn-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i>
                                Iniciando sesión...
                            </span>
                        </button>

                        <!-- Demo Accounts -->
                        <?php if ($_ENV['APP_ENV'] === 'development'): ?>
                            <div class="demo-accounts">
                                <p class="demo-title">
                                    <i class="fas fa-flask"></i>
                                    Cuentas de Demostración
                                </p>
                                <div class="demo-buttons">
                                    <button type="button" class="btn btn-demo" onclick="fillDemoData('cliente')">
                                        <i class="fas fa-user"></i>
                                        Cliente Demo
                                    </button>
                                    <button type="button" class="btn btn-demo" onclick="fillDemoData('vendedor')">
                                        <i class="fas fa-store"></i>
                                        Vendedor Demo
                                    </button>
                                    <button type="button" class="btn btn-demo" onclick="fillDemoData('admin')">
                                        <i class="fas fa-crown"></i>
                                        Admin Demo
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </form>

                    <!-- Additional Options -->
                    <div class="auth-additional">
                        <div class="auth-links">
                            <p>¿No tienes cuenta?</p>
                            <a href="<?= url('/registro') ?>" class="register-link">
                                <i class="fas fa-user-plus"></i>
                                Crear cuenta gratis
                            </a>
                        </div>
                        
                        <div class="help-links">
                            <a href="<?= url('/ayuda/login') ?>" class="help-link">
                                <i class="fas fa-question-circle"></i>
                                ¿Problemas para iniciar sesión?
                            </a>
                        </div>
                    </div>

                    <!-- Security Info -->
                    <div class="security-info">
                        <div class="security-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Tus datos están protegidos con encriptación SSL</span>
                        </div>
                        <div class="security-item">
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
                                <img src="<?= asset('img/customers/testimonial1.jpg') ?>" alt="María González" class="author-image">
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
                        <img src="<?= asset('img/auth/farmer-vegetables.jpg') ?>" 
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
    
    // Auto-fill demo data on double click (development only)
    <?php if ($_ENV['APP_ENV'] === 'development'): ?>
    document.getElementById('email').addEventListener('dblclick', function() {
        fillDemoData('cliente');
    });
    <?php endif; ?>
});
</script>

<?php 
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>