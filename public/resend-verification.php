<?php
require_once '../core/SessionManager.php';
require_once '../core/Database.php';
require_once '../app/models/Usuario.php';

SessionManager::startSecureSession();

// Solo procesar POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: email-verification.php');
    exit;
}

// Verificar token CSRF
if (!SessionManager::validateCSRF($_POST['csrf_token'] ?? '')) {
    SessionManager::setFlash('error', 'Token de seguridad inválido');
    header('Location: email-verification.php');
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));

// Validar email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    SessionManager::setFlash('error', 'Email inválido');
    header('Location: email-verification.php');
    exit;
}

try {
    $userModel = new Usuario();
    
    // Buscar usuario
    $user = $userModel->findByEmail($email);
    
    if ($user) {
        // Si ya está verificado
        if ($user['verificado'] == 1) {
            SessionManager::setFlash('info', 'Tu cuenta ya está verificada. Puedes iniciar sesión normalmente.');
            header('Location: login.php');
            exit;
        }
        
        // Si no está activo
        if ($user['activo'] != 1) {
            SessionManager::setFlash('error', 'Esta cuenta está desactivada. Contacta al soporte.');
            header('Location: email-verification.php');
            exit;
        }
        
        // Generar nuevo token de verificación
        $token = $userModel->generateVerificationToken($user['id_usuario']);
        
        if ($token) {
            // Log del token para desarrollo (en producción se enviaría por email)
            $verificationUrl = "http://localhost/AgroConecta_v2/public/verify-email.php?token=" . urlencode($token);
            error_log("Email verification URL for {$email}: {$verificationUrl}");
            
            SessionManager::setFlash('success', '✅ Email de verificación reenviado correctamente. Revisa tu bandeja de entrada.');
            error_log("Verification email resent successfully for: {$email}");
        } else {
            throw new Exception('Error al generar el token de verificación');
        }
    } else {
        // Por seguridad, no revelamos si el usuario existe o no
        error_log("Verification email requested for non-existing user: {$email}");
        SessionManager::setFlash('success', '✅ Si el email existe en nuestro sistema, recibirás un email de verificación.');
    }
    
} catch (Exception $e) {
    error_log("Resend verification error: " . $e->getMessage());
    SessionManager::setFlash('error', 'Error al reenviar el email. Inténtalo de nuevo.');
}

header('Location: email-verification.php');
exit;
?>