<?php
/**
 * Procesador directo para forgot password
 * Esto evita problemas con el sistema de rutas
 */

session_start();
date_default_timezone_set('America/Mexico_City');

// Constantes
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('BASE_URL', 'http://localhost/AgroConecta_v2/public');

// Solo procesar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot-password.php');
    exit;
}

try {
    // Cargar dependencias
    require_once '../config/database.php';
    require_once '../app/core/Database.php';
    require_once '../app/models/Usuario.php';
    require_once '../core/SessionManager.php';
    
    // Iniciar sesión segura
    SessionManager::startSecureSession();
    
    // Validar CSRF
    if (!SessionManager::validateCSRF($_POST['_token'] ?? '')) {
        SessionManager::setFlash('error', 'Token de seguridad inválido');
        header('Location: forgot-password.php');
        exit;
    }
    
    $email = trim($_POST['email'] ?? '');
    
    // Validar email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        SessionManager::setFlash('error', 'Email inválido');
        header('Location: forgot-password.php');
        exit;
    }
    
    // Generar token de reset
    $userModel = new Usuario();
    
    // Verificar si el usuario existe (para logging)
    $userExists = $userModel->findByEmail($email);
    if ($userExists) {
        error_log("Password reset requested for existing user: {$email}");
    } else {
        error_log("Password reset requested for non-existing user: {$email}");
    }
    
    $token = $userModel->generateResetToken($email);
    
    if ($token && $userExists) {
        error_log("Reset token generated successfully for: {$email}");
        error_log("Reset token (first 16 chars): " . substr($token, 0, 16) . "...");
        
        // Generar URL de reset
        $resetUrl = BASE_URL . "/reset-password.php?token=" . urlencode($token);
        error_log("Password reset URL for {$email}: {$resetUrl}");
        
        // Verificar que se guardó en la base de datos
        $verification = $userModel->findByEmail($email);
        if ($verification && !empty($verification['token_reset'])) {
            error_log("Token verified in database for: {$email}");
        } else {
            error_log("WARNING: Token not found in database after generation for: {$email}");
        }
    } else {
        error_log("Token generation failed or user doesn't exist: {$email}");
    }
    
    // Siempre mostrar mensaje de éxito por seguridad
    SessionManager::setFlash('success', 'Si el email existe en nuestro sistema, recibirás instrucciones para recuperar tu contraseña');
    
    // Redirigir al login
    header('Location: login.php');
    exit;

} catch (Exception $e) {
    // Log del error
    error_log("Error in process-forgot-password.php: " . $e->getMessage());
    
    // Mensaje genérico al usuario
    SessionManager::setFlash('error', 'Ha ocurrido un error. Por favor, inténtalo de nuevo.');
    header('Location: forgot-password.php');
    exit;
}
?>