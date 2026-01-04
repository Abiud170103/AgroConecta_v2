<?php
/**
 * Logout - Cerrar Sesión
 * AgroConecta
 */

require_once '../core/SessionManager.php';

SessionManager::startSecureSession();
SessionManager::logout();

// Mensaje de confirmación y redirección
SessionManager::setFlash('success', 'Sesión cerrada exitosamente');
header('Location: login.php');
exit;
?>