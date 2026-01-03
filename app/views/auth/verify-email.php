<?php
/**
 * Vista de verificación de email - MVC
 * AgroConecta - Sistema de autenticación
 */

// Verificar que no esté logueado
if (SessionManager::isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard');
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/app.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/auth.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verify-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 3rem 2.5rem;
            max-width: 450px;
            width: 100%;
            text-align: center;
        }
        .verify-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        .verify-icon.success {
            color: #28a745;
        }
        .verify-icon.error {
            color: #dc3545;
        }
        .verify-title {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .verify-message {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        .btn-custom {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 15px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
            color: white;
            text-decoration: none;
        }
        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .auth-logo .logo-emoji {
            font-size: 2rem;
            margin-right: 0.5rem;
        }
        .auth-logo .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: #28a745;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="verify-container">
        <!-- Logo -->
        <div class="auth-logo">
            <span class="logo-emoji">🌱</span>
            <h1 class="logo-text">AgroConecta</h1>
        </div>

        <?php if ($verification_result ?? false): ?>
            <!-- Verificación exitosa -->
            <div class="verify-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="verify-title">¡Email Verificado!</h2>
            <p class="verify-message">
                🎉 Tu cuenta ha sido verificada exitosamente. 
                Ya puedes iniciar sesión y disfrutar de todos los beneficios de AgroConecta.
            </p>
            <a href="<?= BASE_URL ?>/public/login.php" class="btn btn-custom">
                <i class="fas fa-sign-in-alt me-2"></i>
                Iniciar Sesión
            </a>
        <?php else: ?>
            <!-- Error en verificación -->
            <div class="verify-icon error">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="verify-title">Error de Verificación</h2>
            <p class="verify-message">
                <?= $error_message ?? 'Token de verificación inválido o expirado. El enlace puede haber sido usado anteriormente o haber caducado.' ?>
            </p>
            
            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>/public/email-verification.php" class="btn btn-warning">
                    <i class="fas fa-envelope me-2"></i>
                    Reenviar Email de Verificación
                </a>
                <a href="<?= BASE_URL ?>/public/login.php" class="btn btn-custom">
                    <i class="fas fa-arrow-left me-2"></i>
                    Volver al Login
                </a>
            </div>
        <?php endif; ?>

        <!-- Flash Messages -->
        <?php if (SessionManager::hasFlash('success')): ?>
            <div class="alert alert-success mt-3">
                <i class="fas fa-check-circle me-2"></i>
                <?= SessionManager::getFlash('success') ?>
            </div>
        <?php endif; ?>

        <?php if (SessionManager::hasFlash('error')): ?>
            <div class="alert alert-danger mt-3">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= SessionManager::getFlash('error') ?>
            </div>
        <?php endif; ?>

        <?php if (SessionManager::hasFlash('info')): ?>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <?= SessionManager::getFlash('info') ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>