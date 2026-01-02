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
    <link rel="stylesheet" href="public/css/app.css">
    <link rel="stylesheet" href="public/css/auth.css">
</head>
<body class="auth-page">

    <section class="auth-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="auth-container">
                        <!-- Header -->
                        <div class="auth-header">
                            <div class="auth-logo">
                                <img src="public/img/logo.png" alt="AgroConecta" class="logo-image">
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
                        <form class="auth-form" id="forgotPasswordForm" action="olvide-password" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            
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
                                <a href="login">
                                    <i class="fas fa-arrow-left"></i>
                                    Volver al inicio de sesión
                                </a>
                            </p>
                            <p style="margin-top: 1rem;">
                                ¿No tienes cuenta? 
                                <a href="registro">Regístrate aquí</a>
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
    <script src="public/js/auth.js"></script>
</body>
</html>
