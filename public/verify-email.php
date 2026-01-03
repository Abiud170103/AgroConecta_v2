<?php
require_once '../core/SessionManager.php';
require_once '../core/Database.php';
SessionManager::startSecureSession();

// Obtener token de la URL
$token = $_GET['token'] ?? '';

// Si no hay token, redirigir
if (empty($token)) {
    SessionManager::setFlash('error', 'Token de verificación inválido');
    header('Location: login.php');
    exit;
}

// Verificar y procesar el token
require_once '../app/models/Usuario.php';
$userModel = new Usuario();

// Buscar usuario con este token
$query = "SELECT * FROM Usuario WHERE token_verificacion = ? AND activo = 1";
$user = $userModel->db->selectOne($query, [$token]);

if (!$user) {
    SessionManager::setFlash('error', 'Token de verificación inválido o expirado');
    header('Location: login.php');
    exit;
}

// Si el usuario ya está verificado
if ($user['verificado'] == 1) {
    SessionManager::setFlash('info', 'Tu cuenta ya está verificada. Puedes iniciar sesión normalmente.');
    header('Location: login.php');
    exit;
}

// Procesar la verificación
$result = $userModel->verifyUser($token);

if ($result) {
    SessionManager::setFlash('success', '🎉 ¡Email verificado correctamente! Tu cuenta está ahora activa. Ya puedes iniciar sesión.');
    error_log("Email verification successful for user ID: " . $user['id_usuario']);
} else {
    SessionManager::setFlash('error', 'Error al verificar el email. Inténtalo de nuevo.');
    error_log("Email verification failed for token: " . substr($token, 0, 16));
}

// Redirigir al login
header('Location: login.php');
exit;
?>