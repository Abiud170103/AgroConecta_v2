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
                            <div class="text-center mb-4">
                                <span style="font-size: 3rem; color: #28a745;">🌱</span>
                                <h1 class="h3 fw-bold text-dark mt-2">AgroConecta</h1>
                            </div>
                            <h2 class="auth-title">Nueva Contraseña</h2>
                            <p class="auth-subtitle">
                                Ingresa tu nueva contraseña para acceder a tu cuenta
                            </p>
                        </div>

                        <!-- Alertas -->
                        <?php 
                        require_once '../../core/SessionManager.php';
                        if (SessionManager::getFlash('error')): 
                        ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars(SessionManager::getFlash('error')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <?php if (SessionManager::getFlash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars(SessionManager::getFlash('success')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Reset Password Form -->
                        <form class="auth-form" id="resetPasswordForm" action="../controllers/auth-router.php?action=processResetPassword" method="POST">
                            <input type="hidden" name="_token" value="<?= SessionManager::generateCSRF() ?>">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                            
                            <!-- Nueva Contraseña -->
                            <div class="form-group mb-4">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2 text-success"></i>Nueva Contraseña <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrapper position-relative">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Mínimo 6 caracteres"
                                           required 
                                           autofocus
                                           minlength="6">
                                    <button type="button" class="btn btn-link toggle-password position-absolute end-0 top-50 translate-middle-y" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div class="form-group mb-4">
                                <label for="password_confirm" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2 text-success"></i>Confirmar Contraseña <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrapper position-relative">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirm" 
                                           name="password_confirm" 
                                           placeholder="Repite tu nueva contraseña"
                                           required
                                           minlength="6">
                                    <button type="button" class="btn btn-link toggle-password position-absolute end-0 top-50 translate-middle-y" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Botón de Actualizar -->
                            <button type="submit" class="btn btn-success w-100 mb-4" id="updateBtn">
                                <i class="fas fa-key me-2"></i>
                                Actualizar Contraseña
                            </button>
                        </form>

                        <!-- Footer -->
                        <div class="auth-footer text-center">
                            <p class="mb-0">
                                <a href="../../public/login.php" class="text-success fw-semibold text-decoration-none">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Volver al inicio de sesión
                                </a>
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
    
    <script>
        // Validar que las contraseñas coincidan
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirm').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
            }
        });
        
        // Toggle de visibilidad de contraseñas
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });
    </script>
</body>
</html>