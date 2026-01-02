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
    <link rel="stylesheet" href="/AgroConecta_v2/public/css/app.css">
    <link rel="stylesheet" href="/AgroConecta_v2/public/css/auth.css">
    
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/AgroConecta_v2/">
                <img src="/AgroConecta_v2/public/img/logo.png" alt="AgroConecta" class="me-2">
                <span class="fw-bold text-success">AgroConecta</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/AgroConecta_v2/#productos">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/AgroConecta_v2/#nosotros">Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/AgroConecta_v2/public/login">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Iniciar Sesión
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-success text-white px-3 rounded-pill" href="/AgroConecta_v2/public/registro">
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
                                <img src="/AgroConecta_v2/public/img/logo.png" alt="AgroConecta" class="logo-image">
                                <h1 class="logo-text">AgroConecta</h1>
                            </div>
                            <h2 class="auth-title">¡Bienvenido de vuelta!</h2>
                            <p class="auth-subtitle">
                                Inicia sesión para acceder a productos frescos directo del campo
                            </p>
                        </div>

                        <!-- Mensajes Flash -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form class="auth-form" id="loginForm" action="/AgroConecta_v2/public/login" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                            
                            <!-- Email -->
                            <div class="form-group">
                                <label for="email">
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrapper">
                                    <input 
                                        type="email" 
                                        class="form-control" 
                                        id="email" 
                                        name="email" 
                                        placeholder="Ingresa tu correo electrónico"
                                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                        required 
                                        autocomplete="email"
                                        maxlength="100"
                                    >
                                    <i class="fas fa-envelope input-icon"></i>
                                </div>
                                <div class="invalid-feedback" id="emailError"></div>
                            </div>

                            <!-- Password -->
                            <div class="form-group">
                                <label for="password">
                                    Contraseña <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrapper">
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="password" 
                                        name="password" 
                                        placeholder="Ingresa tu contraseña"
                                        required 
                                        autocomplete="current-password"
                                        minlength="8"
                                        maxlength="128"
                                    >
                                    <i class="fas fa-lock input-icon"></i>
                                    <button type="button" class="btn btn-link toggle-password" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="passwordError"></div>
                            </div>

                            <!-- Remember & Forgot Password -->
                            <div class="form-options">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">
                                        Recordarme
                                    </label>
                                </div>
                                <a href="/AgroConecta_v2/public/olvide-password" class="forgot-password-link">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>

                            <!-- Botón de Login -->
                            <button type="submit" class="btn btn-primary" id="loginBtn">
                                <span class="btn-text">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Iniciar Sesión
                                </span>
                                <span class="btn-loading d-none">
                                    <i class="fas fa-spinner fa-spin me-2"></i>
                                    Iniciando sesión...
                                </span>
                            </button>
                        </form>

                        <!-- Footer -->
                        <div class="auth-footer">
                            <p>
                                ¿No tienes cuenta? 
                                <a href="/AgroConecta_v2/public/registro">Regístrate aquí</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/AgroConecta_v2/public/js/auth.js"></script>
</body>
</html>