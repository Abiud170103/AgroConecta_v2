<?php
/**
 * Procesador para reset de contraseña
 * Actualiza la contraseña del usuario con un token válido
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
    header('Location: login.php');
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
        header('Location: login.php');
        exit;
    }
    
    $token = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    
    // Validaciones
    if (empty($token)) {
        SessionManager::setFlash('error', 'Token de recuperación inválido');
        header('Location: login.php');
        exit;
    }
    
    if (empty($password) || strlen($password) < 6) {
        SessionManager::setFlash('error', 'La contraseña debe tener al menos 6 caracteres');
        header('Location: reset-password.php?token=' . urlencode($token));
        exit;
    }
    
    if ($password !== $passwordConfirm) {
        SessionManager::setFlash('error', 'Las contraseñas no coinciden');
        header('Location: reset-password.php?token=' . urlencode($token));
        exit;
    }
    
    // Verificar token
    $userModel = new Usuario();
    $user = $userModel->verifyResetToken($token);
    
    if (!$user) {
        SessionManager::setFlash('error', 'Token de recuperación inválido o expirado');
        header('Location: login.php');
        exit;
    }
    
    // Actualizar contraseña
    $success = $userModel->updatePassword($user['id_usuario'], $password);
    
    if ($success) {
        // Log de la actividad
        error_log("Password reset successful for user ID: {$user['id_usuario']}, email: {$user['correo']}");
        
        SessionManager::setFlash('success', '¡Contraseña actualizada correctamente! Ya puedes iniciar sesión con tu nueva contraseña.');
        header('Location: login.php');
        exit;
    } else {
        error_log("Password reset failed for user ID: {$user['id_usuario']}, email: {$user['correo']}");
        
        SessionManager::setFlash('error', 'Error al actualizar la contraseña. Por favor, inténtalo de nuevo.');
        header('Location: reset-password.php?token=' . urlencode($token));
        exit;
    }

} catch (Exception $e) {
    // Log del error
    error_log("Error in process-reset-password.php: " . $e->getMessage());
    
    // Mensaje genérico al usuario
    SessionManager::setFlash('error', 'Ha ocurrido un error interno. Por favor, inténtalo de nuevo.');
    
    // Si tenemos token, volver a la página de reset
    if (!empty($_POST['token'])) {
        header('Location: reset-password.php?token=' . urlencode($_POST['token']));
    } else {
        header('Location: login.php');
    }
    exit;
}
?>